<?php
namespace Core\Controller;

use Core\Model\App;
use Core\Model\Session;
use Core\Database\Ramassage;
use Core\Model\AppController;

class RamassageController extends AppController {
    
    public function __construct() {
        parent::__construct();
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    public function index() {
        if (!isset($_SESSION['user'])) {
            header('Location: index.php?p=login');
            exit();
        }
        
        $user = $_SESSION['user'];
        
        $totalRamassages = Ramassage::count();
        $ramassages = Ramassage::getAll();
        
        $agences = [];
        
        $data = [
            'ramassages' => $ramassages,
            'user' => $user,
            'totalRamassages' => $totalRamassages,
            'agences' => $agences
        ];
        
        $this->render('Ramassage.index', $data);
    }
    
    public function ajoutRamassage() {
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        
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
        
        $requiredFields = [
            'entite_nom' => 'Entité (qui ramasse)',
            'agence_nom' => 'Agence à ramasser',
            'periode' => 'Période',
            'date_debut' => 'Date de début',
            'date_fin' => 'Date de fin'
        ];
        
        foreach ($requiredFields as $field => $label) {
            if (empty($_POST[$field])) {
                $errors[] = "Le champ '$label' est obligatoire";
            }
        }
        
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
        
        if (strtotime($date_debut) >= strtotime($date_fin)) {
            echo json_encode([
                'success' => false,
                'message' => 'La date de début doit être antérieure à la date de fin'
            ]);
            return;
        }
        
        $diff = strtotime($date_fin) - strtotime($date_debut);
        $jours = floor($diff / (60 * 60 * 24));
        
        if ($jours > 31) {
            echo json_encode([
                'success' => false,
                'message' => 'La période ne doit pas dépasser 1 mois (31 jours)'
            ]);
            return;
        }
        
        // Récupération des noms
        $entite_nom = trim($_POST['entite_nom']);
        $agence_nom = trim($_POST['agence_nom']);
        
        $data = [
            'entite_nom' => $entite_nom,
            'agence_nom' => $agence_nom,
            'periode' => trim($_POST['periode']),
            'date_debut' => $date_debut,
            'date_fin' => $date_fin,
            'observations' => isset($_POST['observations']) ? trim($_POST['observations']) : '',
            'created_by' => isset($user['idUser']) ? $user['idUser'] : (isset($user['id']) ? $user['id'] : null)
        ];
        
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
            
            $fileType = $_FILES['liste_ramasseurs']['type'];
            $fileExtension = strtolower(pathinfo($_FILES['liste_ramasseurs']['name'], PATHINFO_EXTENSION));
            
            if ($fileType !== 'application/pdf' || $fileExtension !== 'pdf') {
                echo json_encode([
                    'success' => false,
                    'message' => 'Type de fichier non autorisé. Seuls les fichiers PDF sont acceptés'
                ]);
                return;
            }
            
            $maxFileSize = 10 * 1024 * 1024;
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
        }
        
        try {
            error_log("🔄 Tentative d'insertion ramassage: " . print_r($data, true));
            
            $ramassageId = Ramassage::create($data, $listePath);
            
            if (!$ramassageId) {
                throw new \Exception('Erreur lors de l\'insertion dans la base de données');
            }
            
            $ramassageInserted = Ramassage::find($ramassageId);
            
            echo json_encode([
                'success' => true,
                'message' => 'Plan de ramassage ajouté avec succès',
                'ramassage' => $ramassageInserted,
                'refreshNeeded' => false
            ]);
            
        } catch (\Exception $e) {
            error_log("❌ Erreur ajoutRamassage: " . $e->getMessage());
            error_log("❌ Stack trace: " . $e->getTraceAsString());
            
            echo json_encode([
                'success' => false,
                'message' => 'Erreur serveur: ' . $e->getMessage()
            ]);
        }
    }
    
