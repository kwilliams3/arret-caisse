<?php
namespace Core\Controller;

use Core\Model\App;
use Core\Model\Session;
use Core\Model\AppController;
use Core\Database\EffetCommerce;
use Core\Database\Agence;

// Inclusion PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once dirname(dirname(__DIR__)) . '/PHPMailer/src/PHPMailer.php';
require_once dirname(dirname(__DIR__)) . '/PHPMailer/src/SMTP.php';
require_once dirname(dirname(__DIR__)) . '/PHPMailer/src/Exception.php';

class EffetCommerceController extends AppController
{
    public function __construct() {
        parent::__construct();
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    /**
     * Page d'index (liste des opérations)
     */
    public function index() {
        $session = Session::getInstance();
        $user = $_SESSION['user'];
        
        // Récupérer l'agence de l'utilisateur
        $userAgenceId = isset($user['agence']) ? $user['agence'] : (isset($user['idAgence']) ? $user['idAgence'] : null);
        $userPrivilege = isset($user['privilege']) ? $user['privilege'] : '';
        
        // Admin et Compta voient tout
        $isAdminOrCompta = (stripos($userPrivilege, 'admin') !== false || stripos($userPrivilege, 'compta') !== false);
        
        // Récupération des paramètres
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        $type = isset($_GET['type']) ? $_GET['type'] : '';
        
        // Récupération des opérations selon le privilège
        if ($isAdminOrCompta) {
            if (!empty($search)) {
                $operations = EffetCommerce::search($search, 1000, 0, $type);
            } else {
                $operations = EffetCommerce::getAll($type);
            }
        } else {
            if (!empty($search)) {
                $operations = EffetCommerce::searchByAgence($search, $userAgenceId, 1000, 0, $type);
            } else {
                $operations = EffetCommerce::getByAgence($userAgenceId, $type);
            }
        }
        
        if (!is_array($operations)) {
            $operations = array();
        }
        
        // Pagination
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
                $userAgenceNom = "Agence " . $userAgenceId;
            }
        }
        
        $data = array(
            'operations' => $operationsPagines,
            'totalOperations' => $totalOperations,
            'page' => $page,
            'totalPages' => $totalPages,
            'search' => $search,
            'type' => $type,
            'user' => $user,
            'userAgenceId' => $userAgenceId,
            'userAgenceNom' => $userAgenceNom,
            'isAdminOrCompta' => $isAdminOrCompta,
            'isCompta' => ($userPrivilege === 'Comptabilite'),
            'allowedToAdd' => $allowedToAdd
        );
        
