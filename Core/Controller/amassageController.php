<?php
namespace Core\Controller;

use Core\Model\App;
use Core\Model\Session;
use Core\Database\Ramassage;
use Core\Database\Agence;
use Core\Model\AppController;

class RamassageController extends AppController {
    
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
        $totalRamassages = Ramassage::count();
        $ramassages = Ramassage::getAll();
        
        // Récupérer la liste des agences pour les dropdowns
        $agences = Agence::getAll();
        
        $data = [
            'ramassages' => $ramassages,
            'user' => $user,
            'totalRamassages' => $totalRamassages,
            'agences' => $agences
        ];
        
        $this->render('Ramassage.index', $data);
    }
    
    public function ajoutRamassage() {
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
        
        $allowedToAdd = in_array($user['privilege'], ['Administration', 'SuperAdministration']);
        if (!$allowedToAdd) {
            echo json_encode([
                'success' => false,
                'message' => 'Seuls les administrateurs peuvent ajouter des plans de ramassage'
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
        $requiredFields = ['entite_id', 'agence_id', 'periode', 'date_debut', 'date_fin'];
        
        foreach ($requiredFields as $field) {
            if (empty($_POST[$field])) {
                $errors[] = "Le champ '$field' est obligatoire";
            }
        }
        
        // VÉRIFICATION OBLIGATOIRE DU FICHIER PDF
        if (!isset($_FILES['liste_ramasseurs']) || $_FILES['liste_ramasseurs']['error'] !== UPLOAD_ERR_OK) {
            $errors[] = "La liste des ramasseurs (PDF) est obligatoire";
        }
        
        if (!empty($errors)) {
            echo json_encode([
                'success' => false,
                'message' => 'Erreurs de validation: ' . implode(', ', $errors)
            ]);
            return;
        }
        
        $date_debut = $_POST['date_debut'];
        $date_fin = $_POST['date_fin'];
        
        // Conversion des dates
        if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $date_debut, $matches)) {
            $date_debut = $matches[3] . '-' . $matches[2] . '-' . $matches[1];
        }
        
        if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $date_fin, $matches)) {
            $date_fin = $matches[3] . '-' . $matches[2] . '-' . $matches[1];
        }
        
        if (!strtotime($date_debut) || !strtotime($date_fin)) {
            echo json_encode([
                'success' => false,
                'message' => 'Dates invalides'
            ]);
            return;
        }
        
        // Vérifier que date début < date fin
        if (strtotime($date_debut) >= strtotime($date_fin)) {
            echo json_encode([
                'success' => false,
                'message' => 'La date de début doit être antérieure à la date de fin'
            ]);
            return;
        }
        
        // Vérifier que la période ne dépasse pas 1 mois (31 jours)
        $diff = strtotime($date_fin) - strtotime($date_debut);
        $jours = floor($diff / (60 * 60 * 24));
        
        if ($jours > 31) {
            echo json_encode([
                'success' => false,
                'message' => 'La période ne doit pas dépasser 1 mois (31 jours)'
            ]);
            return;
        }
        
        $data = [
            'entite_id' => intval($_POST['entite_id']),
            'agence_id' => intval($_POST['agence_id']),
            'periode' => trim($_POST['periode']),
            'date_debut' => $date_debut,
            'date_fin' => $date_fin,
            'date_creation' => date('Y-m-d H:i:s'),
            'observations' => isset($_POST['observations']) ? trim($_POST['observations']) : '',
            'created_by' => isset($user['idUser']) ? $user['idUser'] : (isset($user['id']) ? $user['id'] : null)
        ];
        
        // TRAITEMENT OBLIGATOIRE DU FICHIER PDF
        $listePath = null;
        if (isset($_FILES['liste_ramasseurs']) && $_FILES['liste_ramasseurs']['error'] === UPLOAD_ERR_OK) {
            $targetDir = "DocumentsRamassage/";
            if (!is_dir($targetDir)) {
                if (!mkdir($targetDir, 0777, true)) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Impossible de créer le dossier de destination'
                    ]);
                    return;
                }
            }
            
            // Vérifier que c'est bien un PDF
            $fileType = $_FILES['liste_ramasseurs']['type'];
            $fileExtension = strtolower(pathinfo($_FILES['liste_ramasseurs']['name'], PATHINFO_EXTENSION));
            
            if ($fileType !== 'application/pdf' || $fileExtension !== 'pdf') {
                echo json_encode([
                    'success' => false,
                    'message' => 'Type de fichier non autorisé. Seuls les fichiers PDF sont acceptés'
                ]);
                return;
            }
            
            $maxFileSize = 10 * 1024 * 1024; // 10MB
            if ($_FILES['liste_ramasseurs']['size'] > $maxFileSize) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Le fichier est trop volumineux (max 10MB)'
                ]);
                return;
            }
            
            $fileName = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $_FILES['liste_ramasseurs']['name']);
            $targetPath = $targetDir . $fileName;
            
            if (move_uploaded_file($_FILES['liste_ramasseurs']['tmp_name'], $targetPath)) {
                $listePath = $targetPath;
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Erreur lors du téléchargement de la liste'
                ]);
                return;
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'La liste des ramasseurs (PDF) est obligatoire'
            ]);
            return;
        }
        
        try {
            $ramassageId = Ramassage::create($data, $listePath);
            
            if (!$ramassageId) {
                throw new \Exception('Erreur lors de l\'insertion dans la base de données');
            }
            
            $ramassageInserted = Ramassage::find($ramassageId);
            
            if (!$ramassageInserted) {
                $ramassageInserted = [
                    'id' => $ramassageId,
                    'entite_id' => $data['entite_id'],
                    'agence_id' => $data['agence_id'],
                    'periode' => $data['periode'],
                    'date_debut' => $data['date_debut'],
                    'date_fin' => $data['date_fin'],
                    'observations' => $data['observations'],
                    'liste_path' => $listePath
                ];
            }
            
            // Récupérer les noms des agences
            try {
                // Entité (agence qui ramasse)
                $stmtEntite = Agence::searchById($data['entite_id']);
                if ($stmtEntite) {
                    while ($agence = sqlsrv_fetch_array($stmtEntite, SQLSRV_FETCH_ASSOC)) {
                        if (isset($agence['designation'])) {
                            $ramassageInserted['entite_nom'] = $agence['designation'];
                            break;
                        }
                    }
                }
                
                // Agence à ramasser
                $stmtAgence = Agence::searchById($data['agence_id']);
                if ($stmtAgence) {
                    while ($agence = sqlsrv_fetch_array($stmtAgence, SQLSRV_FETCH_ASSOC)) {
                        if (isset($agence['designation'])) {
                            $ramassageInserted['agence_nom'] = $agence['designation'];
                            break;
                        }
                    }
                }
            } catch (\Exception $e) {
                error_log("Erreur récupération noms agences: " . $e->getMessage());
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'Plan de ramassage ajouté avec succès',
                'ramassage' => $ramassageInserted,
                'refreshNeeded' => false
            ]);
            
        } catch (\Exception $e) {
            error_log("Erreur ajoutRamassage: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Erreur serveur: ' . $e->getMessage()
            ]);
        }
    }
    
    public function getRamassageData() {
        error_reporting(0);
        ini_set('display_errors', 0);
        
        header('Content-Type: application/json');
        
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_POST['ramassage_id'])) {
            echo json_encode(['success' => false, 'message' => 'ID ramassage manquant']);
            return;
        }
        
        $ramassage_id = intval($_POST['ramassage_id']);
        $user = $_SESSION['user'];
        
        try {
            $ramassage = Ramassage::find($ramassage_id);
            
            if (!$ramassage) {
                echo json_encode(['success' => false, 'message' => 'Ramassage non trouvé']);
                return;
            }
            
            // Vérifier si l'utilisateur peut modifier
            $canModify = true; // Tous les utilisateurs autorisés peuvent modifier (avec restrictions dans la vue)
            
            // Vérifier si c'est un admin
            $isAdmin = in_array($user['privilege'], ['Administration', 'SuperAdministration']);
            
            echo json_encode([
                'success' => true,
                'ramassage' => $ramassage,
                'canModify' => $canModify,
                'isAdmin' => $isAdmin
            ]);
            
        } catch (\Exception $e) {
            error_log("Erreur getRamassageData: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Erreur serveur: ' . $e->getMessage()
            ]);
        }
    }
    
    public function updateRamassage() {
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
        
        // Vérifier si l'utilisateur a accès (tous les rôles autorisés dans la navigation)
        $allowedRoles = ['Administration', 'SuperAdministration', 'Agence', 'AgenceSage', 'Comptabilite', 'ControleInterne', 'Controleur'];
        if (!in_array($user['privilege'], $allowedRoles)) {
            echo json_encode([
                'success' => false,
                'message' => 'Vous n\'avez pas la permission de modifier des plans de ramassage'
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
        
        if (!isset($_POST['ramassage_id'])) {
            echo json_encode([
                'success' => false,
                'message' => 'ID ramassage manquant'
            ]);
            return;
        }
        
        $ramassage_id = intval($_POST['ramassage_id']);
        
        // Récupérer le ramassage pour vérifier s'il existe
        $ramassage = Ramassage::find($ramassage_id);
        if (!$ramassage) {
            echo json_encode([
                'success' => false,
                'message' => 'Ramassage non trouvé'
            ]);
            return;
        }
        
        // Vérifier si c'est un admin
        $isAdmin = in_array($user['privilege'], ['Administration', 'SuperAdministration']);
        
        $errors = [];
        
        if ($isAdmin) {
            // Admin peut modifier tous les champs
            $requiredFields = ['entite_id', 'agence_id', 'periode', 'date_debut', 'date_fin'];
            
            foreach ($requiredFields as $field) {
                if (empty($_POST[$field])) {
                    $errors[] = "Le champ '$field' est obligatoire";
                }
            }
            
            $date_debut = $_POST['date_debut'];
            $date_fin = $_POST['date_fin'];
            
            // Conversion des dates pour admin
            if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $date_debut, $matches)) {
                $date_debut = $matches[3] . '-' . $matches[2] . '-' . $matches[1];
            }
            
            if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $date_fin, $matches)) {
                $date_fin = $matches[3] . '-' . $matches[2] . '-' . $matches[1];
            }
            
            if (!strtotime($date_debut) || !strtotime($date_fin)) {
                $errors[] = 'Dates invalides';
            } elseif (strtotime($date_debut) >= strtotime($date_fin)) {
                $errors[] = 'La date de début doit être antérieure à la date de fin';
            } elseif (floor((strtotime($date_fin) - strtotime($date_debut)) / (60 * 60 * 24)) > 31) {
                $errors[] = 'La période ne doit pas dépasser 1 mois (31 jours)';
            }
        }
        
        // Observations toujours obligatoires pour la modification
        if (empty($_POST['observations'])) {
            $errors[] = "Les observations sont obligatoires pour la modification";
        }
        
        if (!empty($errors)) {
            echo json_encode([
                'success' => false,
                'message' => 'Erreurs de validation: ' . implode(', ', $errors)
            ]);
            return;
        }
        
        // Préparer les données pour la mise à jour
        $data = [
            'updated_at' => date('Y-m-d H:i:s'),
            'updated_by' => isset($user['idUser']) ? $user['idUser'] : (isset($user['id']) ? $user['id'] : null),
            'observations' => trim($_POST['observations'])
        ];
        
        if ($isAdmin) {
            // Admin peut modifier tous les champs
            $data['entite_id'] = intval($_POST['entite_id']);
            $data['agence_id'] = intval($_POST['agence_id']);
            $data['periode'] = trim($_POST['periode']);
            $data['date_debut'] = $date_debut;
            $data['date_fin'] = $date_fin;
        } else {
            // Non-admin: garder les valeurs originales
            $data['entite_id'] = $ramassage['entite_id'];
            $data['agence_id'] = $ramassage['agence_id'];
            $data['periode'] = $ramassage['periode'];
            $data['date_debut'] = $ramassage['date_debut'];
            $data['date_fin'] = $ramassage['date_fin'];
        }
        
        // TRAITEMENT DU FICHIER PDF (facultatif pour la modification)
        $listePath = null;
        if (isset($_FILES['liste_ramasseurs']) && $_FILES['liste_ramasseurs']['error'] === UPLOAD_ERR_OK) {
            $targetDir = "DocumentsRamassage/";
            if (!is_dir($targetDir)) {
                if (!mkdir($targetDir, 0777, true)) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Impossible de créer le dossier de destination'
                    ]);
                    return;
                }
            }
            
            // Vérifier que c'est bien un PDF
            $fileType = $_FILES['liste_ramasseurs']['type'];
            $fileExtension = strtolower(pathinfo($_FILES['liste_ramasseurs']['name'], PATHINFO_EXTENSION));
            
            if ($fileType !== 'application/pdf' || $fileExtension !== 'pdf') {
                echo json_encode([
                    'success' => false,
                    'message' => 'Type de fichier non autorisé. Seuls les fichiers PDF sont acceptés'
                ]);
                return;
            }
            
            $maxFileSize = 10 * 1024 * 1024; // 10MB
            if ($_FILES['liste_ramasseurs']['size'] > $maxFileSize) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Le fichier est trop volumineux (max 10MB)'
                ]);
                return;
            }
            
            $fileName = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $_FILES['liste_ramasseurs']['name']);
            $targetPath = $targetDir . $fileName;
            
            if (move_uploaded_file($_FILES['liste_ramasseurs']['tmp_name'], $targetPath)) {
                $listePath = $targetPath;
                $data['liste_path'] = $listePath;
                
                // Supprimer l'ancienne liste si elle existe
                if (!empty($ramassage['liste_path']) && file_exists($ramassage['liste_path'])) {
                    @unlink($ramassage['liste_path']);
                }
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Erreur lors du téléchargement de la nouvelle liste'
                ]);
                return;
            }
        }
        
        try {
            $result = Ramassage::update($ramassage_id, $data);
            
            if ($result) {
                $ramassageUpdated = Ramassage::find($ramassage_id);
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Plan de ramassage modifié avec succès',
                    'ramassage' => $ramassageUpdated
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Erreur lors de la modification du plan de ramassage'
                ]);
            }
            
        } catch (\Exception $e) {
            error_log("Erreur updateRamassage: " . $e->getMessage());
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
        
        if (!isset($_POST['ramassage_id'])) {
            echo json_encode(['success' => false, 'message' => 'ID ramassage manquant']);
            return;
        }
        
        $ramassage_id = intval($_POST['ramassage_id']);
        
        try {
            $ramassage = Ramassage::find($ramassage_id);
            
            if (!$ramassage) {
                echo json_encode(['success' => false, 'message' => 'Ramassage non trouvé']);
                return;
            }
            
            // Récupérer les noms des agences
            $entite_nom = '';
            $agence_nom = '';
            
            if (isset($ramassage['entite_id']) && $ramassage['entite_id']) {
                $stmt = Agence::searchById($ramassage['entite_id']);
                if ($stmt) {
                    while ($agence = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                        if (isset($agence['designation'])) {
                            $entite_nom = $agence['designation'];
                            break;
                        }
                    }
                }
            }
            
            if (isset($ramassage['agence_id']) && $ramassage['agence_id']) {
                $stmt = Agence::searchById($ramassage['agence_id']);
                if ($stmt) {
                    while ($agence = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                        if (isset($agence['designation'])) {
                            $agence_nom = $agence['designation'];
                            break;
                        }
                    }
                }
            }
            
            // Formater les dates
            $date_creation = '';
            if (isset($ramassage['date_creation'])) {
                if ($ramassage['date_creation'] instanceof \DateTime) {
                    $date_creation = $ramassage['date_creation']->format('d/m/Y H:i');
                } else {
                    $date_creation = date('d/m/Y H:i', strtotime($ramassage['date_creation']));
                }
            }
            
            $date_debut = '';
            if (isset($ramassage['date_debut'])) {
                if ($ramassage['date_debut'] instanceof \DateTime) {
                    $date_debut = $ramassage['date_debut']->format('d/m/Y');
                } else {
                    $date_debut = date('d/m/Y', strtotime($ramassage['date_debut']));
                }
            }
            
            $date_fin = '';
            if (isset($ramassage['date_fin'])) {
                if ($ramassage['date_fin'] instanceof \DateTime) {
                    $date_fin = $ramassage['date_fin']->format('d/m/Y');
                } else {
                    $date_fin = date('d/m/Y', strtotime($ramassage['date_fin']));
                }
            }
            
            // Déterminer l'état de validité
            $etatValidite = 'success';
            $etatText = 'Valide';
            $etatIcon = 'fa-check-circle';
            
            if (isset($ramassage['date_fin'])) {
                $dateFin = is_string($ramassage['date_fin']) ? strtotime($ramassage['date_fin']) : $ramassage['date_fin'];
                $aujourdhui = time();
                
                if ($dateFin < $aujourdhui) {
                    $etatValidite = 'danger';
                    $etatText = 'Expiré';
                    $etatIcon = 'fa-times-circle';
                } else {
                    $joursRestants = floor(($dateFin - $aujourdhui) / (60 * 60 * 24));
                    
                    if ($joursRestants <= 7) {
                        $etatValidite = 'warning';
                        $etatText = 'Expire bientôt (' . $joursRestants . ' jours restants)';
                        $etatIcon = 'fa-exclamation-triangle';
                    }
                }
            }
            
            // Récupérer le nom du créateur si disponible
            $created_by_name = isset($ramassage['created_by_name']) ? $ramassage['created_by_name'] : '';
            
            $html = '
            <div class="row">
                <div class="col-md-6">
                    <h4>Informations du plan de ramassage</h4>
                    <table class="table table-bordered">
                        <tr>
                            <th style="width: 40%">Entité (qui ramasse):</th>
                            <td>' . htmlspecialchars($entite_nom) . '</td>
                        </tr>
                        <tr>
                            <th>Agence à ramasser:</th>
                            <td>' . htmlspecialchars($agence_nom) . '</td>
                        </tr>
                        <tr>
                            <th>Période:</th>
                            <td><strong>' . htmlspecialchars($ramassage['periode']) . '</strong></td>
                        </tr>
                        <tr>
                            <th>Date de début:</th>
                            <td>' . $date_debut . '</td>
                        </tr>
                        <tr>
                            <th>Date de fin:</th>
                            <td>' . $date_fin . '</td>
                        </tr>
                        <tr>
                            <th>Date de création:</th>
                            <td>' . $date_creation . '</td>
                        </tr>
                        <tr>
                            <th>Créé par:</th>
                            <td>' . htmlspecialchars($created_by_name) . '</td>
                        </tr>
                    </table>
                </div>
                
                <div class="col-md-6">
                    <h4>État et Observations</h4>
                    <table class="table table-bordered">
                        <tr>
                            <th style="width: 40%">État de validité:</th>
                            <td>
                                <span class="label label-' . $etatValidite . '">
                                    <i class="fa ' . $etatIcon . '"></i> ' . $etatText . '
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Observations:</th>
                            <td>' . nl2br(htmlspecialchars($ramassage['observations'])) . '</td>
                        </tr>
                    </table>
                </div>
            </div>';
            
            if (!empty($ramassage['liste_path'])) {
                $html .= '
                <div class="row">
                    <div class="col-md-12">
                        <h4>Liste des ramasseurs</h4>
                        <div class="alert alert-info">
                            <i class="fa fa-file-pdf"></i> 
                            <strong>Document PDF:</strong> 
                            <a href="' . htmlspecialchars($ramassage['liste_path']) . '" target="_blank" class="btn btn-primary btn-sm">
                                <i class="fa fa-eye"></i> Voir la liste des ramasseurs
                            </a>
                            <br>
                            <small>Cette liste est valable pendant 1 mois à partir de la date de début.</small>
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
        $limit = 10;
        $offset = ($page - 1) * $limit;
        
        try {
            $user = isset($_SESSION['user']) ? $_SESSION['user'] : array();
            
            if (!empty($search)) {
                $ramassages = Ramassage::search($search, $limit, $offset);
                $total = Ramassage::searchCount($search);
            } else {
                $ramassages = Ramassage::getPaginated($limit, $offset);
                $total = Ramassage::count();
            }
            
            $html = '';
            if (!empty($ramassages)) {
                foreach ($ramassages as $ramassage) {
                    $html .= $this->generateRamassageRowHtml($ramassage, $user);
                }
            } else {
                $html = '<tr id="aucun-ramassage"><td colspan="9" class="center">Aucun plan de ramassage trouvé</td></tr>';
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
        $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;
        
        try {
            $user = isset($_SESSION['user']) ? $_SESSION['user'] : array();
            
            $ramassages = Ramassage::search($search, $limit, $offset);
            $total = Ramassage::searchCount($search);
            
            $html = '';
            if (!empty($ramassages)) {
                foreach ($ramassages as $ramassage) {
                    $html .= $this->generateRamassageRowHtml($ramassage, $user);
                }
            } else {
                $html = '<tr id="aucun-ramassage"><td colspan="9" class="center">Aucun plan de ramassage trouvé pour "' . htmlspecialchars($search) . '"</td></tr>';
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
    
    private function generateRamassageRowHtml($ramassage, $user) {
        if (!is_array($ramassage)) {
            return '';
        }
        
        // Déterminer l'état de validité
        $etatValidite = 'label-success';
        $etatText = 'Valide';
        
        if (isset($ramassage['date_fin'])) {
            $dateFin = is_string($ramassage['date_fin']) ? strtotime($ramassage['date_fin']) : $ramassage['date_fin'];
            $aujourdhui = time();
            
            if ($dateFin < $aujourdhui) {
                $etatValidite = 'label-danger';
                $etatText = 'Expiré';
            } else {
                $joursRestants = floor(($dateFin - $aujourdhui) / (60 * 60 * 24));
                
                if ($joursRestants <= 7) {
                    $etatValidite = 'label-warning';
                    $etatText = 'Expire bientôt (' . $joursRestants . 'j)';
                }
            }
        }
        
        $ramassage_id = isset($ramassage['id']) ? $ramassage['id'] : 0;
        $entite_nom = isset($ramassage['entite_nom']) ? $ramassage['entite_nom'] : (isset($ramassage['entite_id']) ? 'Entité ' . $ramassage['entite_id'] : '');
        $agence_nom = isset($ramassage['agence_nom']) ? $ramassage['agence_nom'] : (isset($ramassage['agence_id']) ? 'Agence ' . $ramassage['agence_id'] : '');
        $periode = isset($ramassage['periode']) ? $ramassage['periode'] : '';
        $created_by = isset($ramassage['created_by_name']) ? $ramassage['created_by_name'] : '';
        
        $date_creation = '';
        if (isset($ramassage['date_creation']) && !empty($ramassage['date_creation'])) {
            try {
                if ($ramassage['date_creation'] instanceof \DateTime) {
                    $date_creation = $ramassage['date_creation']->format('d-m-Y H:i');
                } else {
                    $date_creation = date('d-m-Y H:i', strtotime($ramassage['date_creation']));
                }
            } catch (\Exception $e) {
                $date_creation = $ramassage['date_creation'];
            }
        }
        
        $date_debut = '';
        if (isset($ramassage['date_debut']) && !empty($ramassage['date_debut'])) {
            try {
                if ($ramassage['date_debut'] instanceof \DateTime) {
                    $date_debut = $ramassage['date_debut']->format('d-m-Y');
                } else {
                    $date_debut = date('d-m-Y', strtotime($ramassage['date_debut']));
                }
            } catch (\Exception $e) {
                $date_debut = $ramassage['date_debut'];
            }
        }
        
        $date_fin = '';
        if (isset($ramassage['date_fin']) && !empty($ramassage['date_fin'])) {
            try {
                if ($ramassage['date_fin'] instanceof \DateTime) {
                    $date_fin = $ramassage['date_fin']->format('d-m-Y');
                } else {
                    $date_fin = date('d-m-Y', strtotime($ramassage['date_fin']));
                }
            } catch (\Exception $e) {
                $date_fin = $ramassage['date_fin'];
            }
        }
        
        $html = '<tr id="ramassage-' . $ramassage_id . '">';
        $html .= '<td class="center">' . htmlspecialchars($date_creation) . '</td>';
        $html .= '<td>' . htmlspecialchars($entite_nom) . '</td>';
        $html .= '<td>' . htmlspecialchars($agence_nom) . '</td>';
        $html .= '<td>' . htmlspecialchars($periode) . '</td>';
        $html .= '<td class="center">' . htmlspecialchars($date_debut) . '</td>';
        $html .= '<td class="center">' . htmlspecialchars($date_fin) . '</td>';
        $html .= '<td class="center"><span class="label ' . $etatValidite . '">' . htmlspecialchars($etatText) . '</span></td>';
        $html .= '<td>' . htmlspecialchars($created_by) . '</td>';
        $html .= '<td class="center">';
        $html .= '<div class="hidden-sm hidden-xs action-buttons">';
        
        // Bouton "Voir les détails" pour tous les utilisateurs
        $html .= '<a href="#" class="details-ramassage purple" data-id="' . $ramassage_id . '" title="Voir les détails">';
        $html .= '<i class="ace-icon fa fa-info-circle bigger-130"></i></a> ';
        
        // Bouton "Modifier" - tous les utilisateurs autorisés peuvent modifier
        $html .= '<a href="#" class="modifier-ramassage green" data-id="' . $ramassage_id . '" title="Modifier">';
        $html .= '<i class="ace-icon fa fa-pencil bigger-130"></i></a>';
        
        $html .= '</div></td></tr>';
        
        return $html;
    }
}
?>