    public function getRamassageData() {
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        
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
            
            $canModify = true;
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
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        
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
        
        $ramassage = Ramassage::find($ramassage_id);
        if (!$ramassage) {
            echo json_encode([
                'success' => false,
                'message' => 'Ramassage non trouvé'
            ]);
            return;
        }
        
        $isAdmin = in_array($user['privilege'], ['Administration', 'SuperAdministration']);
        
        $errors = [];
        
        if ($isAdmin) {
            $requiredFields = [
                'entite_nom' => 'Entité (qui ramasse)',
                'agence_nom' => 'Agence à ramasser',
                'periode' => 'Période',
                'date_debut' => 'Date de début',
                'date_fin' => 'Date de fin'
            ];
            
            foreach ($requiredFields as $field => $label) {
                if (empty($_POST[$field])) {
                    $errors[] = "Le champ '$label' est obligatoire";
                }
            }
            
            $date_debut = $_POST['date_debut'];
            $date_fin = $_POST['date_fin'];
            
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
        
        $data = [
            'updated_at' => date('Y-m-d H:i:s'),
            'updated_by' => isset($user['idUser']) ? $user['idUser'] : (isset($user['id']) ? $user['id'] : null),
            'observations' => trim($_POST['observations'])
        ];
        
        if ($isAdmin) {
            $data['entite_nom'] = trim($_POST['entite_nom']);
            $data['agence_nom'] = trim($_POST['agence_nom']);
            $data['periode'] = trim($_POST['periode']);
            $data['date_debut'] = isset($date_debut) ? $date_debut : $ramassage['date_debut'];
            $data['date_fin'] = isset($date_fin) ? $date_fin : $ramassage['date_fin'];
        }
        
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
            
            $fileType = $_FILES['liste_ramasseurs']['type'];
            $fileExtension = strtolower(pathinfo($_FILES['liste_ramasseurs']['name'], PATHINFO_EXTENSION));
            
            if ($fileType !== 'application/pdf' || $fileExtension !== 'pdf') {
                echo json_encode([
                    'success' => false,
                    'message' => 'Type de fichier non autorisé. Seuls les fichiers PDF sont acceptés'
                ]);
                return;
            }
            
            $maxFileSize = 10 * 1024 * 1024;
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
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        
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
            
            // RÉCUPÉRER LES NOMS DEPUIS LES DONNÉES DU RAMASSAGE
            $entite_nom = isset($ramassage['entite_nom']) && !empty($ramassage['entite_nom']) 
                ? $ramassage['entite_nom'] 
                : 'Non défini';
            
            $agence_nom = isset($ramassage['agence_nom']) && !empty($ramassage['agence_nom']) 
                ? $ramassage['agence_nom'] 
                : 'Non défini';
            
            // FORMATAGE DES DATES
            $date_creation = '';
            if (isset($ramassage['date_creation']) && !empty($ramassage['date_creation'])) {
                try {
                    if ($ramassage['date_creation'] instanceof \DateTime) {
                        $date_creation = $ramassage['date_creation']->format('d/m/Y H:i');
                    } else {
                        $date_creation = date('d/m/Y H:i', strtotime($ramassage['date_creation']));
                    }
                } catch (\Exception $e) {
                    $date_creation = is_string($ramassage['date_creation']) ? $ramassage['date_creation'] : '';
                }
            }
            
            $date_debut = '';
            if (isset($ramassage['date_debut']) && !empty($ramassage['date_debut'])) {
                try {
                    if ($ramassage['date_debut'] instanceof \DateTime) {
                        $date_debut = $ramassage['date_debut']->format('d/m/Y');
                    } else {
                        $date_debut = date('d/m/Y', strtotime($ramassage['date_debut']));
                    }
                } catch (\Exception $e) {
                    $date_debut = is_string($ramassage['date_debut']) ? $ramassage['date_debut'] : '';
                }
            }
            
            $date_fin = '';
            if (isset($ramassage['date_fin']) && !empty($ramassage['date_fin'])) {
                try {
                    if ($ramassage['date_fin'] instanceof \DateTime) {
                        $date_fin = $ramassage['date_fin']->format('d/m/Y');
                    } else {
                        $date_fin = date('d/m/Y', strtotime($ramassage['date_fin']));
                    }
                } catch (\Exception $e) {
                    $date_fin = is_string($ramassage['date_fin']) ? $ramassage['date_fin'] : '';
                }
            }
            
            // CALCUL DE L'ÉTAT DE VALIDITÉ
            $etatValidite = 'success';
            $etatText = 'Valide';
            $etatIcon = 'fa-check-circle';
            
            if (isset($ramassage['date_fin']) && !empty($ramassage['date_fin'])) {
                try {
                    if ($ramassage['date_fin'] instanceof \DateTime) {
                        $dateFin = $ramassage['date_fin']->getTimestamp();
                    } else {
                        $dateFin = strtotime($ramassage['date_fin']);
                    }
                    
                    if ($dateFin) {
                        $aujourdhui = time();
                        
                        if ($dateFin < $aujourdhui) {
                            $etatValidite = 'danger';
                            $etatText = 'Expiré';
                            $etatIcon = 'fa-times-circle';
                        } else {
                            $joursRestants = floor(($dateFin - $aujourdhui) / (60 * 60 * 24));
                            
                            if ($joursRestants <= 7) {
                                $etatValidite = 'warning';
                                $etatText = 'Expire bientôt (' . $joursRestants . ' jour' . ($joursRestants > 1 ? 's' : '') . ' restant' . ($joursRestants > 1 ? 's' : '') . ')';
                                $etatIcon = 'fa-exclamation-triangle';
                            }
                        }
                    }
                } catch (\Exception $e) {
                    error_log("Erreur calcul état validité: " . $e->getMessage());
                }
            }
            
            $created_by_name = isset($ramassage['created_by_name']) ? $ramassage['created_by_name'] : '';
            $periode = isset($ramassage['periode']) ? $ramassage['periode'] : '';
            $observations = isset($ramassage['observations']) ? $ramassage['observations'] : '';
            
            // CONSTRUCTION DU HTML
            $html = '
            <style>
                .details-card {
                    background: white;
                    border-radius: 8px;
                    padding: 0;
                    margin-bottom: 15px;
                }
                .details-table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-bottom: 20px;
                    background: white;
                    border-radius: 8px;
                    overflow: hidden;
                    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
                }
                .details-table th {
                    background: #f8fafc;
                    padding: 12px 15px;
                    text-align: left;
                    font-weight: 600;
                    color: #2c3e50;
                    width: 40%;
                    border-bottom: 1px solid #e9ecef;
                    font-size: 13px;
                }
                .details-table td {
                    padding: 12px 15px;
                    border-bottom: 1px solid #e9ecef;
                    color: #495057;
                    font-size: 13px;
                }
                .details-table tr:last-child th,
                .details-table tr:last-child td {
                    border-bottom: none;
                }
                .badge-success {
                    background: #d5f5e3;
                    color: #13863c;
                    padding: 5px 12px;
                    border-radius: 20px;
                    display: inline-block;
                    font-size: 12px;
                    font-weight: 600;
                }
                .badge-warning {
                    background: #fef5e7;
                    color: #b36b00;
                    padding: 5px 12px;
                    border-radius: 20px;
                    display: inline-block;
                    font-size: 12px;
                    font-weight: 600;
                }
                .badge-danger {
                    background: #fdedec;
                    color: #c0392b;
                    padding: 5px 12px;
                    border-radius: 20px;
                    display: inline-block;
                    font-size: 12px;
                    font-weight: 600;
                }
                .section-title {
                    margin-top: 0;
                    margin-bottom: 15px;
                    font-size: 15px;
                    font-weight: 600;
                    color: #4361ee;
                    display: flex;
                    align-items: center;
                    gap: 8px;
                    border-bottom: 1px solid #e9ecef;
                    padding-bottom: 10px;
                }
                .file-box {
                    background: #e8f4fc;
                    padding: 15px;
                    border-radius: 8px;
                    border: 1px dashed #3498db;
                    margin-top: 10px;
                }
                .file-box a {
                    background: #4361ee;
                    color: white;
                    padding: 8px 15px;
                    border-radius: 6px;
                    text-decoration: none;
                    display: inline-block;
                    margin-top: 10px;
                    font-size: 13px;
                }
                .file-box a:hover {
                    background: #3a56d4;
                }
            </style>
            
            <div class="details-card">
                <div class="row">
                    <div class="col-md-6">
                        <h4 class="section-title">
                            <i class="fas fa-info-circle" style="color:#4361ee;"></i> 
                            INFORMATIONS DU PLAN
                        </h4>
                        <table class="details-table">
                            <tr>
                                <th><i class="fas fa-building" style="width:20px;"></i> Entité (qui ramasse):</th>
                                <td><strong>' . htmlspecialchars($entite_nom) . '</strong></td>
                            </tr>
                            <tr>
                                <th><i class="fas fa-store" style="width:20px;"></i> Agence à ramasser:</th>
                                <td><strong>' . htmlspecialchars($agence_nom) . '</strong></td>
                            </tr>
                            <tr>
                                <th><i class="fas fa-calendar-week" style="width:20px;"></i> Période:</th>
                                <td><strong>' . htmlspecialchars($periode) . '</strong></td>
                            </tr>
                            <tr>
                                <th><i class="fas fa-calendar-alt" style="width:20px;"></i> Date de début:</th>
                                <td>' . htmlspecialchars($date_debut) . '</td>
                            </tr>
                            <tr>
                                <th><i class="fas fa-calendar-alt" style="width:20px;"></i> Date de fin:</th>
                                <td>' . htmlspecialchars($date_fin) . '</td>
                            </tr>
                            <tr>
                                <th><i class="fas fa-clock" style="width:20px;"></i> Date de création:</th>
                                <td>' . htmlspecialchars($date_creation) . '</td>
                            </tr>
                            <tr>
                                <th><i class="fas fa-user" style="width:20px;"></i> Créé par:</th>
                                <td>' . htmlspecialchars($created_by_name) . '</td>
                            </tr>
                        </table>
                    </div>
                    
                    <div class="col-md-6">
                        <h4 class="section-title">
                            <i class="fas fa-chart-line" style="color:#4361ee;"></i> 
                            ÉTAT ET OBSERVATIONS
                        </h4>
                        <table class="details-table">
                            <tr>
                                <th><i class="fas fa-check-circle" style="width:20px;"></i> État de validité:</th>
                                <td>
                                    <span class="badge-' . $etatValidite . '">
                                        <i class="fa ' . $etatIcon . '"></i> ' . htmlspecialchars($etatText) . '
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th><i class="fas fa-comment" style="width:20px;"></i> Observations:</th>
                                <td>' . nl2br(htmlspecialchars($observations)) . '</td>
                            </tr>
                        </table>';
            
            if (!empty($ramassage['liste_path']) && file_exists($ramassage['liste_path'])) {
                $fileName = basename($ramassage['liste_path']);
                $html .= '
                        <h4 class="section-title" style="margin-top:20px;">
                            <i class="fas fa-file-pdf" style="color:#4361ee;"></i> 
                            LISTE DES RAMASSEURS
                        </h4>
                        <div class="file-box">
                            <div style="display:flex; align-items:center; gap:10px;">
                                <i class="fas fa-file-pdf" style="font-size:24px; color:#e74c3c;"></i>
                                <div>
                                    <strong style="font-size:14px;">' . htmlspecialchars($fileName) . '</strong>
                                    <br>
                                    <small class="text-muted">Document PDF • Valable pendant 1 mois</small>
                                </div>
                            </div>
                            <div style="margin-top:15px; text-align:right;">
                                <a href="' . htmlspecialchars($ramassage['liste_path']) . '" target="_blank" class="btn-modern btn-modern-primary" style="padding:8px 20px;">
                                    <i class="fas fa-eye"></i> Voir la liste
                                </a>
                                <a href="' . htmlspecialchars($ramassage['liste_path']) . '" download class="btn-modern btn-modern-secondary" style="padding:8px 20px; margin-left:10px;">
                                    <i class="fas fa-download"></i> Télécharger
                                </a>
                            </div>
                        </div>';
            }
            
            $html .= '
                    </div>
                </div>
            </div>';
            
            echo json_encode([
                'success' => true,
                'html' => $html,
                'ramassage' => $ramassage
            ]);
            
        } catch (\Exception $e) {
            error_log("❌ Erreur getDetails: " . $e->getMessage());
            error_log("❌ Stack trace: " . $e->getTraceAsString());
            
            echo json_encode([
                'success' => false,
                'message' => 'Erreur serveur: ' . $e->getMessage()
            ]);
        }
    }
    
    public function getTableData() {
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        
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
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        
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
        
        $etatValidite = 'label-success';
        $etatText = 'Valide';
        
        if (isset($ramassage['date_fin']) && !empty($ramassage['date_fin'])) {
            try {
                if ($ramassage['date_fin'] instanceof \DateTime) {
                    $dateFin = $ramassage['date_fin']->getTimestamp();
                } else {
                    $dateFin = strtotime($ramassage['date_fin']);
                }
                
                if ($dateFin) {
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
            } catch (\Exception $e) {
                error_log("Erreur calcul état: " . $e->getMessage());
            }
        }
        
        $ramassage_id = isset($ramassage['id']) ? $ramassage['id'] : 0;
        
        // RÉCUPÉRER LES NOMS DEPUIS LES DONNÉES
        $entite_nom = isset($ramassage['entite_nom']) && !empty($ramassage['entite_nom']) 
            ? $ramassage['entite_nom'] 
            : 'Non défini';
        
        $agence_nom = isset($ramassage['agence_nom']) && !empty($ramassage['agence_nom']) 
            ? $ramassage['agence_nom'] 
            : 'Non défini';
        
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
                $date_creation = is_string($ramassage['date_creation']) ? $ramassage['date_creation'] : '';
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
                $date_debut = is_string($ramassage['date_debut']) ? $ramassage['date_debut'] : '';
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
                $date_fin = is_string($ramassage['date_fin']) ? $ramassage['date_fin'] : '';
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
        
        $html .= '<a href="#" class="details-ramassage purple" data-id="' . $ramassage_id . '" title="Voir les détails">';
        $html .= '<i class="ace-icon fa fa-info-circle bigger-130"></i></a> ';
        
        $allowedRoles = ['Administration', 'SuperAdministration', 'Agence', 'AgenceSage', 'Comptabilite', 'ControleInterne', 'Controleur'];
        if (in_array($user['privilege'], $allowedRoles)) {
            $html .= '<a href="#" class="modifier-ramassage green" data-id="' . $ramassage_id . '" title="Modifier">';
            $html .= '<i class="ace-icon fa fa-pencil bigger-130"></i></a>';
        }
        
        $html .= '</div></td></tr>';
        
        return $html;
    }
}