        $this->render('EffetCommerce.index', $data);
    }
    
    /**
     * Récupère les données d'une opération (pour modification)
     */
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
            $operation = EffetCommerce::find($operation_id);
            
            if (!$operation) {
                echo json_encode(array('success' => false, 'message' => 'Opération non trouvée'));
                return;
            }
            
            $isAdminOrCompta = (stripos($userPrivilege, 'admin') !== false || stripos($userPrivilege, 'compta') !== false);
            
            if (!$isAdminOrCompta && $operation['agence_id'] != $userAgenceId) {
                echo json_encode(array('success' => false, 'message' => 'Accès non autorisé'));
                return;
            }
            
            $canBeModified = $this->canOperationBeModified($operation);
            $isCreator = $this->isOperationCreator($operation, $user);
            
            $this->cleanBuffer();
            
            echo json_encode(array(
                'success' => true,
                'operation' => $operation,
                'canBeModified' => $canBeModified,
                'isCreator' => $isCreator
            ));
            
        } catch (\Exception $e) {
            error_log("Erreur getOperationData: " . $e->getMessage());
            $this->cleanBuffer();
            echo json_encode(array('success' => false, 'message' => 'Erreur serveur'));
        }
    }
    
    /**
     * Ajoute une nouvelle opération
     */
    public function ajoutOperation() {
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
        $userAgenceId = isset($user['agence']) ? $user['agence'] : (isset($user['idAgence']) ? $user['idAgence'] : null);
        $userPrivilege = isset($user['privilege']) ? $user['privilege'] : '';
        
        if (!$userAgenceId) {
            echo json_encode(array('success' => false, 'message' => 'Vous n\'êtes pas rattaché à une agence'));
            return;
        }
        
        $allowedToAdd = in_array($userPrivilege, array('Agence', 'OPAgence'));
        if (!$allowedToAdd) {
            echo json_encode(array('success' => false, 'message' => 'Permission refusée'));
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(array('success' => false, 'message' => 'Méthode non autorisée'));
            return;
        }
        
        // Validation des champs obligatoires
        $errors = array();
        $requiredFields = array('type_operation', 'nom_tireur', 'nom_tire', 'date_emission', 'banque');
        
        foreach ($requiredFields as $field) {
            if (empty($_POST[$field])) {
                $errors[] = "Le champ '$field' est obligatoire";
            }
        }
        
        // Validation de l'échéance
        if (!isset($_POST['echeance_type'])) {
            $errors[] = "Le type d'échéance est obligatoire";
        } else {
            if ($_POST['echeance_type'] === 'date' && empty($_POST['echeance_date'])) {
                $errors[] = "La date d'échéance est obligatoire";
            }
            if ($_POST['echeance_type'] === 'jours' && empty($_POST['echeance_jours'])) {
                $errors[] = "Le nombre de jours est obligatoire";
            }
        }
        
        // Validation du scan
        if (!isset($_FILES['scan_operation']) || $_FILES['scan_operation']['error'] !== UPLOAD_ERR_OK) {
            $errors[] = "Le scan du document est obligatoire";
        }
        
        if (!empty($errors)) {
            echo json_encode(array('success' => false, 'message' => implode(', ', $errors)));
            return;
        }
        
        // Traitement de la date d'émission
        $date_emission = $_POST['date_emission'];
        if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $date_emission, $matches)) {
            $date_emission = $matches[3] . '-' . $matches[2] . '-' . $matches[1];
        }
        
        if (!strtotime($date_emission)) {
            echo json_encode(array('success' => false, 'message' => 'Date d\'émission invalide'));
            return;
        }
        
        // Préparation des données
        $data = array(
            'type_operation' => $_POST['type_operation'],
            'nom_tireur' => trim($_POST['nom_tireur']),
            'nom_tire' => trim($_POST['nom_tire']),
            'date_emission' => $date_emission,
            'banque' => trim($_POST['banque']),
            'agence_id' => $userAgenceId,
            'created_by' => isset($user['idUser']) ? $user['idUser'] : (isset($user['id']) ? $user['id'] : null),
            'observations' => isset($_POST['observations']) ? trim($_POST['observations']) : '',
            'echeance_type' => $_POST['echeance_type']
        );
        
        if ($_POST['echeance_type'] === 'date') {
            $data['echeance_date'] = $_POST['echeance_date'];
        } else {
            $data['echeance_jours'] = intval($_POST['echeance_jours']);
        }
        
        // Upload du scan
        $scanPath = $this->uploadScan($_FILES['scan_operation']);
        if (!$scanPath) {
            echo json_encode(array('success' => false, 'message' => 'Erreur lors de l\'upload du scan'));
            return;
        }
        
        try {
            $operationId = EffetCommerce::add($data, $scanPath);
            
            if (!$operationId) {
                throw new \Exception('Erreur lors de l\'insertion');
            }
            
            $this->cleanBuffer();
            
            echo json_encode(array(
                'success' => true,
                'message' => 'Opération ajoutée avec succès',
                'operation_id' => $operationId
            ));
            
        } catch (\Exception $e) {
            error_log("Erreur ajoutOperation: " . $e->getMessage());
            $this->cleanBuffer();
            echo json_encode(array('success' => false, 'message' => 'Erreur serveur'));
        }
    }
    
    /**
     * Modifie une opération
     */
    public function updateOperation() {
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
        $userAgenceId = isset($user['agence']) ? $user['agence'] : (isset($user['idAgence']) ? $user['idAgence'] : null);
        $userPrivilege = isset($user['privilege']) ? $user['privilege'] : '';
        
        $allowedToEdit = in_array($userPrivilege, array('Agence', 'OPAgence'));
        if (!$allowedToEdit) {
            echo json_encode(array('success' => false, 'message' => 'Permission refusée'));
            return;
        }
        
        if (!isset($_POST['operation_id'])) {
            echo json_encode(array('success' => false, 'message' => 'ID opération manquant'));
            return;
        }
        
        $operation_id = intval($_POST['operation_id']);
        $operation = EffetCommerce::find($operation_id);
        
        if (!$operation) {
            echo json_encode(array('success' => false, 'message' => 'Opération non trouvée'));
            return;
        }
        
        $isAdminOrCompta = (stripos($userPrivilege, 'admin') !== false || stripos($userPrivilege, 'compta') !== false);
        
        if (!$isAdminOrCompta && $operation['agence_id'] != $userAgenceId) {
            echo json_encode(array('success' => false, 'message' => 'Accès non autorisé'));
            return;
        }
        
        if (!$this->canOperationBeModified($operation)) {
            echo json_encode(array('success' => false, 'message' => 'Cette opération ne peut plus être modifiée'));
            return;
        }
        
        if (!$isAdminOrCompta && !$this->isOperationCreator($operation, $user)) {
            echo json_encode(array('success' => false, 'message' => 'Vous ne pouvez modifier que vos propres opérations'));
            return;
        }
        
        // Validation
        $errors = array();
        $requiredFields = array('nom_tireur', 'nom_tire', 'date_emission', 'banque');
        
        foreach ($requiredFields as $field) {
            if (empty($_POST[$field])) {
                $errors[] = "Le champ '$field' est obligatoire";
            }
        }
        
        if (!empty($errors)) {
            echo json_encode(array('success' => false, 'message' => implode(', ', $errors)));
            return;
        }
        
        // Date d'émission
        $date_emission = $_POST['date_emission'];
        if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $date_emission, $matches)) {
            $date_emission = $matches[3] . '-' . $matches[2] . '-' . $matches[1];
        }
        
        $data = array(
            'nom_tireur' => trim($_POST['nom_tireur']),
            'nom_tire' => trim($_POST['nom_tire']),
            'date_emission' => $date_emission,
            'banque' => trim($_POST['banque']),
            'observations' => isset($_POST['observations']) ? trim($_POST['observations']) : '',
            'updated_by' => isset($user['idUser']) ? $user['idUser'] : (isset($user['id']) ? $user['id'] : null)
        );
        
        // Échéance
        if (isset($_POST['echeance_type'])) {
            $data['echeance_type'] = $_POST['echeance_type'];
            if ($_POST['echeance_type'] === 'date' && !empty($_POST['echeance_date'])) {
                $data['echeance_date'] = $_POST['echeance_date'];
            } elseif ($_POST['echeance_type'] === 'jours' && !empty($_POST['echeance_jours'])) {
                $data['echeance_jours'] = intval($_POST['echeance_jours']);
            }
        }
        
        // Upload nouveau scan si fourni
        if (isset($_FILES['scan_operation']) && $_FILES['scan_operation']['error'] === UPLOAD_ERR_OK) {
            $scanPath = $this->uploadScan($_FILES['scan_operation']);
            if ($scanPath) {
                $data['scan_path'] = $scanPath;
                // Supprimer l'ancien fichier
                if (!empty($operation['scan_path']) && file_exists($operation['scan_path'])) {
                    @unlink($operation['scan_path']);
                }
            }
        }
        
        try {
            $result = EffetCommerce::update($operation_id, $data);
            
            $this->cleanBuffer();
            
            if ($result) {
                echo json_encode(array('success' => true, 'message' => 'Opération modifiée avec succès'));
            } else {
                echo json_encode(array('success' => false, 'message' => 'Erreur lors de la modification'));
            }
            
        } catch (\Exception $e) {
            error_log("Erreur updateOperation: " . $e->getMessage());
            $this->cleanBuffer();
            echo json_encode(array('success' => false, 'message' => 'Erreur serveur'));
        }
    }
    
    /**
     * Change le statut (Comptabilité uniquement) - AUCUN EMAIL ENVOYÉ
     */
    public function changerStatut() {
        $this->handleEtatChange('statut', array('confirmé', 'annulé'));
    }
    
    /**
     * Change l'état de confirmation (Comptabilité uniquement)
     */
    public function changerEtatConfirmation() {
        $this->handleEtatChange('confirmation', array('Oui', 'Non'));
    }
    
    /**
     * Change l'état de validation (Comptabilité uniquement)
     */
    public function changerEtatValidation() {
        $this->handleEtatChange('validation', array('Oui', 'Non'));
    }
    
    /**
     * Gestionnaire générique pour les changements d'état
     */
    private function handleEtatChange($type, $valeursValides) {
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
            echo json_encode(array('success' => false, 'message' => 'Accès réservé à la comptabilité'));
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(array('success' => false, 'message' => 'Méthode non autorisée'));
            return;
        }
        
        $operation_id = isset($_POST['operation_id']) ? intval($_POST['operation_id']) : 0;
        $nouvel_etat = isset($_POST['nouvel_etat']) ? $_POST['nouvel_etat'] : '';
        $observation = isset($_POST['observation']) ? trim($_POST['observation']) : '';
        
        if (!$operation_id || !$nouvel_etat) {
            echo json_encode(array('success' => false, 'message' => 'Données manquantes'));
            return;
        }
        
        if (!in_array($nouvel_etat, $valeursValides)) {
            echo json_encode(array('success' => false, 'message' => 'État invalide'));
            return;
        }
        
        if (empty($observation)) {
            echo json_encode(array('success' => false, 'message' => 'L\'observation est obligatoire'));
            return;
        }
        
        try {
            $userId = isset($user['idUser']) ? $user['idUser'] : (isset($user['id']) ? $user['id'] : null);
            $result = false;
            
            switch ($type) {
                case 'statut':
                    // Changement de statut : PAS D'ENVOI D'EMAIL
                    $result = EffetCommerce::updateStatut($operation_id, $nouvel_etat, $observation, $userId);
                    // SUPPRESSION DE L'ENVOI D'EMAIL POUR LE STATUT
                    break;
                    
                case 'confirmation':
                    $result = EffetCommerce::updateEtatConfirmation($operation_id, $nouvel_etat, $observation, $userId);
                    if ($result && $nouvel_etat === 'Oui') {
                        $this->sendNotification($operation_id, 'confirmation');
                    }
                    break;
                    
                case 'validation':
                    $result = EffetCommerce::updateEtatValidation($operation_id, $nouvel_etat, $observation, $userId);
                    if ($result && $nouvel_etat === 'Oui') {
                        $this->sendNotification($operation_id, 'validation');
                    }
                    break;
            }
            
            $this->cleanBuffer();
            
            if ($result) {
                echo json_encode(array('success' => true, 'message' => 'Mise à jour effectuée avec succès'));
            } else {
                echo json_encode(array('success' => false, 'message' => 'Erreur lors de la mise à jour'));
            }
            
        } catch (\Exception $e) {
            error_log("Erreur handleEtatChange: " . $e->getMessage());
            $this->cleanBuffer();
            echo json_encode(array('success' => false, 'message' => 'Erreur serveur'));
        }
    }
    
    /**
     * Récupère les détails d'une opération
     */
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
            $operation = EffetCommerce::find($operation_id);
            
            if (!$operation) {
                echo json_encode(array('success' => false, 'message' => 'Opération non trouvée'));
                return;
            }
            
            $isAdminOrCompta = (stripos($userPrivilege, 'admin') !== false || stripos($userPrivilege, 'compta') !== false);
            
            if (!$isAdminOrCompta && $operation['agence_id'] != $userAgenceId) {
                echo json_encode(array('success' => false, 'message' => 'Accès non autorisé'));
                return;
            }
            
            // Récupérer le nom de l'agence
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
            
            // Formatage des dates
            $date_emission = $this->formatDate($operation['date_emission']);
            $date_entree = $this->formatDateTime($operation['date_entree']);
            $echeance = $this->formatDate($operation['echeance']);
            
            // Type d'opération
            $type_label = ($operation['type_operation'] === 'billet_ordre') ? 'Billet à Ordre' : 'Lettre de Change';
            $type_icon = ($operation['type_operation'] === 'billet_ordre') ? 'fa-file-text' : 'fa-exchange';
            
            // Classes CSS pour les statuts
            $statutClass = 'label-warning';
            $statut = isset($operation['statut']) ? $operation['statut'] : 'en cours';
            if ($statut === 'confirmé') $statutClass = 'label-success';
            elseif ($statut === 'annulé') $statutClass = 'label-danger';
            
            $etatConf = isset($operation['etat_confirmation']) ? $operation['etat_confirmation'] : 'Non';
            $etatConfClass = ($etatConf === 'Oui') ? 'label-success' : 'label-danger';
            
            $etatValid = isset($operation['etat_validation']) ? $operation['etat_validation'] : 'Non';
            $etatValidClass = ($etatValid === 'Oui') ? 'label-success' : 'label-danger';
            
            // Construction du HTML
            $html = '
            <div class="row">
                <div class="col-md-6">
                    <h4>Informations générales</h4>
                    <table class="table table-bordered">
                        <tr>
                            <th style="width: 40%">Type:</th>
                            <td><i class="fa ' . $type_icon . '"></i> ' . $type_label . '</td>
                        </tr>
                        <tr>
                            <th>N° Effet:</th>
                            <td><strong>' . htmlspecialchars($operation['numero']) . '</strong></td>
                        </tr>
                        <tr>
                            <th>Tireur:</th>
                            <td>' . htmlspecialchars($operation['nom_tireur']) . '</td>
                        </tr>
                        <tr>
                            <th>Tiré:</th>
                            <td>' . htmlspecialchars($operation['nom_tire']) . '</td>
                        </tr>
                        <tr>
                            <th>Banque:</th>
                            <td>' . htmlspecialchars($operation['banque']) . '</td>
                        </tr>
                        <tr>
                            <th>Agence:</th>
                            <td>' . htmlspecialchars($agence_nom) . '</td>
                        </tr>
                    </table>
                </div>
                
                <div class="col-md-6">
                    <h4>Dates & Échéance</h4>
                    <table class="table table-bordered">
                        <tr>
                            <th style="width: 40%">Date d\'entrée:</th>
                            <td>' . $date_entree . '</td>
                        </tr>
                        <tr>
                            <th>Date d\'émission:</th>
                            <td>' . $date_emission . '</td>
                        </tr>
                        <tr>
                            <th>Échéance:</th>
                            <td>' . $echeance;
            if (!empty($operation['nb_jours'])) {
                $html .= ' <span class="label label-info">(' . $operation['nb_jours'] . ' jours)</span>';
            }
            $html .= '</td></tr>
                    </table>
                    
                    <h4>Validation</h4>
                    <table class="table table-bordered">
                        <tr>
                            <th style="width: 40%">Statut:</th>
                            <td><span class="label ' . $statutClass . '">' . ucfirst($statut) . '</span></td>
                        </tr>
                        <tr>
                            <th>État confirmation:</th>
                            <td><span class="label ' . $etatConfClass . '">' . $etatConf . '</span></td>
                        </tr>
                        <tr>
                            <th>État validation:</th>
                            <td><span class="label ' . $etatValidClass . '">' . $etatValid . '</span></td>
                        </tr>
                    </table>
                </div>
            </div>';
            
            if (!empty($operation['observations'])) {
                $html .= '
                <div class="row">
                    <div class="col-md-12">
                        <h4>Observations</h4>
                        <div class="well well-sm">' . nl2br(htmlspecialchars($operation['observations'])) . '</div>
                    </div>
                </div>';
            }
            
            if (!empty($operation['scan_path']) && file_exists($operation['scan_path'])) {
                $html .= '
                <div class="row">
                    <div class="col-md-12">
                        <h4>Document scanné</h4>
                        <div class="text-center">
                            <a href="' . htmlspecialchars($operation['scan_path']) . '" target="_blank" class="btn btn-primary">
                                <i class="fa fa-eye"></i> Voir le document
                            </a>
                        </div>
                    </div>
                </div>';
            }
            
            $this->cleanBuffer();
            
            echo json_encode(array('success' => true, 'html' => $html));
            
        } catch (\Exception $e) {
            error_log("Erreur getDetails: " . $e->getMessage());
            $this->cleanBuffer();
            echo json_encode(array('success' => false, 'message' => 'Erreur serveur'));
        }
    }
    
    /**
     * Génère une carte de suivi PNG
     */
    public function genererCartePNG() {
        $this->cleanBuffer();
        
        error_reporting(0);
        ini_set('display_errors', 0);
        
        header('Content-Type: image/png');
        
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['user']) || empty($_SESSION['user'])) {
            $this->genererImageErreur('Utilisateur non connecté');
            return;
        }
        
        $user = $_SESSION['user'];
        
        if (!isset($_POST['operation_id'])) {
            $this->genererImageErreur('ID opération manquant');
            return;
        }
        
        $operation_id = intval($_POST['operation_id']);
        $titre_carte = isset($_POST['titre_carte']) ? trim($_POST['titre_carte']) : 'CARTE DE SUIVI';
        
        try {
            $operation = EffetCommerce::find($operation_id);
            
            if (!$operation) {
                $this->genererImageErreur('Opération non trouvée');
                return;
            }
            
            if (!function_exists('imagecreatetruecolor')) {
                $this->genererImageErreur('Bibliothèque GD non disponible');
                return;
            }
            
            // Récupérer l'agence
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
            
            // Formatage
            $type_label = ($operation['type_operation'] === 'billet_ordre') ? 'BILLET À ORDRE' : 'LETTRE DE CHANGE';
            $statut = isset($operation['statut']) ? strtoupper($operation['statut']) : 'EN COURS';
            $date_emission = $this->formatDate($operation['date_emission']);
            $date_entree = $this->formatDateTime($operation['date_entree']);
            $echeance = $this->formatDate($operation['echeance']);
            
            // Dimensions
            $width = 750;
            $height = 500;
            
            $image = imagecreatetruecolor($width, $height);
            if (!$image) {
                $this->genererImageErreur('Impossible de créer l\'image');
                return;
            }
            
            // Couleurs
            $white = imagecolorallocate($image, 255, 255, 255);
            $black = imagecolorallocate($image, 0, 0, 0);
            $darkBlue = imagecolorallocate($image, 0, 51, 102);
            $lightGray = imagecolorallocate($image, 240, 240, 240);
            $green = imagecolorallocate($image, 40, 167, 69);
            $red = imagecolorallocate($image, 220, 53, 69);
            $orange = imagecolorallocate($image, 255, 193, 7);
            
            imagefill($image, 0, 0, $white);
            imagerectangle($image, 5, 5, $width - 5, $height - 5, $darkBlue);
            
            // En-tête
            imagefilledrectangle($image, 10, 10, $width - 10, 60, $darkBlue);
            $this->drawText($image, 18, $width/2, 35, $white, $titre_carte, 'center', true);
            $this->drawText($image, 11, $width/2, 52, $white, 'Effet #' . $operation['numero'], 'center', false);
            
            // Informations
            $y = 80;
            $leftX = 30;
            $rightX = 400;
            $labelWidth = 100;
            $lineHeight = 25;
            
            // Type
            $this->drawText($image, 11, $leftX, $y, $black, 'Type:', 'left', true);
            $this->drawText($image, 11, $leftX + $labelWidth, $y, $darkBlue, $type_label, 'left', false);
            
            $this->drawText($image, 11, $rightX, $y, $black, 'Agence:', 'left', true);
            $this->drawText($image, 11, $rightX + $labelWidth, $y, $darkBlue, $this->truncateText($agence_nom, 25), 'left', false);
            $y += $lineHeight;
            
            // Tireur
            $this->drawText($image, 11, $leftX, $y, $black, 'Tireur:', 'left', true);
            $this->drawText($image, 11, $leftX + $labelWidth, $y, $darkBlue, $this->truncateText($operation['nom_tireur'], 25), 'left', false);
            
            $this->drawText($image, 11, $rightX, $y, $black, 'Tiré:', 'left', true);
            $this->drawText($image, 11, $rightX + $labelWidth, $y, $darkBlue, $this->truncateText($operation['nom_tire'], 25), 'left', false);
            $y += $lineHeight;
            
            // Banque
            $this->drawText($image, 11, $leftX, $y, $black, 'Banque:', 'left', true);
            $this->drawText($image, 11, $leftX + $labelWidth, $y, $darkBlue, $operation['banque'], 'left', false);
            $y += $lineHeight;
            
            // Dates
            $this->drawText($image, 11, $leftX, $y, $black, 'Émission:', 'left', true);
            $this->drawText($image, 11, $leftX + $labelWidth, $y, $darkBlue, $date_emission, 'left', false);
            
            $this->drawText($image, 11, $rightX, $y, $black, 'Entrée:', 'left', true);
            $this->drawText($image, 11, $rightX + $labelWidth, $y, $darkBlue, $date_entree, 'left', false);
            $y += $lineHeight;
            
            // Échéance
            $this->drawText($image, 11, $leftX, $y, $black, 'Échéance:', 'left', true);
            $echeance_text = $echeance;
            if (!empty($operation['nb_jours'])) {
                $echeance_text .= ' (' . $operation['nb_jours'] . ' j)';
            }
            $this->drawText($image, 11, $leftX + $labelWidth, $y, $darkBlue, $echeance_text, 'left', false);
            $y += $lineHeight + 10;
            
            // Ligne séparatrice
            imageline($image, 20, $y - 5, $width - 20, $y - 5, $lightGray);
            
            // Section validation
            $this->drawText($image, 12, $leftX, $y, $darkBlue, 'VALIDATION', 'left', true);
            $y += 20;
            
            // Statut
            $statut_color = $statut == 'CONFIRMÉ' ? $green : ($statut == 'ANNULÉ' ? $red : $orange);
            $this->drawText($image, 11, $leftX, $y, $black, 'Statut:', 'left', true);
            imagefilledrectangle($image, $leftX + $labelWidth, $y - 15, $leftX + $labelWidth + 80, $y - 2, $statut_color);
            $this->drawText($image, 10, $leftX + $labelWidth + 40, $y - 5, $white, $statut, 'center', true);
            $y += $lineHeight;
            
            // États
            $etatConf = isset($operation['etat_confirmation']) ? $operation['etat_confirmation'] : 'Non';
            $etatValid = isset($operation['etat_validation']) ? $operation['etat_validation'] : 'Non';
            
            $conf_color = ($etatConf == 'Oui') ? $green : $red;
            $valid_color = ($etatValid == 'Oui') ? $green : $red;
            
            $this->drawText($image, 11, $leftX, $y, $black, 'Confirmation:', 'left', true);
            imagefilledrectangle($image, $leftX + $labelWidth, $y - 15, $leftX + $labelWidth + 50, $y - 2, $conf_color);
            $this->drawText($image, 10, $leftX + $labelWidth + 25, $y - 5, $white, $etatConf, 'center', true);
            
            $this->drawText($image, 11, $rightX, $y, $black, 'Validation:', 'left', true);
            imagefilledrectangle($image, $rightX + $labelWidth - 20, $y - 15, $rightX + $labelWidth + 30, $y - 2, $valid_color);
            $this->drawText($image, 10, $rightX + $labelWidth + 5, $y - 5, $white, $etatValid, 'center', true);
            $y += $lineHeight + 10;
            
            // Observations
            if (!empty($operation['observations'])) {
                $this->drawText($image, 11, $leftX, $y, $darkBlue, 'OBSERVATIONS:', 'left', true);
                $y += 18;
                $obs_lines = $this->wrapText($operation['observations'], 80);
                foreach ($obs_lines as $line) {
                    $this->drawText($image, 10, $leftX, $y, $black, $line, 'left', false);
                    $y += 18;
                }
            }
            
            // Pied de page
            $footer_y = $height - 30;
            imageline($image, 20, $footer_y - 5, $width - 20, $footer_y - 5, $lightGray);
            $this->drawText($image, 8, 30, $footer_y, $darkBlue, 'Généré le ' . date('d/m/Y H:i:s'), 'left', false);
            $this->drawText($image, 8, $width - 30, $footer_y, $darkBlue, 'Effets de Commerce', 'right', false);
            
            imagepng($image);
            imagedestroy($image);
            
        } catch (\Exception $e) {
            error_log("Erreur genererCartePNG: " . $e->getMessage());
            $this->genererImageErreur('Erreur lors de la génération');
        }
    }
    
    /**
     * Envoie une notification par email
     */
    private function sendNotification($operationId, $type) {
        $logFile = dirname(dirname(__DIR__)) . '/email_debug.log';
        file_put_contents($logFile, date('Y-m-d H:i:s') . " - sendNotification pour operation $operationId, type: $type\n", FILE_APPEND);
        
        $operation = EffetCommerce::find($operationId);
        if (!$operation) {
            file_put_contents($logFile, date('Y-m-d H:i:s') . " - Opération non trouvée\n", FILE_APPEND);
            return false;
        }
        
        $agenceId = isset($operation['agence_id']) ? $operation['agence_id'] : 0;
        
        file_put_contents($logFile, date('Y-m-d H:i:s') . " - Recherche email pour agence ID: $agenceId\n", FILE_APPEND);
        $emailDestinataire = $this->getEmailChefAgence($agenceId);
        file_put_contents($logFile, date('Y-m-d H:i:s') . " - Email trouvé: " . ($emailDestinataire ?: 'NON TROUVÉ') . "\n", FILE_APPEND);
        
        if (!$emailDestinataire) {
            file_put_contents($logFile, date('Y-m-d H:i:s') . " - ⚠️ Aucun email trouvé pour l'agence $agenceId\n", FILE_APPEND);
            return false;
        }
        
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
            
            $type_effet = ($operation['type_operation'] === 'billet_ordre') ? 'Billet à Ordre' : 'Lettre de Change';
            
            $mail->Subject = "Confirmation d'effet de commerce n° " . $operation['numero'];
            
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
                        
                        <p>Nous vous informons que l'effet de commerce suivant a été <strong style='color: #28a745;'>confirmé</strong> par le service comptabilité :</p>
                        
                        <div class='info'>
                            <p><strong>N° Effet :</strong> " . htmlspecialchars($operation['numero']) . "</p>
                            <p><strong>Type :</strong> " . $type_effet . "</p>
                            <p><strong>Tireur :</strong> " . htmlspecialchars($operation['nom_tireur']) . "</p>
                            <p><strong>Tiré :</strong> " . htmlspecialchars($operation['nom_tire']) . "</p>
                            <p><strong>Échéance :</strong> " . $this->formatDate($operation['echeance']) . "</p>
                            <p><strong>Banque :</strong> " . htmlspecialchars($operation['banque']) . "</p>
                        </div>
                        
                        <p>Vous pouvez donc considérer cet effet comme <strong>effectif</strong>.</p>
                        
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
            
            $type_effet = ($operation['type_operation'] === 'billet_ordre') ? 'Billet à Ordre' : 'Lettre de Change';
            
            $mail->Subject = "Validation d'effet de commerce n° " . $operation['numero'];
            
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
                        
                        <p>Nous vous informons que l'effet de commerce suivant a été <strong style='color: #28a745;'>validé</strong> par le service comptabilité :</p>
                        
                        <div class='info'>
                            <p><strong>N° Effet :</strong> " . htmlspecialchars($operation['numero']) . "</p>
                            <p><strong>Type :</strong> " . $type_effet . "</p>
                            <p><strong>Tireur :</strong> " . htmlspecialchars($operation['nom_tireur']) . "</p>
                            <p><strong>Tiré :</strong> " . htmlspecialchars($operation['nom_tire']) . "</p>
                            <p><strong>Échéance :</strong> " . $this->formatDate($operation['echeance']) . "</p>
                            <p><strong>Banque :</strong> " . htmlspecialchars($operation['banque']) . "</p>
                        </div>
                        
                        <p>Vous pouvez donc considérer cet effet comme <strong>effectif</strong>.</p>
                        
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
    
    /**
     * Récupère l'email du chef d'agence
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
        
        return null;
    }
    
    /**
     * Vérifie si une opération peut être modifiée
     */
    private function canOperationBeModified($operation) {
        $statut = isset($operation['statut']) ? strtolower($operation['statut']) : 'en cours';
        $etatConfirmation = isset($operation['etat_confirmation']) ? $operation['etat_confirmation'] : 'Non';
        $etatValidation = isset($operation['etat_validation']) ? $operation['etat_validation'] : 'Non';
        
        return ($statut === 'en cours' && $etatConfirmation === 'Non' && $etatValidation === 'Non');
    }
    
    /**
     * Vérifie si l'utilisateur est le créateur de l'opération
     */
    private function isOperationCreator($operation, $user) {
        $userId = isset($user['idUser']) ? $user['idUser'] : (isset($user['id']) ? $user['id'] : null);
        $operationCreatedBy = isset($operation['created_by']) ? $operation['created_by'] : null;
        
        if (empty($operationCreatedBy)) return true;
        return ($operationCreatedBy == $userId);
    }
    
    /**
     * Upload d'un scan
     */
    private function uploadScan($file) {
        $targetDir = "DocumentsEffets/";
        if (!is_dir($targetDir)) {
            if (!mkdir($targetDir, 0777, true)) {
                return false;
            }
        }
        
        $allowedTypes = array('image/jpeg', 'image/png', 'application/pdf');
        if (!in_array($file['type'], $allowedTypes)) {
            return false;
        }
        
        if ($file['size'] > 5 * 1024 * 1024) {
            return false;
        }
        
        $fileName = 'effet_' . time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $file['name']);
        $targetPath = $targetDir . $fileName;
        
        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            return $targetPath;
        }
        
        return false;
    }
    
    /**
     * Formatage de date
     */
    private function formatDate($date) {
        if (empty($date)) return 'Non définie';
        if (is_object($date) && method_exists($date, 'format')) {
            return $date->format('d/m/Y');
        }
        if (is_string($date)) {
            $timestamp = strtotime($date);
            return ($timestamp && $timestamp > 0) ? date('d/m/Y', $timestamp) : 'Date invalide';
        }
        return 'Non définie';
    }
    
    /**
     * Formatage date + heure
     */
    private function formatDateTime($date) {
        if (empty($date)) return 'Non définie';
        if (is_object($date) && method_exists($date, 'format')) {
            return $date->format('d/m/Y H:i');
        }
        if (is_string($date)) {
            $timestamp = strtotime($date);
            return ($timestamp && $timestamp > 0) ? date('d/m/Y H:i', $timestamp) : 'Date invalide';
        }
        return 'Non définie';
    }
    
    /**
     * Dessine du texte sur l'image
     */
    private function drawText($image, $size, $x, $y, $color, $text, $align = 'left', $bold = false) {
        $font = $bold ? $this->getFontBoldPath() : $this->getFontPath();
        
        if ($align != 'left' && $font && file_exists($font)) {
            $bbox = imagettfbbox($size, 0, $font, $text);
            $text_width = $bbox[2] - $bbox[0];
            if ($align == 'center') $x -= ($text_width / 2);
            elseif ($align == 'right') $x -= $text_width;
        }
        
        if ($font && file_exists($font)) {
            imagettftext($image, $size, 0, $x, $y, $color, $font, $text);
        } else {
            $gd_size = 5;
            if ($size <= 8) $gd_size = 1;
            elseif ($size <= 10) $gd_size = 2;
            elseif ($size <= 12) $gd_size = 3;
            elseif ($size <= 16) $gd_size = 4;
            imagestring($image, $gd_size, (int)$x, (int)($y - 15), utf8_decode($text), $color);
        }
    }
    
    /**
     * Chemin police normale
     */
    private function getFontPath() {
        if (file_exists('/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf'))
            return '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf';
        if (file_exists('C:/Windows/Fonts/arial.ttf'))
            return 'C:/Windows/Fonts/arial.ttf';
        return null;
    }
    
    /**
     * Chemin police gras
     */
    private function getFontBoldPath() {
        if (file_exists('/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf'))
            return '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf';
        if (file_exists('C:/Windows/Fonts/arialbd.ttf'))
            return 'C:/Windows/Fonts/arialbd.ttf';
        return $this->getFontPath();
    }
    
    /**
     * Tronque un texte
     */
    private function truncateText($text, $length) {
        if (strlen($text) <= $length) return $text;
        return substr($text, 0, $length) . '...';
    }
    
    /**
     * Retourne à la ligne
     */
    private function wrapText($text, $maxChars) {
        $words = explode(' ', $text);
        $lines = array();
        $currentLine = '';
        foreach ($words as $word) {
            if (strlen($currentLine . ' ' . $word) <= $maxChars) {
                $currentLine .= ($currentLine === '' ? '' : ' ') . $word;
            } else {
                if ($currentLine !== '') $lines[] = $currentLine;
                $currentLine = $word;
            }
        }
        if ($currentLine !== '') $lines[] = $currentLine;
        return $lines;
    }
    
    /**
     * Génère une image d'erreur
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
        
        imagepng($image);
        imagedestroy($image);
        exit;
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