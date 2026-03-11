<?php
namespace Core\Controller;

use Core\Model\App;
use Core\Model\Session;
use Core\Database\Operation; // Utiliser Operation au lieu de Cheque
use Core\Database\Agence;
use Core\Model\AppController;

class ConfirmationChequeController extends AppController {
    
    public function __construct() {
        parent::__construct();
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    public function index() {
        $session = Session::getInstance();
        $user = $_SESSION['user'];
        
        // Récupérer le nombre total pour la pagination
        $totalOperations = Operation::count();
        $operations = Operation::getAll();
        
        $data = [
            'operations' => $operations,
            'user' => $user,
            'totalOperations' => $totalOperations
        ];
        
        $this->render('ConfirmationCheque.index', $data);
    }
    
    public function ajoutOperation() {
        error_reporting(0);
        ini_set('display_errors', 0);
        
        header('Content-Type: application/json');
        
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['user']) || empty($_SESSION['user'])) {
            echo json_encode([
                'success' => false,
                'message' => 'Utilisateur non connecté. Veuillez vous reconnecter.'
            ]);
            return;
        }
        
        $user = $_SESSION['user'];
        
        $allowedToAdd = in_array($user['privilege'], ['Agence', 'AgenceSage', 'Caissiere', 'CaissiereLD', 'CaissiereSage', 'OPAgence']);
        if (!$allowedToAdd) {
            echo json_encode([
                'success' => false,
                'message' => 'Vous n\'avez pas la permission d\'ajouter des opérations'
            ]);
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode([
                'success' => false,
                'message' => 'Méthode non autorisée'
            ]);
            return;
        }
        
        $errors = [];
        $requiredFields = ['nom_client', 'numero_cheque', 'montant', 'banque', 'date_reception', 'type_operation'];
        
        foreach ($requiredFields as $field) {
            if (empty($_POST[$field])) {
                $errors[] = "Le champ '$field' est obligatoire";
            }
        }
        
        // Vérification du type d'opération
        $type_operation = isset($_POST['type_operation']) ? $_POST['type_operation'] : 'cheque';
        if (!in_array($type_operation, ['cheque', 'virement'])) {
            $errors[] = "Type d'opération invalide. Doit être 'cheque' ou 'virement'.";
        }
        
        // VÉRIFICATION OBLIGATOIRE DU SCAN
        if (!isset($_FILES['scan_operation']) || $_FILES['scan_operation']['error'] !== UPLOAD_ERR_OK) {
            $errors[] = "Le justificatif (scan) est obligatoire";
        }
        
        if (!empty($errors)) {
            echo json_encode([
                'success' => false,
                'message' => 'Erreurs de validation: ' . implode(', ', $errors)
            ]);
            return;
        }
        
        $date_reception = $_POST['date_reception'];
        
