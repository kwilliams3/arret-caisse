<?php
namespace Core\Controller;

use Core\Model\App;
use Core\Model\Session;
use Core\Database\Operation;
use Core\Database\Agence;
use Core\Model\AppController;

// Inclusion directe de PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once dirname(dirname(__DIR__)) . '/PHPMailer/src/PHPMailer.php';
require_once dirname(dirname(__DIR__)) . '/PHPMailer/src/SMTP.php';
require_once dirname(dirname(__DIR__)) . '/PHPMailer/src/Exception.php';

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
        
        // Récupérer l'agence de l'utilisateur
        if (isset($user['agence'])) {
            $userAgenceId = $user['agence'];
        } elseif (isset($user['idAgence'])) {
            $userAgenceId = $user['idAgence'];
        } else {
            $userAgenceId = null;
        }

        $userPrivilege = '';
        if (isset($user['privilege'])) {
            $userPrivilege = $user['privilege'];
        }

        // Admin et Comptabilité voient tout, les autres voient seulement leur agence
        $isAdminOrCompta = false;
        $isCompta = false;
        if (stripos($userPrivilege, 'admin') !== false) {
            $isAdminOrCompta = true;
        } elseif (stripos($userPrivilege, 'compta') !== false) {
            $isCompta = true;
            $isAdminOrCompta = true;
        }

        // Récupérer le nom de l'agence de l'utilisateur
        $userAgenceNom = '';
        if ($userAgenceId) {
            try {
                $stmtAgence = Agence::searchById($userAgenceId);
                if ($stmtAgence) {
                    while ($agence = sqlsrv_fetch_array($stmtAgence, SQLSRV_FETCH_ASSOC)) {
                        if (isset($agence['designation'])) {
                            $userAgenceNom = $agence['designation'];
                            break;
                        }
                    }
                }
            } catch (\Exception $e) {
                error_log("Erreur récupération agence utilisateur: " . $e->getMessage());
                $userAgenceNom = "Agence " . $userAgenceId;
            }
        }

        // Récupération des paramètres
        $page = 1;
        if (isset($_GET['page'])) {
            $page = (int)$_GET['page'];
        }

        $search = '';
        if (isset($_GET['search'])) {
            $search = trim($_GET['search']);
        }

        $type = '';
        if (isset($_GET['type'])) {
            $type = $_GET['type'];
        }

        // Récupération des opérations selon le privilège
        if ($isAdminOrCompta) {
            if (!empty($search)) {
                $operations = Operation::search($search, 1000, 0, $type);
            } else {
                $operations = Operation::getAll($type);
            }
        } else {
            if (!empty($search)) {
                $operations = Operation::searchByAgence($search, $userAgenceId, 1000, 0, $type);
            } else {
                $operations = Operation::getByAgence($userAgenceId, $type);
            }
        }

        if (!is_array($operations)) {
            $operations = array();
        }

        // PAGINATION
        $itemsPerPage = 10;
        $totalOperations = count($operations);
        $totalPages = ceil($totalOperations / $itemsPerPage);

        if ($page < 1) $page = 1;
        if ($totalPages > 0 && $page > $totalPages) $page = $totalPages;

        $startIndex = ($page - 1) * $itemsPerPage;
        $endIndex = min($startIndex + $itemsPerPage, $totalOperations);
        $operationsPagines = array_slice($operations, $startIndex, $itemsPerPage);

        // Définir les rôles autorisés à ajouter
        $rolesAjout = array('Agence', 'OPAgence');
        $allowedToAdd = in_array($userPrivilege, $rolesAjout);

        // Données à passer à la vue
        $data = array(
            'operations' => $operations,
            'operationsPagines' => $operationsPagines,
            'user' => $user,
            'totalOperations' => $totalOperations,
            'userAgenceId' => $userAgenceId,
            'userAgenceNom' => $userAgenceNom,
            'userPrivilege' => $userPrivilege,
            'isAdminOrCompta' => $isAdminOrCompta,
            'isCompta' => $isCompta,
            'page' => $page,
            'search' => $search,
            'type' => $type,
            'totalPages' => $totalPages,
            'startIndex' => $startIndex,
            'endIndex' => $endIndex,
            'allowedToAdd' => $allowedToAdd
        );
        
        $this->render('ConfirmationCheque.index', $data);
    }
    
    /**
     * Affiche la page des archives
     */
    public function archives() {
        $session = Session::getInstance();
        $user = $_SESSION['user'];
        
        // Récupérer l'agence de l'utilisateur
        if (isset($user['agence'])) {
            $userAgenceId = $user['agence'];
        } elseif (isset($user['idAgence'])) {
            $userAgenceId = $user['idAgence'];
        } else {
            $userAgenceId = null;
        }
        
        $userPrivilege = isset($user['privilege']) ? $user['privilege'] : '';
        
        // Admin et Comptabilité voient tout, les autres voient seulement leur agence
        $isAdminOrCompta = (stripos($userPrivilege, 'admin') !== false || stripos($userPrivilege, 'compta') !== false);
        
        // Récupération des paramètres - CORRECTION IMPORTANTE
        $page = 1;
        if (isset($_GET['page'])) {
            if (is_array($_GET['page'])) {
                // Si c'est un tableau (page[]=2), prendre la première valeur
                $page = isset($_GET['page'][0]) ? (int)$_GET['page'][0] : 1;
            } else {
                $page = (int)$_GET['page'];
            }
            if ($page < 1) $page = 1;
        }
        
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        $type = isset($_GET['type']) ? $_GET['type'] : '';
        
        // Récupérer le nom de l'agence de l'utilisateur
        $userAgenceNom = '';
        if ($userAgenceId) {
            try {
                $stmtAgence = Agence::searchById($userAgenceId);
                if ($stmtAgence) {
                    while ($agence = sqlsrv_fetch_array($stmtAgence, SQLSRV_FETCH_ASSOC)) {
                        if (isset($agence['designation'])) {
                            $userAgenceNom = $agence['designation'];
                            break;
                        }
                    }
                }
            } catch (\Exception $e) {
                error_log("Erreur récupération agence utilisateur: " . $e->getMessage());
                $userAgenceNom = "Agence " . $userAgenceId;
            }
        }
        
        // PAGINATION
        $itemsPerPage = 10;
        
        // Récupération des archives selon le privilège
        if ($isAdminOrCompta) {
            $totalArchives = Operation::countArchives(null, $type);
            $totalPages = ceil($totalArchives / $itemsPerPage);
            
            if ($totalPages > 0 && $page > $totalPages) $page = $totalPages;
            
            $offset = ($page - 1) * $itemsPerPage;
            $archives = Operation::getArchives(null, $type, $itemsPerPage, $offset);
        } else {
            $totalArchives = Operation::countArchives($userAgenceId, $type);
            $totalPages = ceil($totalArchives / $itemsPerPage);
            
            if ($totalPages > 0 && $page > $totalPages) $page = $totalPages;
            
            $offset = ($page - 1) * $itemsPerPage;
            $archives = Operation::getArchives($userAgenceId, $type, $itemsPerPage, $offset);
        }
        
        if (!is_array($archives)) {
            $archives = array();
        }
        
        $startIndex = ($page - 1) * $itemsPerPage;
        $endIndex = min($startIndex + $itemsPerPage, $totalArchives);
        
        $data = array(
            'archives' => $archives,
            'user' => $user,
            'totalArchives' => $totalArchives,
            'userAgenceId' => $userAgenceId,
            'userAgenceNom' => $userAgenceNom,
            'userPrivilege' => $userPrivilege,
            'isAdminOrCompta' => $isAdminOrCompta,
            'page' => $page,
            'search' => $search,
            'type' => $type,
            'totalPages' => $totalPages,
            'startIndex' => $startIndex,
            'endIndex' => $endIndex,
            'itemsPerPage' => $itemsPerPage
        );
        
        $this->render('ConfirmationCheque.archives', $data);
    }
    
    public function ajoutOperation() {
        error_reporting(0);
        ini_set('display_errors', 0);
        
        header('Content-Type: application/json');
        
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['user']) || empty($_SESSION['user'])) {
            echo json_encode(array(
                'success' => false,
                'message' => 'Utilisateur non connecté. Veuillez vous reconnecter.'
            ));
            return;
        }
        
        $user = $_SESSION['user'];
        
        // Récupérer l'agence de l'utilisateur connecté
        if (isset($user['agence'])) {
            $userAgenceId = $user['agence'];
        } elseif (isset($user['idAgence'])) {
            $userAgenceId = $user['idAgence'];
        } else {
            $userAgenceId = null;
        }
        
        // Vérifier que l'utilisateur a une agence
        if (!$userAgenceId) {
            echo json_encode(array(
                'success' => false,
                'message' => 'Vous n\'êtes pas rattaché à une agence. Impossible d\'ajouter une opération.'
            ));
            return;
        }
        
        $allowedToAdd = in_array($user['privilege'], array('Agence', 'OPAgence'));
        if (!$allowedToAdd) {
            echo json_encode(array(
                'success' => false,
                'message' => 'Vous n\'avez pas la permission d\'ajouter des opérations'
            ));
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(array(
                'success' => false,
                'message' => 'Méthode non autorisée'
            ));
            return;
        }
        
        $errors = array();
        $requiredFields = array('nom_client', 'numero_cheque', 'montant', 'banque', 'date_reception', 'type_operation');
        
        foreach ($requiredFields as $field) {
            if (empty($_POST[$field])) {
                $errors[] = "Le champ '$field' est obligatoire";
            }
        }
        
        // Vérification du type d'opération
        $type_operation = isset($_POST['type_operation']) ? $_POST['type_operation'] : 'cheque';
        if (!in_array($type_operation, array('cheque', 'virement'))) {
            $errors[] = "Type d'opération invalide. Doit être 'cheque' ou 'virement'.";
        }
        
        // VÉRIFICATION OBLIGATOIRE DU SCAN
        if (!isset($_FILES['scan_operation']) || $_FILES['scan_operation']['error'] !== UPLOAD_ERR_OK) {
            $errors[] = "Le justificatif (scan) est obligatoire";
        }
        
        if (!empty($errors)) {
            echo json_encode(array(
                'success' => false,
                'message' => 'Erreurs de validation: ' . implode(', ', $errors)
            ));
            return;
        }
        
        $date_reception = $_POST['date_reception'];
        
        if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $date_reception, $matches)) {
            $date_reception = $matches[3] . '-' . $matches[2] . '-' . $matches[1];
        }
        
        if (!strtotime($date_reception)) {
            echo json_encode(array(
                'success' => false,
                'message' => 'Date de réception invalide'
            ));
            return;
        }
        
        // VÉRIFICATION D'UNICITÉ DU NUMÉRO/RÉFÉRENCE
        $numero_cheque = trim($_POST['numero_cheque']);
        $type_operation = $_POST['type_operation'];
        
        // Vérifier si le numéro existe déjà pour le même type d'opération dans la même agence
        if (Operation::existsByNumero($numero_cheque, $type_operation, $userAgenceId)) {
            echo json_encode(array(
                'success' => false,
                'message' => $type_operation === 'cheque' ? 
                    'Ce numéro de chèque existe déjà dans votre agence' : 
                    'Cette référence de virement existe déjà dans votre agence'
            ));
            return;
        }
        
        $data = array(
            'type_operation' => $type_operation,
            'nom_client' => trim($_POST['nom_client']),
            'numero_cheque' => $numero_cheque,
            'montant' => floatval(str_replace(',', '.', $_POST['montant'])),
            'banque' => trim($_POST['banque']),
            'date_reception' => $date_reception,
            'date_entree' => date('Y-m-d H:i:s'),
            'observations' => isset($_POST['observations']) ? trim($_POST['observations']) : '',
            'agence_id' => $userAgenceId,
            'created_by' => isset($user['idUser']) ? $user['idUser'] : (isset($user['id']) ? $user['id'] : null)
        );
        
        if ($data['montant'] <= 0) {
            echo json_encode(array(
                'success' => false,
                'message' => 'Le montant doit être supérieur à 0'
            ));
            return;
        }
        
        // TRAITEMENT OBLIGATOIRE DU SCAN
        $scanPath = null;
        if (isset($_FILES['scan_operation']) && $_FILES['scan_operation']['error'] === UPLOAD_ERR_OK) {
            $targetDir = "DocumentsCheques/";
            if (!is_dir($targetDir)) {
                if (!mkdir($targetDir, 0777, true)) {
                    echo json_encode(array(
                        'success' => false,
                        'message' => 'Impossible de créer le dossier de destination'
                    ));
                    return;
                }
            }
            
            $allowedTypes = array('image/jpeg', 'image/png', 'application/pdf');
            $fileType = $_FILES['scan_operation']['type'];
            
            if (!in_array($fileType, $allowedTypes)) {
                echo json_encode(array(
                    'success' => false,
                    'message' => 'Type de fichier non autorisé. Formats acceptés: JPG, PNG, PDF'
                ));
                return;
            }
            
            $maxFileSize = 5 * 1024 * 1024;
            if ($_FILES['scan_operation']['size'] > $maxFileSize) {
                echo json_encode(array(
                    'success' => false,
                    'message' => 'Le fichier est trop volumineux (max 5MB)'
                ));
                return;
            }
            
            $prefix = $type_operation === 'cheque' ? 'cheque_' : 'virement_';
            $fileName = $prefix . time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $_FILES['scan_operation']['name']);
            $targetPath = $targetDir . $fileName;
            
            if (move_uploaded_file($_FILES['scan_operation']['tmp_name'], $targetPath)) {
                $scanPath = $targetPath;
            } else {
                echo json_encode(array(
                    'success' => false,
                    'message' => 'Erreur lors du téléchargement du justificatif'
                ));
                return;
            }
        } else {
            echo json_encode(array(
                'success' => false,
                'message' => 'Le justificatif est obligatoire'
            ));
            return;
        }
        
        try {
            $operationId = Operation::add($data, $scanPath);
            
            if (!$operationId) {
                throw new \Exception('Erreur lors de l\'insertion dans la base de données');
            }
            
            $operationInserted = Operation::find($operationId);
            
            if (!$operationInserted) {
                $operationInserted = array(
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
                );
            }
            
            // Récupérer le nom de l'agence pour l'affichage
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
            
            $this->cleanBuffer();
            
            echo json_encode(array(
                'success' => true,
                'message' => 'Opération ajoutée avec succès',
                'operation' => $operationInserted,
                'refreshNeeded' => false
            ));
            
        } catch (\Exception $e) {
            error_log("Erreur ajoutOperation: " . $e->getMessage());
            
            $this->cleanBuffer();
            
            echo json_encode(array(
                'success' => false,
                'message' => 'Erreur serveur: ' . $e->getMessage()
            ));
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
            echo json_encode(array('success' => false, 'message' => 'ID opération manquant'));
            return;
        }
        
        $operation_id = intval($_POST['operation_id']);
        $user = $_SESSION['user'];
        $userAgenceId = isset($user['agence']) ? $user['agence'] : (isset($user['idAgence']) ? $user['idAgence'] : null);
        $userPrivilege = isset($user['privilege']) ? $user['privilege'] : '';
        
        try {
            $operation = Operation::find($operation_id);
            
            if (!$operation) {
                echo json_encode(array('success' => false, 'message' => 'Opération non trouvée'));
                return;
            }
            
            // Les admins et compta peuvent voir toutes les opérations
            $isAdminOrCompta = (stripos($userPrivilege, 'admin') !== false || stripos($userPrivilege, 'compta') !== false);
            
            // Vérifier que l'opération appartient à l'agence de l'utilisateur (sauf admin/compta)
            if (!$isAdminOrCompta && $operation['agence_id'] != $userAgenceId) {
                echo json_encode(array('success' => false, 'message' => 'Accès non autorisé à cette opération'));
                return;
            }
            
            $canBeModified = $this->canOperationBeModified($operation);
            $isCreator = $this->isOperationCreator($operation, $user);
            $userCanModify = $canBeModified && ($isAdminOrCompta || $isCreator);
            
            $this->cleanBuffer();
            
            echo json_encode(array(
                'success' => true,
                'operation' => $operation,
                'canBeModified' => $canBeModified,
                'isCreator' => $isCreator,
                'userCanModify' => $userCanModify
            ));
            
        } catch (\Exception $e) {
            error_log("Erreur getOperationData: " . $e->getMessage());
            
            $this->cleanBuffer();
            
            echo json_encode(array(
                'success' => false,
                'message' => 'Erreur serveur: ' . $e->getMessage()
            ));
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
            echo json_encode(array(
                'success' => false,
                'message' => 'Utilisateur non connecté. Veuillez vous reconnecter.'
            ));
            return;
        }
        
        $user = $_SESSION['user'];
        $userAgenceId = isset($user['agence']) ? $user['agence'] : (isset($user['idAgence']) ? $user['idAgence'] : null);
        $userPrivilege = isset($user['privilege']) ? $user['privilege'] : '';
        
        $allowedToEdit = in_array($user['privilege'], array('Agence', 'OPAgence'));
        if (!$allowedToEdit) {
            echo json_encode(array(
                'success' => false,
                'message' => 'Vous n\'avez pas la permission de modifier des opérations'
            ));
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(array(
                'success' => false,
                'message' => 'Méthode non autorisée'
            ));
            return;
        }
        
        if (!isset($_POST['operation_id'])) {
            echo json_encode(array(
                'success' => false,
                'message' => 'ID opération manquant'
            ));
            return;
        }
        
        $operation_id = intval($_POST['operation_id']);
        
        $operation = Operation::find($operation_id);
        if (!$operation) {
            echo json_encode(array(
                'success' => false,
                'message' => 'Opération non trouvée'
            ));
            return;
        }
        
        $isAdminOrCompta = (stripos($userPrivilege, 'admin') !== false || stripos($userPrivilege, 'compta') !== false);
        
        // Vérifier que l'opération appartient à l'agence de l'utilisateur (sauf admin/compta)
        if (!$isAdminOrCompta && $operation['agence_id'] != $userAgenceId) {
            echo json_encode(array(
                'success' => false,
                'message' => 'Vous ne pouvez modifier que les opérations de votre agence'
            ));
            return;
        }
        
        if (!$this->canOperationBeModified($operation)) {
            echo json_encode(array(
                'success' => false,
                'message' => 'Cette opération ne peut plus être modifiée (statut, état confirmation ou état validation a changé)'
            ));
            return;
        }
        
        if (!$isAdminOrCompta && !$this->isOperationCreator($operation, $user)) {
            echo json_encode(array(
                'success' => false,
                'message' => 'Vous ne pouvez modifier que les opérations que vous avez créées'
            ));
            return;
        }
        
        $errors = array();
        $requiredFields = array('nom_client', 'numero_cheque', 'montant', 'banque', 'date_reception');
        
        foreach ($requiredFields as $field) {
            if (empty($_POST[$field])) {
                $errors[] = "Le champ '$field' est obligatoire";
            }
        }
        
        if (!empty($errors)) {
            echo json_encode(array(
                'success' => false,
                'message' => 'Erreurs de validation: ' . implode(', ', $errors)
            ));
            return;
        }
        
        // VÉRIFICATION D'UNICITÉ POUR LA MODIFICATION
        $numero_cheque = trim($_POST['numero_cheque']);
        $type_operation = $operation['type_operation'];
        
        // Vérifier si le numéro existe déjà (sauf pour cette même opération)
        if (Operation::existsByNumero($numero_cheque, $type_operation, $userAgenceId, $operation_id)) {
            echo json_encode(array(
                'success' => false,
                'message' => $type_operation === 'cheque' ? 
                    'Ce numéro de chèque existe déjà dans votre agence' : 
                    'Cette référence de virement existe déjà dans votre agence'
            ));
            return;
        }
        
        $date_reception = $_POST['date_reception'];
        
        if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $date_reception, $matches)) {
            $date_reception = $matches[3] . '-' . $matches[2] . '-' . $matches[1];
        }
        
        if (!strtotime($date_reception)) {
            echo json_encode(array(
                'success' => false,
                'message' => 'Date de réception invalide'
            ));
            return;
        }
        
        $data = array(
            'nom_client' => trim($_POST['nom_client']),
            'numero_cheque' => $numero_cheque,
            'montant' => floatval(str_replace(',', '.', $_POST['montant'])),
            'banque' => trim($_POST['banque']),
            'date_reception' => $date_reception,
            'observations' => isset($_POST['observations']) ? trim($_POST['observations']) : '',
            'updated_at' => date('Y-m-d H:i:s'),
            'updated_by' => isset($user['idUser']) ? $user['idUser'] : (isset($user['id']) ? $user['id'] : null)
        );
        
        if (isset($operation['type_operation'])) {
            $data['type_operation'] = $operation['type_operation'];
        }
        
        if ($data['montant'] <= 0) {
            echo json_encode(array(
                'success' => false,
                'message' => 'Le montant doit être supérieur à 0'
            ));
            return;
        }
        
        $scanPath = null;
        if (isset($_FILES['scan_operation']) && $_FILES['scan_operation']['error'] === UPLOAD_ERR_OK) {
            $targetDir = "DocumentsCheques/";
            if (!is_dir($targetDir)) {
                if (!mkdir($targetDir, 0777, true)) {
                    echo json_encode(array(
                        'success' => false,
                        'message' => 'Impossible de créer le dossier de destination'
                    ));
                    return;
                }
            }
            
            $allowedTypes = array('image/jpeg', 'image/png', 'application/pdf');
            $fileType = $_FILES['scan_operation']['type'];
            
            if (!in_array($fileType, $allowedTypes)) {
                echo json_encode(array(
                    'success' => false,
                    'message' => 'Type de fichier non autorisé. Formats acceptés: JPG, PNG, PDF'
                ));
                return;
            }
            
            $maxFileSize = 5 * 1024 * 1024;
            if ($_FILES['scan_operation']['size'] > $maxFileSize) {
                echo json_encode(array(
                    'success' => false,
                    'message' => 'Le fichier est trop volumineux (max 5MB)'
                ));
                return;
            }
            
            $type_operation = isset($operation['type_operation']) ? $operation['type_operation'] : 'cheque';
            $prefix = $type_operation === 'cheque' ? 'cheque_' : 'virement_';
            $fileName = $prefix . time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $_FILES['scan_operation']['name']);
            $targetPath = $targetDir . $fileName;
            
            if (move_uploaded_file($_FILES['scan_operation']['tmp_name'], $targetPath)) {
                $scanPath = $targetPath;
                $data['scan_path'] = $scanPath;
                
                if (!empty($operation['scan_path']) && file_exists($operation['scan_path'])) {
                    @unlink($operation['scan_path']);
                }
            } else {
                echo json_encode(array(
                    'success' => false,
                    'message' => 'Erreur lors du téléchargement du nouveau justificatif'
                ));
                return;
            }
        }
        
        try {
            $result = Operation::update($operation_id, $data);
            
            if ($result) {
                $operationUpdated = Operation::find($operation_id);
                
                $this->cleanBuffer();
                
                echo json_encode(array(
                    'success' => true,
                    'message' => 'Opération modifiée avec succès',
                    'operation' => $operationUpdated
                ));
            } else {
                $this->cleanBuffer();
                
                echo json_encode(array(
                    'success' => false,
                    'message' => 'Erreur lors de la modification de l\'opération'
                ));
            }
            
        } catch (\Exception $e) {
            error_log("Erreur updateOperation: " . $e->getMessage());
            
            $this->cleanBuffer();
            
            echo json_encode(array(
                'success' => false,
                'message' => 'Erreur serveur: ' . $e->getMessage()
            ));
        }
    }
    
    private function canOperationBeModified($operation) {
        $statut = isset($operation['statut']) ? strtolower($operation['statut']) : 'en cours';
        $etatConfirmation = isset($operation['etat_confirmation']) ? $operation['etat_confirmation'] : 'Non';
        $etatValidation = isset($operation['etat_validation']) ? $operation['etat_validation'] : 'Non';
        
        return ($statut === 'en cours' && 
                $etatConfirmation === 'Non' && 
                $etatValidation === 'Non');
    }
    
    private function isOperationCreator($operation, $user) {
        $userId = isset($user['idUser']) ? $user['idUser'] : (isset($user['id']) ? $user['id'] : null);
        $userCreatedBy = isset($user['created_by']) ? $user['created_by'] : null;
        $operationCreatedBy = isset($operation['created_by']) ? $operation['created_by'] : null;
        
        if (empty($operationCreatedBy)) {
            return true;
        }
        
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
            echo json_encode(array('success' => false, 'message' => 'Utilisateur non connecté'));
            return;
        }
        
        $user = $_SESSION['user'];
        
        if ($user['privilege'] !== 'Comptabilite') {
            echo json_encode(array('success' => false, 'message' => 'Accès refusé. Réservé à la comptabilité.'));
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(array('success' => false, 'message' => 'Méthode non autorisée'));
            return;
        }
        
        if (!isset($_POST['operation_id']) || !isset($_POST['nouveau_statut'])) {
            echo json_encode(array('success' => false, 'message' => 'Données manquantes'));
            return;
        }
        
        $operation_id = intval($_POST['operation_id']);
        $nouveau_statut = $_POST['nouveau_statut'];
        $observation = isset($_POST['observation']) ? trim($_POST['observation']) : null;
        
        $statuts_valides = array('confirmé', 'annulé');
        if (!in_array($nouveau_statut, $statuts_valides)) {
            echo json_encode(array('success' => false, 'message' => 'Statut invalide. Doit être "confirmé" ou "annulé".'));
            return;
        }
        
        try {
            $userId = isset($user['idUser']) ? $user['idUser'] : (isset($user['id']) ? $user['id'] : null);
            $result = Operation::updateStatut($operation_id, $nouveau_statut, $observation, $userId);
            
            $this->cleanBuffer();
            
            if ($result) {
                echo json_encode(array(
                    'success' => true,
                    'message' => 'Statut de l\'opération mis à jour avec succès'
                ));
            } else {
                echo json_encode(array(
                    'success' => false,
                    'message' => 'Erreur lors de la mise à jour du statut'
                ));
            }
        } catch (\Exception $e) {
            error_log("Erreur changerStatut: " . $e->getMessage());
            
            $this->cleanBuffer();
            
            echo json_encode(array(
                'success' => false,
                'message' => 'Erreur serveur: ' . $e->getMessage()
            ));
        }
    }
    
    /**
     * Récupère l'email de l'agence depuis la table Tb_Agence
     */
    private function getEmailChefAgence($agenceId) {
        $logFile = dirname(dirname(__DIR__)) . '/email_debug.log';
        file_put_contents($logFile, date('Y-m-d H:i:s') . " - Recherche email pour agence ID: $agenceId\n", FILE_APPEND);
        
        try {
            $stmt = Agence::searchById($agenceId);
            if ($stmt) {
                while ($agence = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                    if (isset($agence['email']) && !empty($agence['email'])) {
                        $email = trim($agence['email']);
                        file_put_contents($logFile, date('Y-m-d H:i:s') . " - Email trouvé: $email\n", FILE_APPEND);
                        return $email;
                    }
                }
            }
            
            file_put_contents($logFile, date('Y-m-d H:i:s') . " - Aucun email trouvé pour l'agence $agenceId\n", FILE_APPEND);
            
        } catch (\Exception $e) {
            file_put_contents($logFile, date('Y-m-d H:i:s') . " - Erreur: " . $e->getMessage() . "\n", FILE_APPEND);
        }
        
        return;
    }
    
    /**
     * Envoie une notification selon le type
     */
    private function sendNotification($operationId, $type) {
        $logFile = dirname(dirname(__DIR__)) . '/email_debug.log';
        file_put_contents($logFile, date('Y-m-d H:i:s') . " - sendNotification pour operation $operationId, type: $type\n", FILE_APPEND);
        
        $operation = Operation::find($operationId);
        
        if (!$operation) {
            file_put_contents($logFile, date('Y-m-d H:i:s') . " - Opération non trouvée\n", FILE_APPEND);
            return false;
        }
        
        $agenceId = isset($operation['agence_id']) ? $operation['agence_id'] : 0;
        $emailDestinataire = $this->getEmailChefAgence($agenceId);
        
        file_put_contents($logFile, date('Y-m-d H:i:s') . " - Email destinataire: $emailDestinataire\n", FILE_APPEND);
        
        if ($type === 'confirmation') {
            return $this->sendConfirmationEmail($operation, $emailDestinataire);
        } elseif ($type === 'validation') {
            return $this->sendValidationEmail($operation, $emailDestinataire);
        }
        
        return false;
    }
    
    /**
     * Envoie un email de confirmation
     */
    private function sendConfirmationEmail($operation, $emailDestinataire) {
        $logFile = dirname(dirname(__DIR__)) . '/email_debug.log';
        
        $logMessage = date('Y-m-d H:i:s') . " - Tentative d'envoi email de CONFIRMATION à $emailDestinataire\n";
        file_put_contents($logFile, $logMessage, FILE_APPEND);
        
        try {
            $mail = new PHPMailer(true);
            
            // Configuration SMTP
            $mail->isSMTP();
            $mail->Host = '192.168.0.247';
            $mail->SMTPAuth = true;
            $mail->Username = 'arrets.caisses@groupesorepco.com';
            $mail->Password = 'dirSRPC2854';
            $mail->Port = 587;
            
            // AJOUT CRUCIAL POUR L'ENCODAGE UTF-8
            $mail->CharSet = 'UTF-8';
            
            // Désactiver la vérification SSL
            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );
            
            // Expéditeur et destinataire
            $mail->setFrom('arrets.caisses@groupesorepco.com', 'Service Comptabilité SOREPCO');
            $mail->addAddress($emailDestinataire);
            $mail->isHTML(true);
            
            $typeTexte = ($operation['type_operation'] === 'cheque') ? 'chèque' : 'virement';
            $reference = ($operation['type_operation'] === 'cheque') 
                ? 'n° ' . $operation['numero_cheque'] 
                : 'référence ' . $operation['numero_cheque'];
            
            $mail->Subject = "Confirmation de " . $typeTexte . " " . $reference;
            
            $body = "
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset='UTF-8'>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background: #0047ab; color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0; }
                    .content { background: #f9f9f9; padding: 30px; border: 1px solid #ddd; border-top: none; border-radius: 0 0 5px 5px; }
                    .info { background: white; border-left: 4px solid #0047ab; padding: 15px; margin: 20px 0; }
                    .footer { margin-top: 20px; font-size: 12px; color: #666; text-align: center; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h2>SOREPCO</h2>
                        <p>Service Comptabilité</p>
                    </div>
                    <div class='content'>
                        <p>Bonjour,</p>
                        
                        <p>Nous vous informons que le <strong>$typeTexte $reference</strong> a été <strong style='color: #28a745;'>confirmé</strong> par le service comptabilité de SOREPCO.</p>
                        
                        <div class='info'>
                            <p><strong>Client :</strong> " . htmlspecialchars($operation['nom_client']) . "</p>
                            <p><strong>Montant :</strong> " . number_format($operation['montant'], 0, ',', ' ') . " FCFA</p>
                            <p><strong>Banque :</strong> " . htmlspecialchars($operation['banque']) . "</p>
                        </div>
                        
                        <p>Vous pouvez donc considérer ce paiement comme <strong>effectif</strong>.</p>
                        
                        <p>Restant à votre disposition pour toute information complémentaire.</p>
                        
                        <p>Cordialement,<br>
                        <strong>Service Comptabilité</strong><br>
                        Groupe SOREPCO</p>
                    </div>
                    <div class='footer'>
                        <p>Ce message est automatique, merci de ne pas y répondre.</p>
                    </div>
                </div>
            </body>
            </html>
            ";
            
            $mail->Body = $body;
            $mail->AltBody = strip_tags(str_replace(array('<br>', '</p>'), array("\n", "\n\n"), $body));
            
            $mail->send();
            
            file_put_contents($logFile, date('Y-m-d H:i:s') . " - ✅ Email de CONFIRMATION envoyé avec succès\n", FILE_APPEND);
            return true;
            
        } catch (Exception $e) {
            $errorMsg = date('Y-m-d H:i:s') . " - ❌ Erreur envoi CONFIRMATION: " . $mail->ErrorInfo . "\n";
            file_put_contents($logFile, $errorMsg, FILE_APPEND);
            return false;
        }
    }
    
    /**
     * Envoie un email de validation
     */
    private function sendValidationEmail($operation, $emailDestinataire) {
        $logFile = dirname(dirname(__DIR__)) . '/email_debug.log';
        
        $logMessage = date('Y-m-d H:i:s') . " - Tentative d'envoi email de VALIDATION à $emailDestinataire\n";
        file_put_contents($logFile, $logMessage, FILE_APPEND);
        
        try {
            $mail = new PHPMailer(true);
            
            // Configuration SMTP
            $mail->isSMTP();
            $mail->Host = '192.168.0.247';
            $mail->SMTPAuth = true;
            $mail->Username = 'arrets.caisses@groupesorepco.com';
            $mail->Password = 'dirSRPC2854';
            $mail->Port = 587;
            
            // AJOUT CRUCIAL POUR L'ENCODAGE UTF-8
            $mail->CharSet = 'UTF-8';
            
            // Désactiver la vérification SSL
            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );
            
            // Expéditeur et destinataire
            $mail->setFrom('arrets.caisses@groupesorepco.com', 'Service Comptabilité SOREPCO');
            $mail->addAddress($emailDestinataire);
            $mail->isHTML(true);
            
            $typeTexte = ($operation['type_operation'] === 'cheque') ? 'chèque' : 'virement';
            $reference = ($operation['type_operation'] === 'cheque') 
                ? 'n° ' . $operation['numero_cheque'] 
                : 'référence ' . $operation['numero_cheque'];
            
            $mail->Subject = "Validation de " . $typeTexte . " " . $reference;
            
            $body = "
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset='UTF-8'>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background: #0047ab; color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0; }
                    .content { background: #f9f9f9; padding: 30px; border: 1px solid #ddd; border-top: none; border-radius: 0 0 5px 5px; }
                    .info { background: white; border-left: 4px solid #0047ab; padding: 15px; margin: 20px 0; }
                    .footer { margin-top: 20px; font-size: 12px; color: #666; text-align: center; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h2>SOREPCO</h2>
                        <p>Service Comptabilité</p>
                    </div>
                    <div class='content'>
                        <p>Bonjour,</p>
                        
                        <p>Nous vous informons que le <strong>$typeTexte $reference</strong> a été <strong style='color: #28a745;'>validé</strong> par le service comptabilité de SOREPCO.</p>
                        
                        <div class='info'>
                            <p><strong>Client :</strong> " . htmlspecialchars($operation['nom_client']) . "</p>
                            <p><strong>Montant :</strong> " . number_format($operation['montant'], 0, ',', ' ') . " FCFA</p>
                            <p><strong>Banque :</strong> " . htmlspecialchars($operation['banque']) . "</p>
                        </div>
                        
                        <p>Vous pouvez donc considérer ce paiement comme <strong>effectif</strong>.</p>
                        
                        <p>Restant à votre disposition pour toute information complémentaire.</p>
                        
                        <p>Cordialement,<br>
                        <strong>Service Comptabilité</strong><br>
                        Groupe SOREPCO</p>
                    </div>
                    <div class='footer'>
                        <p>Ce message est automatique, merci de ne pas y répondre.</p>
                    </div>
                </div>
            </body>
            </html>
            ";
            
            $mail->Body = $body;
            $mail->AltBody = strip_tags(str_replace(array('<br>', '</p>'), array("\n", "\n\n"), $body));
            
            $mail->send();
            
            file_put_contents($logFile, date('Y-m-d H:i:s') . " - ✅ Email de VALIDATION envoyé avec succès\n", FILE_APPEND);
            return true;
            
        } catch (Exception $e) {
            $errorMsg = date('Y-m-d H:i:s') . " - ❌ Erreur envoi VALIDATION: " . $mail->ErrorInfo . "\n";
            file_put_contents($logFile, $errorMsg, FILE_APPEND);
            return false;
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
            echo json_encode(array('success' => false, 'message' => 'Utilisateur non connecté'));
            return;
        }
        
        $user = $_SESSION['user'];
        
        if ($user['privilege'] !== 'Comptabilite') {
            echo json_encode(array('success' => false, 'message' => 'Accès refusé. Réservé à la comptabilité.'));
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(array('success' => false, 'message' => 'Méthode non autorisée'));
            return;
        }
        
        if (!isset($_POST['operation_id']) || !isset($_POST['nouvel_etat_validation'])) {
            echo json_encode(array('success' => false, 'message' => 'Données manquantes'));
            return;
        }
        
        $operation_id = intval($_POST['operation_id']);
        $nouvel_etat = $_POST['nouvel_etat_validation'];
        $observation = isset($_POST['observation']) ? trim($_POST['observation']) : '';
        
        $etats_valides = array('Oui', 'Non');
        if (!in_array($nouvel_etat, $etats_valides)) {
            echo json_encode(array('success' => false, 'message' => 'État invalide. Doit être "Oui" ou "Non".'));
            return;
        }
        
        try {
            $userId = isset($user['idUser']) ? $user['idUser'] : (isset($user['id']) ? $user['id'] : null);
            
            $result = Operation::updateEtatValidation($operation_id, $nouvel_etat, $observation, $userId);
            
            if ($result) {
                if ($nouvel_etat === 'Oui') {
                    $this->sendNotification($operation_id, 'validation');
                }
                
                $this->cleanBuffer();
                
                echo json_encode(array(
                    'success' => true,
                    'message' => 'État de validation mis à jour avec succès'
                ));
            } else {
                $this->cleanBuffer();
                
                echo json_encode(array(
                    'success' => false,
                    'message' => 'Erreur lors de la mise à jour de l\'état'
                ));
            }
        } catch (\Exception $e) {
            error_log("Erreur changerEtat: " . $e->getMessage());
            
            $this->cleanBuffer();
            
            echo json_encode(array(
                'success' => false,
                'message' => 'Erreur serveur: ' . $e->getMessage()
            ));
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
            echo json_encode(array('success' => false, 'message' => 'Utilisateur non connecté'));
            return;
        }
        
        $user = $_SESSION['user'];
        
        if ($user['privilege'] !== 'Comptabilite') {
            echo json_encode(array('success' => false, 'message' => 'Accès refusé. Réservé à la comptabilité.'));
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(array('success' => false, 'message' => 'Méthode non autorisée'));
            return;
        }
        
        if (!isset($_POST['operation_id']) || !isset($_POST['nouvel_etat_confirmation'])) {
            echo json_encode(array('success' => false, 'message' => 'Données manquantes'));
            return;
        }
        
        $operation_id = intval($_POST['operation_id']);
        $nouvel_etat = $_POST['nouvel_etat_confirmation'];
        $observation = isset($_POST['observation']) ? trim($_POST['observation']) : '';
        
        $etats_valides = array('Oui', 'Non');
        if (!in_array($nouvel_etat, $etats_valides)) {
            echo json_encode(array('success' => false, 'message' => 'État invalide. Doit être "Oui" ou "Non".'));
            return;
        }
        
        try {
            $userId = isset($user['idUser']) ? $user['idUser'] : (isset($user['id']) ? $user['id'] : null);
            
            $result = Operation::updateEtatConfirmation($operation_id, $nouvel_etat, $observation, $userId);
            
            if ($result) {
                if ($nouvel_etat === 'Oui') {
                    $this->sendNotification($operation_id, 'confirmation');
                }
                
                $this->cleanBuffer();
                
                echo json_encode(array(
                    'success' => true,
                    'message' => 'État de confirmation mis à jour avec succès'
                ));
            } else {
                $this->cleanBuffer();
                
                echo json_encode(array(
                    'success' => false,
                    'message' => 'Erreur lors de la mise à jour'
                ));
            }
        } catch (\Exception $e) {
            error_log("Erreur changerEtatConfirmation: " . $e->getMessage());
            
            $this->cleanBuffer();
            
            echo json_encode(array(
                'success' => false,
                'message' => 'Erreur serveur: ' . $e->getMessage()
            ));
        }
    }
    
    /**
     * Génère une carte de suivi au format PNG simplifiée et claire
     */
    public function genererCartePNG() {
        // Nettoyage complet du buffer avant toute sortie
        $this->cleanBuffer();
        
        error_reporting(0);
        ini_set('display_errors', 0);
        
        header('Content-Type: image/png');
        
        // Vérification de session et authentification
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['user']) || empty($_SESSION['user'])) {
            $this->genererImageErreur('Utilisateur non connecté');
            return;
        }
        
        $user = $_SESSION['user'];
        $userAgenceId = isset($user['agence']) ? $user['agence'] : (isset($user['idAgence']) ? $user['idAgence'] : null);
        $userPrivilege = isset($user['privilege']) ? $user['privilege'] : '';
        $isAdminOrCompta = (stripos($userPrivilege, 'admin') !== false || stripos($userPrivilege, 'compta') !== false);
        
        if (!isset($_POST['operation_id'])) {
            $this->genererImageErreur('ID opération manquant');
            return;
        }
        
        $operation_id = intval($_POST['operation_id']);
        $titre_carte = isset($_POST['titre_carte']) ? trim($_POST['titre_carte']) : 'CARTE DE SUIVI';
        
        try {
            $operation = Operation::find($operation_id);
            
            if (!$operation) {
                $this->genererImageErreur('Opération non trouvée');
                return;
            }
            
            // Vérifier que l'utilisateur a accès à cette opération
            if (!$isAdminOrCompta && $operation['agence_id'] != $userAgenceId) {
                $this->genererImageErreur('Accès non autorisé');
                return;
            }
            
            // Vérifier que les fonctions GD sont disponibles
            if (!function_exists('imagecreatetruecolor')) {
                $this->genererImageErreur('Bibliothèque GD non disponible');
                return;
            }
            
            // Récupérer les informations de l'agence
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
            
            if (empty($agence_nom)) {
                $agence_nom = 'Agence ' . $operation['agence_id'];
            }
            
            // Formatage des dates
            $date_aujourdhui = date('d/m/Y');
            $heure_aujourdhui = date('H:i:s');
            
            // DATE DE RÉCEPTION
            $date_reception = 'Non définie';
            if (isset($operation['date_reception']) && !empty($operation['date_reception'])) {
                if ($operation['date_reception'] instanceof \DateTime) {
                    $date_reception = $operation['date_reception']->format('d/m/Y');
                } else {
                    $timestamp = strtotime($operation['date_reception']);
                    if ($timestamp && $timestamp > 0) {
                        $date_reception = date('d/m/Y', $timestamp);
                    }
                }
            }
            
            // DATE D'ENTRÉE
            $date_entree = 'Non définie';
            if (isset($operation['date_entree']) && !empty($operation['date_entree'])) {
                if ($operation['date_entree'] instanceof \DateTime) {
                    $date_entree = $operation['date_entree']->format('d/m/Y H:i');
                } else {
                    $timestamp = strtotime($operation['date_entree']);
                    if ($timestamp && $timestamp > 0) {
                        $date_entree = date('d/m/Y H:i', $timestamp);
                    }
                }
            }
            
            // OBSERVATIONS
            $observations = isset($operation['observations']) && !empty($operation['observations']) 
                ? $operation['observations'] 
                : 'Aucune observation';
            
            // Type d'opération et libellés
            $type_operation = isset($operation['type_operation']) ? $operation['type_operation'] : 'cheque';
            $type_label = $type_operation === 'cheque' ? 'CHÈQUE' : 'VIREMENT';
            $numero_label = $type_operation === 'cheque' ? 'N° Chèque' : 'Référence';
            
            // Statuts
            $statut = isset($operation['statut']) ? strtoupper($operation['statut']) : 'EN COURS';
            $etatConfirmation = isset($operation['etat_confirmation']) ? $operation['etat_confirmation'] : 'Non';
            $etatValidation = isset($operation['etat_validation']) ? $operation['etat_validation'] : 'Non';
            
            // Dimensions de l'image
            $width = 750;
            $height = 500;
            
            // Création de l'image
            $image = imagecreatetruecolor($width, $height);
            
            if (!$image) {
                $this->genererImageErreur('Impossible de créer l\'image');
                return;
            }
            
            // Définition des couleurs
            $white = imagecolorallocate($image, 255, 255, 255);
            $black = imagecolorallocate($image, 0, 0, 0);
            $darkBlue = imagecolorallocate($image, 0, 51, 102);
            $lightBlue = imagecolorallocate($image, 240, 248, 255);
            $gray = imagecolorallocate($image, 128, 128, 128);
            $lightGray = imagecolorallocate($image, 240, 240, 240);
            $green = imagecolorallocate($image, 40, 167, 69);
            $red = imagecolorallocate($image, 220, 53, 69);
            $orange = imagecolorallocate($image, 255, 193, 7);
            
            // Fond blanc
            imagefill($image, 0, 0, $white);
            
            // Bordure simple
            imagerectangle($image, 5, 5, $width - 5, $height - 5, $darkBlue);
            
            // EN-TÊTE
            imagefilledrectangle($image, 10, 10, $width - 10, 60, $darkBlue);
            
            // Titre
            $this->drawText($image, 18, $width/2, 35, $white, $titre_carte, 'center', true);
            
            // Sous-titre
            $this->drawText($image, 11, $width/2, 52, $white, 'Opération #' . $operation_id, 'center', false);
            
            // INFORMATIONS PRINCIPALES
            $y = 80;
            $leftX = 30;
            $rightX = 400;
            $lineHeight = 25;
            $labelWidth = 100;
            
            // Type et client
            $this->drawText($image, 11, $leftX, $y, $black, 'Type:', 'left', true);
            $this->drawText($image, 11, $leftX + $labelWidth, $y, $darkBlue, $type_label, 'left', false);
            
            $this->drawText($image, 11, $rightX, $y, $black, 'Client:', 'left', true);
            $this->drawText($image, 11, $rightX + $labelWidth, $y, $darkBlue, $this->truncateText($operation['nom_client'], 25), 'left', false);
            $y += $lineHeight;
            
            // Numéro/Référence et Banque
            $this->drawText($image, 11, $leftX, $y, $black, $numero_label . ':', 'left', true);
            $this->drawText($image, 11, $leftX + $labelWidth, $y, $darkBlue, $operation['numero_cheque'], 'left', false);
            
            $this->drawText($image, 11, $rightX, $y, $black, 'Banque:', 'left', true);
            $this->drawText($image, 11, $rightX + $labelWidth, $y, $darkBlue, $operation['banque'], 'left', false);
            $y += $lineHeight;
            
            // Montant
            $montant_formate = number_format($operation['montant'], 0, ',', ' ') . ' FCFA';
            $this->drawText($image, 11, $leftX, $y, $black, 'Montant:', 'left', true);
            $this->drawText($image, 12, $leftX + $labelWidth, $y, $darkBlue, $montant_formate, 'left', true);
            $y += $lineHeight;
            
            // Agence
            $this->drawText($image, 11, $leftX, $y, $black, 'Agence:', 'left', true);
            $this->drawText($image, 11, $leftX + $labelWidth, $y, $darkBlue, $this->truncateText($agence_nom, 30), 'left', false);
            $y += $lineHeight;
            
            // Dates
            $this->drawText($image, 11, $leftX, $y, $black, 'Réception:', 'left', true);
            $this->drawText($image, 11, $leftX + $labelWidth, $y, $darkBlue, $date_reception, 'left', false);
            
            $this->drawText($image, 11, $rightX, $y, $black, 'Entrée:', 'left', true);
            $this->drawText($image, 11, $rightX + $labelWidth, $y, $darkBlue, $date_entree, 'left', false);
            $y += $lineHeight;
            
            // Ligne de séparation
            imageline($image, 20, $y + 5, $width - 20, $y + 5, $lightGray);
            $y += 20;
            
            // SECTION VALIDATION
            $this->drawText($image, 12, $leftX, $y, $darkBlue, 'VALIDATION', 'left', true);
            $y += 20;
            
            // Statut avec couleur
            $statut_color = $statut == 'CONFIRMÉ' ? $green : ($statut == 'ANNULÉ' ? $red : $orange);
            $this->drawText($image, 11, $leftX, $y, $black, 'Statut:', 'left', true);
            imagefilledrectangle($image, $leftX + $labelWidth, $y - 15, $leftX + $labelWidth + 80, $y - 2, $statut_color);
            $this->drawText($image, 10, $leftX + $labelWidth + 40, $y - 5, $white, $statut, 'center', true);
            $y += $lineHeight;
            
            // États confirmation et validation
            $conf_color = $etatConfirmation == 'Oui' ? $green : $red;
            $valid_color = $etatValidation == 'Oui' ? $green : $red;
            
            $this->drawText($image, 11, $leftX, $y, $black, 'Confirmation:', 'left', true);
            imagefilledrectangle($image, $leftX + $labelWidth, $y - 15, $leftX + $labelWidth + 50, $y - 2, $conf_color);
            $this->drawText($image, 10, $leftX + $labelWidth + 25, $y - 5, $white, $etatConfirmation, 'center', true);
            
            $this->drawText($image, 11, $rightX, $y, $black, 'Validation:', 'left', true);
            imagefilledrectangle($image, $rightX + $labelWidth - 20, $y - 15, $rightX + $labelWidth + 30, $y - 2, $valid_color);
            $this->drawText($image, 10, $rightX + $labelWidth + 5, $y - 5, $white, $etatValidation, 'center', true);
            $y += $lineHeight + 10;
            
            // OBSERVATIONS
            $this->drawText($image, 11, $leftX, $y, $darkBlue, 'OBSERVATIONS:', 'left', true);
            $y += 18;
            
            // Affichage des observations avec retour à la ligne
            $obs_lines = $this->wrapText($observations, 80);
            foreach ($obs_lines as $line) {
                $this->drawText($image, 10, $leftX, $y, $black, $line, 'left', false);
                $y += 18;
            }
            
            // PIED DE PAGE
            $footer_y = $height - 30;
            imageline($image, 20, $footer_y - 5, $width - 20, $footer_y - 5, $lightGray);
            
            $this->drawText($image, 8, 30, $footer_y, $gray, 'Généré le ' . $date_aujourdhui . ' à ' . $heure_aujourdhui, 'left', false);
            $this->drawText($image, 8, $width - 30, $footer_y, $gray, 'Arrêts Caisses - Document officiel', 'right', false);
            
            // Génération de l'image
            imagepng($image);
            imagedestroy($image);
            
        } catch (\Exception $e) {
            error_log("Erreur genererCartePNG: " . $e->getMessage());
            $this->genererImageErreur('Erreur: ' . substr($e->getMessage(), 0, 50));
        }
    }
    
    /**
     * Fonction unifiée pour dessiner du texte (avec ou sans TrueType)
     */
    private function drawText($image, $size, $x, $y, $color, $text, $align = 'left', $bold = false) {
        // Gestion de l'alignement
        if ($align != 'left') {
            if ($bold) {
                $font = $this->getFontBoldPath();
            } else {
                $font = $this->getFontPath();
            }
            
            if ($font && file_exists($font)) {
                $bbox = imagettfbbox($size, 0, $font, $text);
                $text_width = $bbox[2] - $bbox[0];
                
                if ($align == 'center') {
                    $x = $x - ($text_width / 2);
                } elseif ($align == 'right') {
                    $x = $x - $text_width;
                }
            } else {
                // Approximation pour la police GD
                $char_width = $size / 2;
                $text_width = strlen($text) * $char_width;
                
                if ($align == 'center') {
                    $x = $x - ($text_width / 2);
                } elseif ($align == 'right') {
                    $x = $x - $text_width;
                }
            }
        }
        
        // Tentative avec TrueType si disponible
        if ($bold) {
            $font = $this->getFontBoldPath();
        } else {
            $font = $this->getFontPath();
        }
        
        if ($font && file_exists($font)) {
            imagettftext($image, $size, 0, $x, $y, $color, $font, $text);
        } else {
            // Fallback vers les polices GD intégrées
            $gd_size = 5;
            if ($size <= 8) $gd_size = 1;
            elseif ($size <= 10) $gd_size = 2;
            elseif ($size <= 12) $gd_size = 3;
            elseif ($size <= 16) $gd_size = 4;
            else $gd_size = 5;
            
            // Pour les polices GD, on est obligé de convertir en ISO-8859-1
            $text_gd = utf8_decode($text);
            imagestring($image, $gd_size, (int)$x, (int)($y - 15), $text_gd, $color);
        }
    }
    
    /**
     * Retourne le chemin de la police (compatible Linux/Windows)
     */
    private function getFontPath() {
        // Polices disponibles sur Linux (Debian/Ubuntu)
        if (file_exists('/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf')) {
            return '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf';
        }
        if (file_exists('/usr/share/fonts/truetype/liberation/LiberationSans-Regular.ttf')) {
            return '/usr/share/fonts/truetype/liberation/LiberationSans-Regular.ttf';
        }
        if (file_exists('/usr/share/fonts/truetype/arial.ttf')) {
            return '/usr/share/fonts/truetype/arial.ttf';
        }
        
        // Polices Windows
        if (file_exists('C:/Windows/Fonts/arial.ttf')) {
            return 'C:/Windows/Fonts/arial.ttf';
        }
        if (file_exists('C:/Windows/Fonts/Arial.ttf')) {
            return 'C:/Windows/Fonts/Arial.ttf';
        }
        
        return null;
    }
    
    /**
     * Retourne le chemin de la police en gras
     */
    private function getFontBoldPath() {
        // Polices Linux
        if (file_exists('/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf')) {
            return '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf';
        }
        if (file_exists('/usr/share/fonts/truetype/liberation/LiberationSans-Bold.ttf')) {
            return '/usr/share/fonts/truetype/liberation/LiberationSans-Bold.ttf';
        }
        if (file_exists('/usr/share/fonts/truetype/arialbd.ttf')) {
            return '/usr/share/fonts/truetype/arialbd.ttf';
        }
        
        // Polices Windows
        if (file_exists('C:/Windows/Fonts/arialbd.ttf')) {
            return 'C:/Windows/Fonts/arialbd.ttf';
        }
        if (file_exists('C:/Windows/Fonts/Arialbd.ttf')) {
            return 'C:/Windows/Fonts/Arialbd.ttf';
        }
        
        return $this->getFontPath();
    }
    
    /**
     * Génère une image d'erreur simple
     */
    private function genererImageErreur($message) {
        $this->cleanBuffer();
        
        header('Content-Type: image/png');
        
        $width = 600;
        $height = 200;
        
        $image = imagecreatetruecolor($width, $height);
        
        if (!$image) {
            header('Content-Type: text/plain');
            die('Erreur: ' . $message);
        }
        
        $white = imagecolorallocate($image, 255, 255, 255);
        $red = imagecolorallocate($image, 255, 0, 0);
        $black = imagecolorallocate($image, 0, 0, 0);
        
        imagefill($image, 0, 0, $white);
        imagerectangle($image, 5, 5, $width - 5, $height - 5, $red);
        
        $this->drawText($image, 20, $width/2, 70, $red, 'ERREUR', 'center', true);
        $this->drawText($image, 14, $width/2, 120, $black, $message, 'center', false);
        $this->drawText($image, 10, $width/2, 160, $black, 'Veuillez contacter le support', 'center', false);
        
        imagepng($image);
        imagedestroy($image);
        exit;
    }
    
    private function truncateText($text, $length) {
        if (strlen($text) <= $length) {
            return $text;
        }
        return substr($text, 0, $length) . '...';
    }
    
    private function wrapText($text, $maxChars) {
        $words = explode(' ', $text);
        $lines = array();
        $currentLine = '';
        
        foreach ($words as $word) {
            if (strlen($currentLine . ' ' . $word) <= $maxChars) {
                $currentLine .= ($currentLine === '' ? '' : ' ') . $word;
            } else {
                if ($currentLine !== '') {
                    $lines[] = $currentLine;
                }
                $currentLine = $word;
            }
        }
        
        if ($currentLine !== '') {
            $lines[] = $currentLine;
        }
        
        return $lines;
    }
    
    public function getDetails() {
        error_reporting(0);
        ini_set('display_errors', 0);
        
        header('Content-Type: application/json');
        
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_POST['operation_id'])) {
            echo json_encode(array('success' => false, 'message' => 'ID opération manquant'));
            return;
        }
        
        $operation_id = intval($_POST['operation_id']);
        $user = $_SESSION['user'];
        $userAgenceId = isset($user['agence']) ? $user['agence'] : (isset($user['idAgence']) ? $user['idAgence'] : null);
        $userPrivilege = isset($user['privilege']) ? $user['privilege'] : '';
        
        try {
            $operation = Operation::find($operation_id);
            
            if (!$operation) {
                echo json_encode(array('success' => false, 'message' => 'Opération non trouvée'));
                return;
            }
            
            // Les admins et compta peuvent voir toutes les opérations
            $isAdminOrCompta = (stripos($userPrivilege, 'admin') !== false || stripos($userPrivilege, 'compta') !== false);
            
            // Vérifier que l'opération appartient à l'agence de l'utilisateur (sauf admin/compta)
            if (!$isAdminOrCompta && $operation['agence_id'] != $userAgenceId) {
                echo json_encode(array('success' => false, 'message' => 'Accès non autorisé à cette opération'));
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
            if (isset($operation['date_reception']) && !empty($operation['date_reception'])) {
                if ($operation['date_reception'] instanceof \DateTime) {
                    $date_reception = $operation['date_reception']->format('d/m/Y');
                } elseif (is_string($operation['date_reception'])) {
                    $timestamp = strtotime($operation['date_reception']);
                    if ($timestamp && $timestamp > 0) {
                        $date_reception = date('d/m/Y', $timestamp);
                    } else {
                        $date_reception = 'Date invalide';
                    }
                }
            } else {
                $date_reception = 'Non définie';
            }
            
            $date_entree = '';
            if (isset($operation['date_entree']) && !empty($operation['date_entree'])) {
                if ($operation['date_entree'] instanceof \DateTime) {
                    $date_entree = $operation['date_entree']->format('d/m/Y H:i');
                } elseif (is_string($operation['date_entree'])) {
                    $timestamp = strtotime($operation['date_entree']);
                    if ($timestamp && $timestamp > 0) {
                        $date_entree = date('d/m/Y H:i', $timestamp);
                    } else {
                        $date_entree = 'Date invalide';
                    }
                }
            } else {
                $date_entree = 'Non définie';
            }
            
            $date_validation = '';
            if (isset($operation['date_validation']) && !empty($operation['date_validation'])) {
                if ($operation['date_validation'] instanceof \DateTime) {
                    $date_validation = $operation['date_validation']->format('d/m/Y H:i');
                } elseif (is_string($operation['date_validation'])) {
                    $timestamp = strtotime($operation['date_validation']);
                    if ($timestamp && $timestamp > 0) {
                        $date_validation = date('d/m/Y H:i', $timestamp);
                    }
                }
            }
            
            $date_confirmation = '';
            if (isset($operation['date_confirmation']) && !empty($operation['date_confirmation'])) {
                if ($operation['date_confirmation'] instanceof \DateTime) {
                    $date_confirmation = $operation['date_confirmation']->format('d/m/Y H:i');
                } elseif (is_string($operation['date_confirmation'])) {
                    $timestamp = strtotime($operation['date_confirmation']);
                    if ($timestamp && $timestamp > 0) {
                        $date_confirmation = date('d/m/Y H:i', $timestamp);
                    }
                }
            }
            
            $statutClass = 'label-warning';
            $statut = isset($operation['statut']) ? $operation['statut'] : 'en cours';
            if ($statut === 'confirmé') {
                $statutClass = 'label-success';
            } elseif ($statut === 'annulé') {
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
                            <th>Observations générales:</th>
                            <td>' . nl2br(htmlspecialchars($observations ?: 'Aucune observation')) . '</td>
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
                                    ' . ucfirst($statut) . '
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
                                <i class="fa fa-eye"></i> Voir le justificatif
                            </a>
                        </div>
                    </div>
                </div>';
            }
            
            $this->cleanBuffer();
            
            echo json_encode(array(
                'success' => true,
                'html' => $html
            ));
            
        } catch (\Exception $e) {
            error_log("Erreur getDetails: " . $e->getMessage());
            
            $this->cleanBuffer();
            
            echo json_encode(array(
                'success' => false,
                'message' => 'Erreur serveur: ' . $e->getMessage()
            ));
        }
    }
    
    public function getTableData() {
        error_reporting(0);
        ini_set('display_errors', 0);
        
        header('Content-Type: application/json');
        
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $user = $_SESSION['user'];
        $userAgenceId = isset($user['agence']) ? $user['agence'] : (isset($user['idAgence']) ? $user['idAgence'] : null);
        $userPrivilege = isset($user['privilege']) ? $user['privilege'] : '';
        $isAdminOrCompta = (stripos($userPrivilege, 'admin') !== false || stripos($userPrivilege, 'compta') !== false);
        
        $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
        $search = isset($_POST['search']) ? trim($_POST['search']) : '';
        $type = isset($_POST['type']) ? trim($_POST['type']) : '';
        $limit = 10;
        $offset = ($page - 1) * $limit;
        
        try {
            if ($isAdminOrCompta) {
                if (!empty($search)) {
                    $operations = Operation::search($search, $limit, $offset, $type);
                    $total = Operation::searchCount($search, $type);
                } else {
                    $operations = Operation::getPaginated($limit, $offset, $type);
                    $total = Operation::count($type);
                }
            } else {
                if (!empty($search)) {
                    $operations = Operation::searchByAgence($search, $userAgenceId, $limit, $offset, $type);
                    $total = Operation::searchCountByAgence($search, $userAgenceId, $type);
                } else {
                    $operations = Operation::getPaginatedByAgence($userAgenceId, $limit, $offset, $type);
                    $total = Operation::countByAgence($userAgenceId, $type);
                }
            }
            
            $html = '';
            if (!empty($operations)) {
                foreach ($operations as $operation) {
                    $html .= $this->generateOperationRowHtml($operation, $user);
                }
            } else {
                $colspan = 11;
                $message = $isAdminOrCompta ? 'Aucune opération trouvée' : 'Aucune opération trouvée pour votre agence';
                $html = '<tr id="aucun-operation"><td colspan="' . $colspan . '" class="center">' . $message . '</td></tr>';
            }
            
            $totalPages = ceil($total / $limit);
            
            $this->cleanBuffer();
            
            echo json_encode(array(
                'success' => true,
                'html' => $html,
                'pagination' => array(
                    'page' => $page,
                    'total' => $total,
                    'totalPages' => $totalPages,
                    'limit' => $limit
                )
            ));
        } catch (\Exception $e) {
            error_log("Erreur getTableData: " . $e->getMessage());
            
            $this->cleanBuffer();
            
            echo json_encode(array(
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ));
        }
    }
    
    public function search() {
        error_reporting(0);
        ini_set('display_errors', 0);
        
        header('Content-Type: application/json');
        
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $user = $_SESSION['user'];
        $userAgenceId = isset($user['agence']) ? $user['agence'] : (isset($user['idAgence']) ? $user['idAgence'] : null);
        $userPrivilege = isset($user['privilege']) ? $user['privilege'] : '';
        $isAdminOrCompta = (stripos($userPrivilege, 'admin') !== false || stripos($userPrivilege, 'compta') !== false);
        
        $search = isset($_POST['search']) ? trim($_POST['search']) : '';
        $type = isset($_POST['type']) ? trim($_POST['type']) : '';
        $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;
        
        try {
            if ($isAdminOrCompta) {
                $operations = Operation::search($search, $limit, $offset, $type);
                $total = Operation::searchCount($search, $type);
            } else {
                $operations = Operation::searchByAgence($search, $userAgenceId, $limit, $offset, $type);
                $total = Operation::searchCountByAgence($search, $userAgenceId, $type);
            }
            
            $html = '';
            if (!empty($operations)) {
                foreach ($operations as $operation) {
                    $html .= $this->generateOperationRowHtml($operation, $user);
                }
            } else {
                $colspan = 11;
                $message = $isAdminOrCompta ? 
                    'Aucune opération trouvée pour "' . htmlspecialchars($search) . '"' : 
                    'Aucune opération trouvée pour "' . htmlspecialchars($search) . '" dans votre agence';
                $html = '<tr id="aucun-operation"><td colspan="' . $colspan . '" class="center">' . $message . '</td></tr>';
            }
            
            $totalPages = ceil($total / $limit);
            
            $this->cleanBuffer();
            
            echo json_encode(array(
                'success' => true,
                'html' => $html,
                'pagination' => array(
                    'page' => $page,
                    'total' => $total,
                    'totalPages' => $totalPages,
                    'limit' => $limit
                )
            ));
        } catch (\Exception $e) {
            error_log("Erreur search: " . $e->getMessage());
            
            $this->cleanBuffer();
            
            echo json_encode(array(
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ));
        }
    }
    
    private function generateOperationRowHtml($operation, $user) {
        if (!is_array($operation)) {
            return '';
        }
        
        $statusClass = 'label-warning';
        $statut = isset($operation['statut']) ? $operation['statut'] : 'en cours';
        $statutText = 'En cours';
        
        if ($statut === 'confirmé' || $statut === 'confirme') {
            $statusClass = 'label-success';
            $statutText = 'Confirmé';
        } elseif ($statut === 'annulé' || $statut === 'annule') {
            $statusClass = 'label-danger';
            $statutText = 'Annulé';
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
            if ($operation['date_entree'] instanceof \DateTime) {
                $date_entree = $operation['date_entree']->format('d-m-Y H:i');
            } elseif (is_string($operation['date_entree'])) {
                $timestamp = strtotime($operation['date_entree']);
                if ($timestamp && $timestamp > 0) {
                    $date_entree = date('d-m-Y H:i', $timestamp);
                }
            }
        }
        
        $canBeModified = $this->canOperationBeModified($operation);
        $isCreator = $this->isOperationCreator($operation, $user);
        $userPrivilege = isset($user['privilege']) ? $user['privilege'] : '';
        $isAdminOrCompta = (stripos($userPrivilege, 'admin') !== false || stripos($userPrivilege, 'compta') !== false);
        
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
        
        $html .= '<a href="#" class="details-operation purple" data-id="' . $operation_id . '" title="Voir les détails">';
        $html .= '<i class="ace-icon fa fa-info-circle bigger-130"></i></a> ';
        
        $allowedToAdd = in_array($userPrivilege, array('Agence', 'OPAgence'));
        
        if ($allowedToAdd && $canBeModified && ($isAdminOrCompta || $isCreator)) {
            $html .= '<a href="#" class="modifier-operation green" data-id="' . $operation_id . '" title="Modifier">';
            $html .= '<i class="ace-icon fa fa-pencil bigger-130"></i></a> ';
        }
        
        if ($userPrivilege === 'Comptabilite' || $isAdminOrCompta) {
            if ($statutText === 'En cours') {
                $html .= '<a href="#" class="changer-statut orange" data-id="' . $operation_id . '" title="Changer le statut">';
                $html .= '<i class="ace-icon fa fa-exchange bigger-130"></i></a> ';
            }
            
            if ($etatConfText === 'Non' && $statutText === 'En cours') {
                $html .= '<a href="#" class="changer-etat-confirmation green" data-id="' . $operation_id . '" title="Changer l\'état de confirmation">';
                $html .= '<i class="ace-icon fa fa-check-circle bigger-130"></i></a> ';
            }
            
            if ($etatValidText === 'Non' && $statutText === 'En cours') {
                $html .= '<a href="#" class="changer-etat blue" data-id="' . $operation_id . '" title="Changer l\'état de validation">';
                $html .= '<i class="ace-icon fa fa-check-square-o bigger-130"></i></a>';
            }
        }
        
        $html .= '</div></td></tr>';
        
        return $html;
    }
    
    /**
     * Nettoie le buffer de sortie
     */
    private function cleanBuffer() {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
    }
}
?>