        if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $date_reception, $matches)) {
            $date_reception = $matches[3] . '-' . $matches[2] . '-' . $matches[1];
        }
        
        if (!strtotime($date_reception)) {
            echo json_encode([
                'success' => false,
                'message' => 'Date de réception invalide'
            ]);
            return;
        }
        
        $data = [
            'type_operation' => $type_operation, // NOUVEAU
            'nom_client' => trim($_POST['nom_client']),
            'numero_cheque' => trim($_POST['numero_cheque']),
            'montant' => floatval(str_replace(',', '.', $_POST['montant'])),
            'banque' => trim($_POST['banque']),
            'date_reception' => $date_reception,
            'date_entree' => date('Y-m-d H:i:s'),
            'observations' => isset($_POST['observations']) ? trim($_POST['observations']) : '',
            'agence_id' => isset($user['agence']) ? $user['agence'] : (isset($user['idAgence']) ? $user['idAgence'] : null),
            'created_by' => isset($user['idUser']) ? $user['idUser'] : (isset($user['id']) ? $user['id'] : null)
        ];
        
        if ($data['montant'] <= 0) {
            echo json_encode([
                'success' => false,
                'message' => 'Le montant doit être supérieur à 0'
            ]);
            return;
        }
        
        // TRAITEMENT OBLIGATOIRE DU SCAN
        $scanPath = null;
        if (isset($_FILES['scan_operation']) && $_FILES['scan_operation']['error'] === UPLOAD_ERR_OK) {
            $targetDir = "DocumentsCheques/";
            if (!is_dir($targetDir)) {
                if (!mkdir($targetDir, 0777, true)) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Impossible de créer le dossier de destination'
                    ]);
                    return;
                }
            }
            
            $allowedTypes = ['image/jpeg', 'image/png', 'application/pdf'];
            $fileType = $_FILES['scan_operation']['type'];
            
            if (!in_array($fileType, $allowedTypes)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Type de fichier non autorisé. Formats acceptés: JPG, PNG, PDF'
                ]);
                return;
            }
            
            $maxFileSize = 5 * 1024 * 1024; // 5MB
            if ($_FILES['scan_operation']['size'] > $maxFileSize) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Le fichier est trop volumineux (max 5MB)'
                ]);
                return;
            }
            
            // Nom du fichier avec préfixe selon le type
            $prefix = $type_operation === 'cheque' ? 'cheque_' : 'virement_';
            $fileName = $prefix . time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $_FILES['scan_operation']['name']);
            $targetPath = $targetDir . $fileName;
            
            if (move_uploaded_file($_FILES['scan_operation']['tmp_name'], $targetPath)) {
                $scanPath = $targetPath;
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Erreur lors du téléchargement du justificatif'
                ]);
                return;
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Le justificatif est obligatoire'
            ]);
            return;
        }
        
        try {
            $operationId = Operation::add($data, $scanPath);
            
            if (!$operationId) {
                throw new \Exception('Erreur lors de l\'insertion dans la base de données');
            }
            
            $operationInserted = Operation::find($operationId);
            
            if (!$operationInserted) {
                $operationInserted = [
                    'id' => $operationId,
                    'type_operation' => $data['type_operation'],
                    'nom_client' => $data['nom_client'],
                    'numero_cheque' => $data['numero_cheque'],
                    'montant' => $data['montant'],
                    'banque' => $data['banque'],
                    'date_reception' => $data['date_reception'],
                    'date_entree' => $data['date_entree'],
                    'observations' => $data['observations'],
                    'agence_id' => $data['agence_id'],
                    'statut' => 'en cours',
                    'etat_confirmation' => 'Non',
                    'etat_validation' => 'Non',
                    'scan_path' => $scanPath
                ];
            }
            
            if (isset($data['agence_id']) && $data['agence_id']) {
                try {
                    $stmt = Agence::searchById($data['agence_id']);
                    if ($stmt) {
                        while ($agence = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                            if (isset($agence['designation'])) {
                                $operationInserted['agence_nom'] = $agence['designation'];
                                break;
                            }
                        }
                    }
                } catch (\Exception $e) {}
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'Opération ajoutée avec succès',
                'operation' => $operationInserted,
                'refreshNeeded' => false
            ]);
            
        } catch (\Exception $e) {
            error_log("Erreur ajoutOperation: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Erreur serveur: ' . $e->getMessage()
            ]);
        }
    }
    
    public function getOperationData() {
        error_reporting(0);
        ini_set('display_errors', 0);
        
        header('Content-Type: application/json');
        
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_POST['operation_id'])) {
            echo json_encode(['success' => false, 'message' => 'ID opération manquant']);
            return;
        }
        
        $operation_id = intval($_POST['operation_id']);
        $user = $_SESSION['user'];
        
        try {
            $operation = Operation::find($operation_id);
            
            if (!$operation) {
                echo json_encode(['success' => false, 'message' => 'Opération non trouvée']);
                return;
            }
            
            // Vérifier si l'opération peut être modifiée
            $canBeModified = $this->canOperationBeModified($operation);
            
            // Vérifier si l'utilisateur est le créateur de l'opération
            $isCreator = $this->isOperationCreator($operation, $user);
            
            // L'utilisateur peut modifier seulement s'il est le créateur ET si l'opération peut être modifiée
            $userCanModify = $canBeModified && $isCreator;
            
            echo json_encode([
                'success' => true,
                'operation' => $operation,
                'canBeModified' => $canBeModified,
                'isCreator' => $isCreator,
                'userCanModify' => $userCanModify
            ]);
            
        } catch (\Exception $e) {
            error_log("Erreur getOperationData: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Erreur serveur: ' . $e->getMessage()
            ]);
        }
    }
    
    public function updateOperation() {
        error_reporting(0);
        ini_set('display_errors', 0);
        
        header('Content-Type: application/json');
        
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['user']) || empty($_SESSION['user'])) {
            echo json_encode([
                'success' => false,
                'message' => 'Utilisateur non connecté. Veuillez vous reconnecter.'
            ]);
            return;
        }
        
        $user = $_SESSION['user'];
        
        // Vérifier les permissions
        // $allowedToEdit = in_array($user['privilege'], ['Agence', 'AgenceSage', 'Caissiere', 'CaissiereLD', 'CaissiereSage', 'OPAgence']);
         $allowedToEdit = in_array($user['privilege'], ['Agence','OPAgence']);
        if (!$allowedToEdit) {
            echo json_encode([
                'success' => false,
                'message' => 'Vous n\'avez pas la permission de modifier des opérations'
            ]);
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode([
                'success' => false,
                'message' => 'Méthode non autorisée'
            ]);
            return;
        }
        
        if (!isset($_POST['operation_id'])) {
            echo json_encode([
                'success' => false,
                'message' => 'ID opération manquant'
            ]);
            return;
        }
        
        $operation_id = intval($_POST['operation_id']);
        
        // Récupérer l'opération pour vérifier si elle peut être modifiée
        $operation = Operation::find($operation_id);
        if (!$operation) {
            echo json_encode([
                'success' => false,
                'message' => 'Opération non trouvée'
            ]);
            return;
        }
        
        // Vérifier si l'opération peut être modifiée
        if (!$this->canOperationBeModified($operation)) {
            echo json_encode([
                'success' => false,
                'message' => 'Cette opération ne peut plus être modifiée (statut, état confirmation ou état validation a changé)'
            ]);
            return;
        }
        
        // Vérifier si l'utilisateur est le créateur de l'opération
        if (!$this->isOperationCreator($operation, $user)) {
            echo json_encode([
                'success' => false,
                'message' => 'Vous ne pouvez modifier que les opérations que vous avez créées'
            ]);
            return;
        }
        
        $errors = [];
        $requiredFields = ['nom_client', 'numero_cheque', 'montant', 'banque', 'date_reception'];
        
        foreach ($requiredFields as $field) {
            if (empty($_POST[$field])) {
                $errors[] = "Le champ '$field' est obligatoire";
            }
        }
        
        if (!empty($errors)) {
            echo json_encode([
                'success' => false,
                'message' => 'Erreurs de validation: ' . implode(', ', $errors)
            ]);
            return;
        }
        
        $date_reception = $_POST['date_reception'];
        
        if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $date_reception, $matches)) {
            $date_reception = $matches[3] . '-' . $matches[2] . '-' . $matches[1];
        }
        
        if (!strtotime($date_reception)) {
            echo json_encode([
                'success' => false,
                'message' => 'Date de réception invalide'
            ]);
            return;
        }
        
        $data = [
            'nom_client' => trim($_POST['nom_client']),
            'numero_cheque' => trim($_POST['numero_cheque']),
            'montant' => floatval(str_replace(',', '.', $_POST['montant'])),
            'banque' => trim($_POST['banque']),
            'date_reception' => $date_reception,
            'observations' => isset($_POST['observations']) ? trim($_POST['observations']) : '',
            'updated_at' => date('Y-m-d H:i:s'),
            'updated_by' => isset($user['idUser']) ? $user['idUser'] : (isset($user['id']) ? $user['id'] : null)
        ];
        
        // Garder le même type d'opération
        if (isset($operation['type_operation'])) {
            $data['type_operation'] = $operation['type_operation'];
        }
        
        if ($data['montant'] <= 0) {
            echo json_encode([
                'success' => false,
                'message' => 'Le montant doit être supérieur à 0'
            ]);
            return;
        }
        
        // TRAITEMENT DU SCAN (facultatif pour la modification)
        $scanPath = null;
        if (isset($_FILES['scan_operation']) && $_FILES['scan_operation']['error'] === UPLOAD_ERR_OK) {
            $targetDir = "DocumentsCheques/";
            if (!is_dir($targetDir)) {
                if (!mkdir($targetDir, 0777, true)) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Impossible de créer le dossier de destination'
                    ]);
                    return;
                }
            }
            
            $allowedTypes = ['image/jpeg', 'image/png', 'application/pdf'];
            $fileType = $_FILES['scan_operation']['type'];
            
            if (!in_array($fileType, $allowedTypes)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Type de fichier non autorisé. Formats acceptés: JPG, PNG, PDF'
                ]);
                return;
            }
            
            $maxFileSize = 5 * 1024 * 1024; // 5MB
            if ($_FILES['scan_operation']['size'] > $maxFileSize) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Le fichier est trop volumineux (max 5MB)'
                ]);
                return;
            }
            
            // Nom du fichier avec préfixe selon le type
            $type_operation = isset($operation['type_operation']) ? $operation['type_operation'] : 'cheque';
            $prefix = $type_operation === 'cheque' ? 'cheque_' : 'virement_';
            $fileName = $prefix . time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $_FILES['scan_operation']['name']);
            $targetPath = $targetDir . $fileName;
            
            if (move_uploaded_file($_FILES['scan_operation']['tmp_name'], $targetPath)) {
                $scanPath = $targetPath;
                $data['scan_path'] = $scanPath;
                
                // Supprimer l'ancien scan s'il existe
                if (!empty($operation['scan_path']) && file_exists($operation['scan_path'])) {
                    @unlink($operation['scan_path']);
                }
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Erreur lors du téléchargement du nouveau justificatif'
                ]);
                return;
            }
        }
        
        try {
            $result = Operation::update($operation_id, $data);
            
            if ($result) {
                $operationUpdated = Operation::find($operation_id);
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Opération modifiée avec succès',
                    'operation' => $operationUpdated
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Erreur lors de la modification de l\'opération'
                ]);
            }
            
        } catch (\Exception $e) {
            error_log("Erreur updateOperation: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Erreur serveur: ' . $e->getMessage()
            ]);
        }
    }
    
    private function canOperationBeModified($operation) {
        // Une opération peut être modifiée seulement si:
        // 1. Le statut est toujours "en cours"
        // 2. L'état confirmation est "Non"
        // 3. L'état validation est "Non"
        
        $statut = isset($operation['statut']) ? strtolower($operation['statut']) : 'en cours';
        $etatConfirmation = isset($operation['etat_confirmation']) ? $operation['etat_confirmation'] : 'Non';
        $etatValidation = isset($operation['etat_validation']) ? $operation['etat_validation'] : 'Non';
        
        return ($statut === 'en cours' && 
                $etatConfirmation === 'Non' && 
                $etatValidation === 'Non');
    }
    
    private function isOperationCreator($operation, $user) {
        // Récupérer l'ID de l'utilisateur
        $userId = isset($user['idUser']) ? $user['idUser'] : (isset($user['id']) ? $user['id'] : null);
        $userCreatedBy = isset($user['created_by']) ? $user['created_by'] : null;
        
        // Récupérer l'ID du créateur de l'opération
        $operationCreatedBy = isset($operation['created_by']) ? $operation['created_by'] : null;
        
        // Si l'opération n'a pas de créateur enregistré, on considère que c'est l'utilisateur actuel
        if (empty($operationCreatedBy)) {
            return true; // Pour les anciennes opérations qui n'ont pas de created_by
        }
        
        // Vérifier si l'utilisateur est le créateur
        return ($operationCreatedBy == $userId || $operationCreatedBy == $userCreatedBy);
    }
    
    public function changerStatut() {
        error_reporting(0);
        ini_set('display_errors', 0);
        
        header('Content-Type: application/json');
        
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['user']) || empty($_SESSION['user'])) {
            echo json_encode(['success' => false, 'message' => 'Utilisateur non connecté']);
            return;
        }
        
        $user = $_SESSION['user'];
        
        if ($user['privilege'] !== 'Comptabilite') {
            echo json_encode(['success' => false, 'message' => 'Accès refusé. Réservé à la comptabilité.']);
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
            return;
        }
        
        if (!isset($_POST['operation_id']) || !isset($_POST['nouveau_statut'])) {
            echo json_encode(['success' => false, 'message' => 'Données manquantes']);
            return;
        }
        
        $operation_id = intval($_POST['operation_id']);
        $nouveau_statut = $_POST['nouveau_statut'];
        $observation = isset($_POST['observation']) ? trim($_POST['observation']) : null;
        
        $statuts_valides = ['confirmé', 'annulé'];
        if (!in_array($nouveau_statut, $statuts_valides)) {
            echo json_encode(['success' => false, 'message' => 'Statut invalide. Doit être "confirmé" ou "annulé".']);
            return;
        }
        
        try {
            $userId = isset($user['idUser']) ? $user['idUser'] : (isset($user['id']) ? $user['id'] : null);
            $result = Operation::updateStatut($operation_id, $nouveau_statut, $observation, $userId);
            
            if ($result) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Statut de l\'opération mis à jour avec succès'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Erreur lors de la mise à jour du statut'
                ]);
            }
        } catch (\Exception $e) {
            error_log("Erreur changerStatut: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Erreur serveur: ' . $e->getMessage()
            ]);
        }
    }
    
    public function changerEtat() {
        error_reporting(0);
        ini_set('display_errors', 0);
        
        header('Content-Type: application/json');
        
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['user']) || empty($_SESSION['user'])) {
            echo json_encode(['success' => false, 'message' => 'Utilisateur non connecté']);
            return;
        }
        
        $user = $_SESSION['user'];
        
        if ($user['privilege'] !== 'Comptabilite') {
            echo json_encode(['success' => false, 'message' => 'Accès refusé. Réservé à la comptabilité.']);
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
            return;
        }
        
        if (!isset($_POST['operation_id']) || !isset($_POST['nouvel_etat_validation'])) {
            echo json_encode(['success' => false, 'message' => 'Données manquantes']);
            return;
        }
        
        $operation_id = intval($_POST['operation_id']);
        $nouvel_etat = $_POST['nouvel_etat_validation'];
        $observation = isset($_POST['observation']) ? trim($_POST['observation']) : null;
        
        $etats_valides = ['Oui', 'Non'];
        if (!in_array($nouvel_etat, $etats_valides)) {
            echo json_encode(['success' => false, 'message' => 'État invalide. Doit être "Oui" ou "Non".']);
            return;
        }
        
        try {
            $userId = isset($user['idUser']) ? $user['idUser'] : (isset($user['id']) ? $user['id'] : null);
            $result = Operation::updateEtatValidation($operation_id, $nouvel_etat, $observation, $userId);
            
            if ($result) {
                echo json_encode([
                    'success' => true,
                    'message' => 'État de validation mis à jour avec succès'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Erreur lors de la mise à jour de l\'état'
                ]);
            }
        } catch (\Exception $e) {
            error_log("Erreur changerEtat: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Erreur serveur: ' . $e->getMessage()
            ]);
        }
    }
    
    public function changerEtatConfirmation() {
        error_reporting(0);
        ini_set('display_errors', 0);
        
        header('Content-Type: application/json');
        
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['user']) || empty($_SESSION['user'])) {
            echo json_encode(['success' => false, 'message' => 'Utilisateur non connecté']);
            return;
        }
        
        $user = $_SESSION['user'];
        
        if ($user['privilege'] !== 'Comptabilite') {
            echo json_encode(['success' => false, 'message' => 'Accès refusé. Réservé à la comptabilité.']);
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
            return;
        }
        
        if (!isset($_POST['operation_id']) || !isset($_POST['nouvel_etat_confirmation'])) {
            echo json_encode(['success' => false, 'message' => 'Données manquantes']);
            return;
        }
        
        $operation_id = intval($_POST['operation_id']);
        $nouvel_etat = $_POST['nouvel_etat_confirmation'];
        $observation = isset($_POST['observation']) ? trim($_POST['observation']) : null;
        
        $etats_valides = ['Oui', 'Non'];
        if (!in_array($nouvel_etat, $etats_valides)) {
            echo json_encode(['success' => false, 'message' => 'État invalide. Doit être "Oui" ou "Non".']);
            return;
        }
        
        try {
            $userId = isset($user['idUser']) ? $user['idUser'] : (isset($user['id']) ? $user['id'] : null);
            $result = Operation::updateEtatConfirmation($operation_id, $nouvel_etat, $observation, $userId);
            
            if ($result) {
                echo json_encode([
                    'success' => true,
                    'message' => 'État de confirmation mis à jour avec succès'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Erreur lors de la mise à jour'
                ]);
            }
        } catch (\Exception $e) {
            error_log("Erreur changerEtatConfirmation: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Erreur serveur: ' . $e->getMessage()
            ]);
        }
    }
    
    public function getDetails() {
        error_reporting(0);
        ini_set('display_errors', 0);
        
        header('Content-Type: application/json');
        
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_POST['operation_id'])) {
            echo json_encode(['success' => false, 'message' => 'ID opération manquant']);
            return;
        }
        
        $operation_id = intval($_POST['operation_id']);
        
        try {
            $operation = Operation::find($operation_id);
            
            if (!$operation) {
                echo json_encode(['success' => false, 'message' => 'Opération non trouvée']);
                return;
            }
            
            $agence_nom = '';
            if (isset($operation['agence_id']) && $operation['agence_id']) {
                $stmt = Agence::searchById($operation['agence_id']);
                if ($stmt) {
                    while ($agence = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                        if (isset($agence['designation'])) {
                            $agence_nom = $agence['designation'];
                            break;
                        }
                    }
                }
            }
            
            $type_operation = isset($operation['type_operation']) ? $operation['type_operation'] : 'cheque';
            $type_label = $type_operation === 'cheque' ? 'Chèque' : 'Virement';
            $type_icon = $type_operation === 'cheque' ? 'fa-money' : 'fa-exchange';
            
            $date_reception = '';
            if (isset($operation['date_reception'])) {
                if ($operation['date_reception'] instanceof \DateTime) {
                    $date_reception = $operation['date_reception']->format('d/m/Y');
                } else {
                    $date_reception = date('d/m/Y', strtotime($operation['date_reception']));
                }
            }
            
            $date_entree = '';
            if (isset($operation['date_entree'])) {
                if ($operation['date_entree'] instanceof \DateTime) {
                    $date_entree = $operation['date_entree']->format('d/m/Y H:i');
                } else {
                    $date_entree = date('d/m/Y H:i', strtotime($operation['date_entree']));
                }
            }
            
            $date_validation = '';
            if (isset($operation['date_validation']) && !empty($operation['date_validation'])) {
                if ($operation['date_validation'] instanceof \DateTime) {
                    $date_validation = $operation['date_validation']->format('d/m/Y H:i');
                } else {
                    $date_validation = date('d/m/Y H:i', strtotime($operation['date_validation']));
                }
            }
            
            $date_confirmation = '';
            if (isset($operation['date_confirmation']) && !empty($operation['date_confirmation'])) {
                if ($operation['date_confirmation'] instanceof \DateTime) {
                    $date_confirmation = $operation['date_confirmation']->format('d/m/Y H:i');
                } else {
                    $date_confirmation = date('d/m/Y H:i', strtotime($operation['date_confirmation']));
                }
            }
            
            $statutClass = 'label-warning';
            if (isset($operation['statut']) && $operation['statut'] === 'confirmé') {
                $statutClass = 'label-success';
            } elseif (isset($operation['statut']) && $operation['statut'] === 'annulé') {
                $statutClass = 'label-danger';
            }
            
            $etatConfClass = 'label-danger';
            $etatConfText = isset($operation['etat_confirmation']) ? $operation['etat_confirmation'] : 'Non';
            if ($etatConfText === 'Oui') {
                $etatConfClass = 'label-success';
            }
            
            $etatValidClass = 'label-danger';
            $etatValidText = isset($operation['etat_validation']) ? $operation['etat_validation'] : 'Non';
            if ($etatValidText === 'Oui') {
                $etatValidClass = 'label-success';
            }
            
            $observation_confirmation = isset($operation['observation_confirmation']) ? $operation['observation_confirmation'] : '';
            $observation_validation = isset($operation['observation_validation']) ? $operation['observation_validation'] : '';
            $observation_statut = isset($operation['observation_validation']) ? $operation['observation_validation'] : '';
            $observations = isset($operation['observations']) ? $operation['observations'] : '';
            
            $numero_label = $type_operation === 'cheque' ? 'Numéro chèque' : 'Référence virement';
            $scan_label = $type_operation === 'cheque' ? 'Scan du chèque' : 'Justificatif du virement';
            
            $html = '
            <div class="row">
                <div class="col-md-6">
                    <h4>Informations de l\'opération</h4>
                    <table class="table table-bordered">
                        <tr>
                            <th style="width: 40%">Type:</th>
                            <td><i class="fa ' . $type_icon . '"></i> ' . $type_label . '</td>
                        </tr>
                        <tr>
                            <th>Client:</th>
                            <td>' . htmlspecialchars($operation['nom_client']) . '</td>
                        </tr>
                        <tr>
                            <th>' . $numero_label . ':</th>
                            <td>' . htmlspecialchars($operation['numero_cheque']) . '</td>
                        </tr>
                        <tr>
                            <th>Montant:</th>
                            <td><strong>' . number_format($operation['montant'], 0, ',', ' ') . ' FCFA</strong></td>
                        </tr>
                        <tr>
                            <th>Banque:</th>
                            <td>' . htmlspecialchars($operation['banque']) . '</td>
                        </tr>
                        <tr>
                            <th>Date réception:</th>
                            <td>' . $date_reception . '</td>
                        </tr>
                        <tr>
                            <th>Date entrée:</th>
                            <td>' . $date_entree . '</td>
                        </tr>
                        <tr>
                            <th>Agence:</th>
                            <td>' . htmlspecialchars($agence_nom) . '</td>
                        </tr>
                        <tr>
                            <th>Observations:</th>
                            <td>' . nl2br(htmlspecialchars($observations)) . '</td>
                        </tr>
                    </table>
                </div>
                
                <div class="col-md-6">
                    <h4>Validation</h4>
                    <table class="table table-bordered">
                        <tr>
                            <th style="width: 40%">Statut:</th>
                            <td>
                                <span class="label ' . $statutClass . '">
                                    ' . (isset($operation['statut']) ? ucfirst($operation['statut']) : 'En cours') . '
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>État confirmation:</th>
                            <td>
                                <span class="label ' . $etatConfClass . '">
                                    ' . $etatConfText . '
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>État validation:</th>
                            <td>
                                <span class="label ' . $etatValidClass . '">
                                    ' . $etatValidText . '
                                </span>
                            </td>
                        </tr>';
            
            if ($date_confirmation) {
                $html .= '<tr><th>Date confirmation:</th><td>' . $date_confirmation . '</td></tr>';
            }
            
            if ($date_validation) {
                $html .= '<tr><th>Date validation:</th><td>' . $date_validation . '</td></tr>';
            }
            
            if (!empty($observation_confirmation)) {
                $html .= '<tr><th>Observation confirmation:</th><td>' . nl2br(htmlspecialchars($observation_confirmation)) . '</td></tr>';
            }
            
            if (!empty($observation_validation)) {
                $html .= '<tr><th>Observation validation:</th><td>' . nl2br(htmlspecialchars($observation_validation)) . '</td></tr>';
            }
            
            if (!empty($observation_statut)) {
                $html .= '<tr><th>Observation statut:</th><td>' . nl2br(htmlspecialchars($observation_statut)) . '</td></tr>';
            }
            
            $html .= '
                    </table>
                </div>
            </div>';
            
            if (!empty($operation['scan_path'])) {
                $fileName = basename($operation['scan_path']);
                $html .= '
                <div class="row">
                    <div class="col-md-12">
                        <h4>' . $scan_label . '</h4>
                        <div class="text-center">
                            <a href="' . htmlspecialchars($operation['scan_path']) . '" target="_blank" class="btn btn-primary">
                                <i class="fa fa-eye"></i> Voir le justificatif (' . htmlspecialchars($fileName) . ')
                            </a>
                        </div>
                    </div>
                </div>';
            }
            
            echo json_encode([
                'success' => true,
                'html' => $html
            ]);
            
        } catch (\Exception $e) {
            error_log("Erreur getDetails: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Erreur serveur: ' . $e->getMessage()
            ]);
        }
    }
    
    public function getTableData() {
        error_reporting(0);
        ini_set('display_errors', 0);
        
        header('Content-Type: application/json');
        
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
        $search = isset($_POST['search']) ? trim($_POST['search']) : '';
        $type = isset($_POST['type']) ? trim($_POST['type']) : '';
        $limit = 10;
        $offset = ($page - 1) * $limit;
        
        try {
            $user = isset($_SESSION['user']) ? $_SESSION['user'] : array();
            
            if (!empty($search)) {
                $operations = Operation::search($search, $limit, $offset, $type);
                $total = Operation::searchCount($search, $type);
            } else {
                $operations = Operation::getPaginated($limit, $offset, $type);
                $total = Operation::count($type);
            }
            
            $html = '';
            if (!empty($operations)) {
                foreach ($operations as $operation) {
                    $html .= $this->generateOperationRowHtml($operation, $user);
                }
            } else {
                $html = '<tr id="aucun-operation"><td colspan="11" class="center">Aucune opération trouvée</td></tr>';
            }
            
            $totalPages = ceil($total / $limit);
            
            echo json_encode([
                'success' => true,
                'html' => $html,
                'pagination' => [
                    'page' => $page,
                    'total' => $total,
                    'totalPages' => $totalPages,
                    'limit' => $limit
                ]
            ]);
        } catch (\Exception $e) {
            error_log("Erreur getTableData: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ]);
        }
    }
    
    public function search() {
        error_reporting(0);
        ini_set('display_errors', 0);
        
        header('Content-Type: application/json');
        
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $search = isset($_POST['search']) ? trim($_POST['search']) : '';
        $type = isset($_POST['type']) ? trim($_POST['type']) : '';
        $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;
        
        try {
            $user = isset($_SESSION['user']) ? $_SESSION['user'] : array();
            
            $operations = Operation::search($search, $limit, $offset, $type);
            $total = Operation::searchCount($search, $type);
            
            $html = '';
            if (!empty($operations)) {
                foreach ($operations as $operation) {
                    $html .= $this->generateOperationRowHtml($operation, $user);
                }
            } else {
                $html = '<tr id="aucun-operation"><td colspan="11" class="center">Aucune opération trouvée pour "' . htmlspecialchars($search) . '"</td></tr>';
            }
            
            $totalPages = ceil($total / $limit);
            
            echo json_encode([
                'success' => true,
                'html' => $html,
                'pagination' => [
                    'page' => $page,
                    'total' => $total,
                    'totalPages' => $totalPages,
                    'limit' => $limit
                ]
            ]);
        } catch (\Exception $e) {
            error_log("Erreur search: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ]);
        }
    }
    
    private function generateOperationRowHtml($operation, $user) {
        if (!is_array($operation)) {
            return '';
        }
        
        $statusClass = 'label-warning';
        $statut = isset($operation['statut']) ? $operation['statut'] : 'en cours';
        $statutText = 'En cours';
        
        switch($statut) {
            case 'confirmé': 
                $statusClass = 'label-success'; 
                $statutText = 'Confirmé';
                break;
            case 'annulé': 
                $statusClass = 'label-danger'; 
                $statutText = 'Annulé';
                break;
        }
        
        $etatConfClass = 'label-danger';
        $etatConfText = isset($operation['etat_confirmation']) ? $operation['etat_confirmation'] : 'Non';
        if ($etatConfText === 'Oui') {
            $etatConfClass = 'label-success';
        }
        
        $etatValidClass = 'label-danger';
        $etatValidText = isset($operation['etat_validation']) ? $operation['etat_validation'] : 'Non';
        if ($etatValidText === 'Oui') {
            $etatValidClass = 'label-success';
        }
        
        $operation_id = isset($operation['id']) ? $operation['id'] : 0;
        $type_operation = isset($operation['type_operation']) ? $operation['type_operation'] : 'cheque';
        $nom_client = isset($operation['nom_client']) ? $operation['nom_client'] : '';
        $numero_cheque = isset($operation['numero_cheque']) ? $operation['numero_cheque'] : '';
        $montant = isset($operation['montant']) ? $operation['montant'] : 0;
        $banque = isset($operation['banque']) ? $operation['banque'] : '';
        $agence_id = isset($operation['agence_id']) ? $operation['agence_id'] : '';
        $scan_path = isset($operation['scan_path']) ? $operation['scan_path'] : '';
        
        $agence_nom = 'Agence ' . $agence_id;
        if ($agence_id) {
            try {
                $stmt = Agence::searchById($agence_id);
                if ($stmt) {
                    while ($agence = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                        if (isset($agence['designation'])) {
                            $agence_nom = $agence['designation'];
                            break;
                        }
                    }
                }
            } catch (\Exception $e) {}
        }
        
        $date_entree = '';
        if (isset($operation['date_entree']) && !empty($operation['date_entree'])) {
            try {
                if ($operation['date_entree'] instanceof \DateTime) {
                    $date_entree = $operation['date_entree']->format('d-m-Y H:i');
                } else {
                    $date_entree = date('d-m-Y H:i', strtotime($operation['date_entree']));
                }
            } catch (\Exception $e) {
                $date_entree = $operation['date_entree'];
            }
        }
        
        // Vérifier si l'opération peut être modifiée
        $canBeModified = $this->canOperationBeModified($operation);
        
        // Vérifier si l'utilisateur est le créateur
        $isCreator = $this->isOperationCreator($operation, $user);
        
        $typeIcon = $type_operation === 'virement' ? 
            '<i class="fa fa-exchange text-primary" title="Virement"></i>' : 
            '<i class="fa fa-money text-success" title="Chèque"></i>';
        
        $numeroLabel = $type_operation === 'virement' ? 'Réf.' : 'N°';
        
        $html = '<tr id="operation-' . $operation_id . '">';
        $html .= '<td class="center">' . $typeIcon . '</td>';
        $html .= '<td class="center">' . htmlspecialchars($date_entree) . '</td>';
        $html .= '<td>' . htmlspecialchars($nom_client) . '</td>';
        $html .= '<td><small class="text-muted">' . $numeroLabel . '</small><br>' . htmlspecialchars($numero_cheque) . '</td>';
        $html .= '<td class="text-right">' . number_format($montant, 0, ',', ' ') . ' FCFA</td>';
        $html .= '<td>' . htmlspecialchars($banque) . '</td>';
        $html .= '<td class="center"><span class="label ' . $statusClass . '">' . htmlspecialchars($statutText) . '</span></td>';
        $html .= '<td class="center"><span class="label ' . $etatConfClass . '">' . htmlspecialchars($etatConfText) . '</span></td>';
        $html .= '<td class="center"><span class="label ' . $etatValidClass . '">' . htmlspecialchars($etatValidText) . '</span></td>';
        $html .= '<td>' . htmlspecialchars($agence_nom) . '</td>';
        $html .= '<td class="center">';
        $html .= '<div class="hidden-sm hidden-xs action-buttons">';
        
        // Bouton "Voir les détails" pour tous les utilisateurs
        $html .= '<a href="#" class="details-operation purple" data-id="' . $operation_id . '" title="Voir les détails">';
        $html .= '<i class="ace-icon fa fa-info-circle bigger-130"></i></a> ';
        
        $userPrivilege = isset($user['privilege']) ? $user['privilege'] : '';
        
        // Bouton "Modifier" pour les utilisateurs qui peuvent ajouter, seulement si l'opération peut être modifiée ET si l'utilisateur est le créateur
        $allowedToAdd = in_array($userPrivilege, ['Agence', 'AgenceSage', 'Caissiere', 'CaissiereLD', 'CaissiereSage', 'OPAgence']);
        if ($allowedToAdd && $canBeModified && $isCreator) {
            $html .= '<a href="#" class="modifier-operation green" data-id="' . $operation_id . '" title="Modifier">';
            $html .= '<i class="ace-icon fa fa-pencil bigger-130"></i></a> ';
        }
        
        // Actions spécifiques pour Comptabilité
        if ($userPrivilege === 'Comptabilite') {
            if ($statut === 'en cours') {
                $html .= '<a href="#" class="changer-statut orange" data-id="' . $operation_id . '" title="Changer le statut">';
                $html .= '<i class="ace-icon fa fa-exchange bigger-130"></i></a> ';
            }
            
            $html .= '<a href="#" class="changer-etat-confirmation green" data-id="' . $operation_id . '" title="Changer l\'état de confirmation">';
            $html .= '<i class="ace-icon fa fa-check-circle bigger-130"></i></a> ';
            
            $html .= '<a href="#" class="changer-etat blue" data-id="' . $operation_id . '" title="Changer l\'état de validation">';
            $html .= '<i class="ace-icon fa fa-check-square-o bigger-130"></i></a>';
        }
        
        $html .= '</div></td></tr>';
        
        return $html;
    }
}
?>