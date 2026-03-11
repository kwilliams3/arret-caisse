<?php
use Core\Model\App;
use Core\Model\Session;
use Core\Database\Agence;
use Core\Database\Cheque;
use Core\Database\ClientAutorise;

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
    } catch (Exception $e) {
        error_log("Erreur récupération agence utilisateur: " . $e->getMessage());
        $userAgenceNom = "Agence " . $userAgenceId;
    }
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
        $operations = Cheque::search($search, 1000, 0, $type);
    } else {
        $operations = Cheque::getAll($type);
    }
} else {
    if (!empty($search)) {
        $operations = Cheque::searchByAgence($search, $userAgenceId, 1000, 0, $type);
    } else {
        $operations = Cheque::getByAgence($userAgenceId, $type);
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

// ============================================
// RÉCUPÉRATION DES CLIENTS AUTORISÉS
// ============================================

// Mapping entre les noms d'agences et les sites
$siteMapping = array(
    'PV MOKOLO' => array('PV MOKOLO', 'MOKOLO'),
    'HM MOKOLO' => array('HM MOKOLO', 'MOKOLO'),
    'NLONGKAK' => array('NLONGKAK'),
    'GRAND HANGAR' => array('GRAND HANGAR'),
    'SHOW ROOM' => array('SHOW ROOM'),
    'TAMDJA' => array('TAMDJA'),
    'YASSA' => array('YASSA'),
    'BEACH' => array('BEACH'),
    'MBOPPI' => array('MBOPPI'),
    'EMERAUDE' => array('EMERAUDE'),
    'KRIBI' => array('KRIBI'),
    'SODIKO' => array('SODIKO'),
    'DIRECTION GENERALE' => array('DIRECTION GENERALE'),
    'DIRECTION REGIONALE YAOUNDE' => array('DIRECTION REGIONALE YAOUNDE'),
    'NDOGPASSI' => array('NDOGPASSI'),
    'PK12' => array('PK12'),
    'NOUVELLE AJOUT' => array('NOUVELLE AJOUT')
);

// Déterminer le site de l'utilisateur
$userSite = '';
if (!$isAdminOrCompta && $userAgenceNom) {
    foreach ($siteMapping as $site => $agences) {
        foreach ($agences as $agence) {
            if (stripos($userAgenceNom, $agence) !== false) {
                $userSite = $site;
                break 2;
            }
        }
    }
    if (empty($userSite)) {
        $userSite = $userAgenceNom;
    }
}

// Récupération des clients autorisés via le modèle ClientAutorise
$clientsAutorises = array();
$totalClients = 0;

try {
    if ($isAdminOrCompta) {
        // Admin et Compta voient tous les clients
        $clientsAutorises = ClientAutorise::getAll();
    } else {
        // Les agences ne voient que les clients de leur site
        if (!empty($userSite)) {
            $clientsAutorises = ClientAutorise::getBySite($userSite);
        }
    }
} catch (Exception $e) {
    error_log("Erreur récupération clients autorisés: " . $e->getMessage());
}

$totalClients = count($clientsAutorises);
?>

<!-- FONT AWESOME 4.5.0 LOCAL -->
<link rel="stylesheet" href="Public/font-awesome/4.5.0/css/font-awesome.min.css">

<!-- JQUERY LOCAL -->
<script src="Public/js/jquery-2.1.4.min.js"></script>

<!-- BOOTSTRAP LOCAL -->
<link rel="stylesheet" href="Public/css/bootstrap/css/bootstrap.min.css">
<script src="Public/js/bootstrap.min.js"></script>

<!-- DATEPICKER LOCAL -->
<link rel="stylesheet" href="Public/css/bootstrap-datepicker.min.css">
<script src="Public/js/bootstrap-datepicker.min.js"></script>
<script src="Public/js/bootstrap-datepicker.fr.min.js"></script>

<style>
:root { 
    --primary:#4361ee; 
    --primary-light:#e8edff; 
    --primary-dark:#3a56d4; 
    --secondary:#3a0ca3; 
    --success:#2ecc71; 
    --success-light:#d5f5e3; 
    --warning:#f39c12; 
    --warning-light:#fef5e7; 
    --danger:#e74c3c; 
    --danger-light:#fdedec; 
    --info:#3498db; 
    --info-light:#e8f4fc; 
    --dark:#2c3e50; 
    --gray:#6c757d; 
    --gray-light:#f8f9fa; 
    --gray-border:#e9ecef; 
    --radius:8px; 
    --shadow:0 2px 8px rgba(0,0,0,0.06); 
    --shadow-hover:0 4px 12px rgba(0,0,0,0.1); 
}
.modal { z-index: 1050 !important; }
.modal-backdrop { z-index: 1040 !important; }
.modal-dialog:not(.modal-dialog-centered) { margin-top: 80px !important; }
.modal-content { border-radius:12px; box-shadow:0 10px 50px rgba(0,0,0,0.3); }

.operations-container { max-width:100%; margin:0 auto; font-family:'Inter',sans-serif; }
.modern-header-banner { background:white; border-radius:var(--radius); padding:20px 25px; margin-bottom:25px; color:var(--dark); box-shadow:var(--shadow); border:1px solid var(--gray-border); }
.banner-title { font-size:18px; font-weight:700; margin-bottom:10px; display:flex; align-items:center; gap:10px; color:var(--primary); }
.banner-subtitle { font-size:13px; color:var(--gray); margin-bottom:15px; }

/* Annonce importante */
.announcement-banner {
    margin: 15px 0 25px 0;
    border-left: 5px solid #dc3545;
    border-radius: var(--radius);
    padding: 20px 25px;
    background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
    animation: pulse-glow-red 2s ease-in-out infinite;
    box-shadow: var(--shadow-hover);
    position: relative;
    overflow: hidden;
}
.announcement-banner::before {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: linear-gradient(45deg, transparent 30%, rgba(255, 255, 255, 0.3) 50%, transparent 70%);
    animation: shine 3s infinite linear;
}
.announcement-text {
    position: relative;
    display: inline-block;
    color: #721c24;
    font-weight: bold;
    text-shadow: 0 1px 2px rgba(0,0,0,0.1);
}
@keyframes pulse-glow-red {
    0% { box-shadow: 0 4px 15px rgba(220, 53, 69, 0.2); transform: scale(1); }
    50% { box-shadow: 0 8px 25px rgba(220, 53, 69, 0.4); transform: scale(1.002); }
    100% { box-shadow: 0 4px 15px rgba(220, 53, 69, 0.2); transform: scale(1); }
}
@keyframes shine {
    0% { transform: translateX(-100%) translateY(-100%) rotate(45deg); }
    100% { transform: translateX(100%) translateY(100%) rotate(45deg); }
}
@keyframes bounce {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-5px); }
}
@keyframes rotate {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.stats-grid { display:flex; flex-wrap:wrap; gap:15px; margin-top:15px; }
.stat-item { background:var(--gray-light); padding:10px 15px; border-radius:var(--radius); display:flex; align-items:center; gap:10px; }
.stat-value { font-size:20px; font-weight:700; color:var(--primary); }
.stat-label { font-size:12px; color:var(--gray); }

.legend-grid { display:flex; flex-wrap:wrap; gap:12px; margin-top:15px; }
.legend-item { display:flex; align-items:center; gap:8px; font-size:12px; background:var(--gray-light); padding:6px 10px; border-radius:6px; color:var(--dark); }
.legend-color { width:14px; height:14px; border-radius:3px; }
.legend-color.green { background:var(--success); }
.legend-color.blue { background:var(--info); }
.legend-color.purple { background:#8e44ad; }
.legend-color.orange { background:var(--warning); }
.legend-color.red { background:var(--danger); }

.actions-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(200px, 1fr)); gap:12px; margin-bottom:20px; }
.modern-action-btn { display:flex; flex-direction:column; align-items:center; padding:15px 10px; background:white; border:1px solid var(--gray-border); border-radius:var(--radius); text-decoration:none; color:var(--dark); transition:all 0.2s ease; box-shadow:var(--shadow); }
.modern-action-btn:hover { transform:translateY(-2px); box-shadow:var(--shadow-hover); border-color:var(--primary); text-decoration:none; color:var(--dark); }
.btn-icon { font-size:18px; margin-bottom:8px; }
.btn-text { font-weight:600; font-size:13px; }
.btn-subtext { font-size:10px; color:var(--gray); margin-top:3px; }
.add-operation-btn { background:linear-gradient(135deg, var(--primary), var(--primary-dark)); border:none; color:white; }
.add-operation-btn:hover { color:white; }

.modern-table-container { background:white; border-radius:var(--radius); padding:20px; box-shadow:var(--shadow); margin-bottom:30px; border:1px solid var(--gray-border); }
.table-header-modern { display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; padding-bottom:15px; border-bottom:1px solid var(--gray-border); flex-wrap:wrap; gap:15px; }
.table-title { font-size:16px; font-weight:600; color:var(--dark); display:flex; align-items:center; gap:10px; }

/* Barre de recherche moderne */
.search-wrapper {
    width: 350px;
}
.search-box {
    display: flex;
    align-items: center;
    background: #f8f9fa;
    border: 1px solid #dce4ec;
    border-radius: 25px;
    overflow: hidden;
    transition: all 0.3s ease;
}
.search-box:focus-within {
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(67,97,238,0.1);
}
.search-icon {
    background: transparent;
    border: none;
    padding: 0 15px;
    color: #95a5a6;
}
.search-input {
    flex: 1;
    border: none;
    background: transparent;
    padding: 10px 0;
    font-size: 14px;
    outline: none;
}
.search-btn {
    background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
    color: white;
    border: none;
    border-radius: 0 25px 25px 0;
    padding: 10px 20px;
    cursor: pointer;
    transition: all 0.3s ease;
}
.search-btn:hover {
    opacity: 0.9;
}
.clear-search {
    background: #f8d7da;
    border: none;
    color: #721c24;
    border-radius: 25px;
    padding: 8px 15px;
    margin-left: 10px;
    cursor: pointer;
    transition: all 0.3s ease;
}
.clear-search:hover {
    background: #f5c6cb;
}

.filter-select {
    padding: 10px 15px;
    border: 1px solid #dce4ec;
    border-radius: 25px;
    background: #f8f9fa;
    font-size: 14px;
    outline: none;
    transition: all 0.3s ease;
}
.filter-select:focus {
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(67,97,238,0.1);
}

#dynamic-table { width:100% !important; font-size:13px; }
#dynamic-table thead th { background:var(--gray-light); color:var(--dark); font-weight:600; font-size:11px; padding:12px 8px; }
#dynamic-table tbody td { padding:12px 8px; border-bottom:1px solid var(--gray-border); vertical-align:middle; }
#dynamic-table tbody tr:hover { background:var(--gray-light); }

.status-badge { display:inline-flex; align-items:center; gap:5px; padding:4px 10px; border-radius:12px; font-size:11px; font-weight:600; }
.status-en-cours { background:var(--warning-light); color:#b36b00; }
.status-termine { background:var(--success-light); color:#13863c; }
.status-annule { background:var(--danger-light); color:#c0392b; }

.type-cheque { color: var(--success); font-weight:600; font-size:16px; }
.type-virement { color: var(--info); font-weight:600; font-size:16px; }

.action-icons { display:flex; gap:8px; flex-wrap: wrap; justify-content: center; }
.action-link { display:inline-flex; align-items:center; justify-content:center; text-decoration:none; width:32px; height:32px; border-radius:4px; transition:all 0.2s ease; }
.action-link:hover { transform:translateY(-2px); box-shadow:var(--shadow); text-decoration:none; }
.action-link.purple i { color:#8e44ad; }
.action-link.green i { color:var(--success); }
.action-link.orange i { color:var(--warning); }
.action-link.blue i { color:var(--info); }
.action-link.red i { color:var(--danger); }
.action-link.teal i { color:#008080; }

.modal-header.modern-modal-header { background:linear-gradient(135deg,#0d1b3e,#1a2b5c); padding:20px 25px; border-radius:12px 12px 0 0; }
.modern-modal-header .modal-title { color:white; font-weight:600; font-size:16px; display:flex; align-items:center; gap:12px; }
.modern-modal-header .close { color:white; opacity:0.8; width:32px; height:32px; background:rgba(255,255,255,0.1); border-radius:50%; display:flex; align-items:center; justify-content:center; border:none; }
.modern-modal-header .close:hover { opacity:1; background:rgba(255,255,255,0.2); }
.modal-body.modern-modal-body { padding:25px; background:#f8fafc; max-height:70vh; overflow-y:auto; }
.modal-footer.modern-modal-footer { padding:20px 25px; background:white; border-top:1px solid #e9ecef; border-radius:0 0 12px 12px; display:flex; gap:10px; justify-content:flex-end; }

.modern-form-control { width:100%; padding:12px 15px; border:1px solid #dee2e6; border-radius:8px; font-size:14px; transition:all 0.3s ease; margin-bottom:15px; background:white; }
.modern-form-control:focus { border-color:#4361ee; box-shadow:0 0 0 3px rgba(67,97,238,0.1); outline:none; }
.modern-form-label { font-weight:600; font-size:13px; color:var(--dark); margin-bottom:5px; display:block; }
.modern-form-label i { margin-right:5px; color:var(--primary); }
.modern-form-label .required { color:var(--danger); margin-left:3px; }

.btn-modern { padding:10px 24px; border-radius:8px; font-weight:600; font-size:14px; border:none; cursor:pointer; transition:all 0.3s ease; display:inline-flex; align-items:center; gap:8px; }
.btn-modern-primary { background:linear-gradient(135deg,#4361ee,#3a56d4); color:white; }
.btn-modern-primary:hover { background:linear-gradient(135deg,#3a56d4,#2c3e50); color:white; }
.btn-modern-secondary { background:#f8f9fa; color:#6c757d; border:1px solid #dee2e6; }
.btn-modern-secondary:hover { background:#e9ecef; }
.btn-modern-danger { background:linear-gradient(135deg,#e74c3c,#c0392b); color:white; }
.btn-modern-danger:hover { background:linear-gradient(135deg,#c0392b,#a93226); color:white; }
.btn-modern-success { background:linear-gradient(135deg,#2ecc71,#27ae60); color:white; }
.btn-modern-success:hover { background:linear-gradient(135deg,#27ae60,#1e8449); color:white; }

.modern-loader { text-align:center; padding:40px 20px; }
.modern-loader i { font-size:32px; color:var(--primary); margin-bottom:15px; animation:spin 1s linear infinite; }
@keyframes spin { 100% { transform:rotate(360deg); } }

.modern-notification { position:fixed; top:20px; right:20px; padding:15px 20px; border-radius:var(--radius); z-index:10001; font-size:14px; transform:translateX(100%); opacity:0; transition:all 0.3s ease; box-shadow:var(--shadow-hover); cursor:pointer; }
.modern-notification.show { transform:translateX(0); opacity:1; }
.modern-notification.success { background:var(--success-light); color:#13863c; border-left:4px solid var(--success); }
.modern-notification.error { background:var(--danger-light); color:#c0392b; border-left:4px solid var(--danger); }
.modern-notification.warning { background:var(--warning-light); color:#b36b00; border-left:4px solid var(--warning); }

.statut-option { padding:15px; border:2px solid #e9ecef; border-radius:10px; text-align:center; cursor:pointer; transition:all 0.2s ease; background:white; }
.statut-option:hover { border-color:var(--primary); background:var(--primary-light); }
.statut-option.active { border-color:var(--primary); background:var(--primary-light); }
.statut-option i { font-size:28px; margin-bottom:10px; display:block; }

.empty-state { text-align:center; padding:40px; }
.empty-state i { font-size:48px; color:var(--gray); margin-bottom:15px; }
.empty-state h4 { color:var(--dark); margin-bottom:10px; }
.empty-state p { color:var(--gray); }

/* Radio group */
.radio-group { display:flex; gap:20px; margin-bottom:20px; }
.radio-option { display:flex; align-items:center; gap:8px; }
.radio-option input[type="radio"] { width:18px; height:18px; }

/* Pagination */
.pagination-modern { display:flex; justify-content:flex-end; gap:5px; margin-top:20px; }
.pagination-item { display:inline-flex; align-items:center; justify-content:center; min-width:36px; height:36px; padding:0 6px; border-radius:8px; background:white; border:1px solid #dee2e6; color:var(--dark); text-decoration:none; transition:all 0.2s ease; }
.pagination-item:hover { background:var(--primary-light); border-color:var(--primary); color:var(--primary); text-decoration:none; }
.pagination-item.active { background:var(--primary); border-color:var(--primary); color:white; }
.pagination-item.disabled { opacity:0.5; pointer-events:none; }
.pagination-info { padding:8px 0; color:var(--gray); }

.file-preview { margin-top:10px; padding:10px; border-radius:8px; background:white; border:1px dashed #dee2e6; }
.file-preview img { max-width:200px; max-height:150px; border:1px solid #ddd; border-radius:4px; }
.file-preview .file-info { margin-top:5px; font-size:12px; color:var(--gray); }

/* Styles pour les détails */
.details-card { background:white; border-radius:8px; padding:20px; margin-bottom:15px; border:1px solid var(--gray-border); }
.details-row { display:flex; padding:10px 0; border-bottom:1px solid var(--gray-border); }
.details-label { width:150px; font-weight:600; color:var(--dark); }
.details-value { flex:1; color:var(--gray); }
.details-value img { max-width:100%; max-height:300px; border-radius:8px; border:1px solid var(--gray-border); }

/* Styles pour les onglets */
.nav-tabs { border-bottom: 2px solid var(--gray-border); }
.nav-tabs .nav-link { 
    background: #f8f9fa; 
    border: 1px solid var(--gray-border); 
    border-bottom: none; 
    border-radius: 8px 8px 0 0; 
    padding: 12px 25px; 
    font-weight: 600;
    color: var(--dark);
    margin-right: 5px;
    cursor: pointer;
}
.nav-tabs .nav-link.active { 
    background: white; 
    border-color: var(--gray-border); 
    border-bottom: 2px solid white;
    color: var(--primary);
}
.nav-tabs .nav-link i { margin-right: 8px; }

/* Badge site */
.site-badge {
    background: var(--info);
    color: white;
    padding: 3px 10px;
    border-radius: 12px;
    font-size: 11px;
    margin-left: 10px;
}

/* FontAwesome Fixes pour FA4 */
.fa {
    font-family: FontAwesome !important;
}

/* Style pour le select des clients autorisés */
select.modern-form-control {
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
    background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%23333' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'><polyline points='6 9 12 15 18 9'/></svg>");
    background-repeat: no-repeat;
    background-position: right 10px center;
    background-size: 16px;
    padding-right: 30px;
}

.info-client-card {
    background: var(--info-light);
    border-left: 4px solid var(--info);
    padding: 10px;
    border-radius: 8px;
    margin-bottom: 15px;
    font-size: 13px;
}

.info-client-item {
    display: inline-block;
    margin-right: 20px;
}

.info-client-item i {
    color: var(--info);
    margin-right: 5px;
}

@media (max-width:768px) { 
    .actions-grid { grid-template-columns:repeat(2,1fr); } 
    .table-header-modern { flex-direction:column; align-items:flex-start; } 
    .search-wrapper { width:100%; }
    .modal-dialog { margin:10px !important; max-width:calc(100% - 20px) !important; } 
    .stats-grid { justify-content:center; }
    .pagination-modern { justify-content:center; }
    .details-row { flex-direction:column; }
    .details-label { width:100%; margin-bottom:5px; }
    .nav-tabs .nav-link { padding: 8px 15px; font-size: 14px; }
    .info-client-item { display: block; margin-bottom: 5px; }
}
@media (max-width:576px) { 
    .modern-header-banner,.modern-table-container { padding:15px; } 
    .actions-grid { grid-template-columns:1fr; } 
    .modal-footer.modern-modal-footer { flex-direction:column; } 
    .btn-modern { width:100%; justify-content:center; } 
    .stats-grid { flex-direction:column; }
    .stat-item { width:100%; }
    .radio-group { flex-direction:column; gap:10px; }
    .nav-tabs .nav-link { 
        padding: 8px 10px; 
        font-size: 12px;
        margin-right: 2px;
    }
}
</style>

<div class="operations-container">
    <!-- Fil d'Ariane moderne -->
    <div class="modern-breadcrumbs" style="margin-bottom:15px;">
        <ul class="breadcrumb" style="background:none; padding:0;">
            <li style="display:inline-block;">
                <i class="fa fa-home" style="color:var(--primary);"></i>
                <a href="#" onclick="return naviguerVers('<?php echo App::url("dashboard"); ?>')" style="color:var(--dark);">Arrêts Caisses</a>
            </li>
            <li style="display:inline-block; margin-left:8px;">
                <i class="fa fa-angle-right" style="color:var(--gray);"></i>
                Gestion des opérations
            </li>
            <?php if (!empty($search)): ?>
            <li style="display:inline-block; margin-left:8px;">
                <i class="fa fa-angle-right" style="color:var(--gray);"></i>
                <i class="fa fa-search" style="color:var(--warning);"></i>
                Recherche: "<?php echo htmlspecialchars($search); ?>"
            </li>
            <?php endif; ?>
            <?php if (!empty($type)): ?>
            <li style="display:inline-block; margin-left:8px;">
                <i class="fa fa-angle-right" style="color:var(--gray);"></i>
                <i class="fa <?php echo $type === 'cheque' ? 'fa-money' : 'fa-exchange'; ?>" style="color:<?php echo $type === 'cheque' ? 'var(--success)' : 'var(--info)'; ?>"></i>
                <?php echo $type === 'cheque' ? 'Chèques' : 'Virements'; ?>
            </li>
            <?php endif; ?>
        </ul>
    </div>

    <!-- ANNONCE IMPORTANTE -->
    <div class="announcement-banner">
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap:wrap; gap:15px;">
            <div style="flex: 1;">
                <h4 style="margin: 0 0 8px 0; color: #721c24; display: flex; align-items: center;">
                    <i class="fa fa-exclamation-triangle" style="margin-right: 10px; animation: bounce 1.5s infinite; color: #dc3545;"></i>
                    <span class="announcement-text">⚠️ ATTENTION IMPORTANT</span>
                </h4>
                <p style="margin: 0; font-size: 15px; font-weight: 600; color: #721c24;">
                    <strong>Tout ajout de chèque ou virement demeure sous la responsabilité 
                    exclusive du chef d'agence et engage pleinement son autorité.</strong>
                </p>
            </div>
            <div class="alert-icon" style="font-size: 40px; color: #dc3545; opacity: 0.8; animation: rotate 10s linear infinite;">
                <i class="fa fa-gavel"></i>
            </div>
        </div>
    </div>

    <!-- Header avec statistiques -->
    <div class="modern-header-banner">
        <h1 class="banner-title"><i class="fa fa-credit-card"></i> GESTION DES OPÉRATIONS</h1>
        <p class="banner-subtitle">
            Gestion des chèques et virements clients • Suivi en temps réel
            <?php if (!$isAdminOrCompta && $userAgenceNom): ?>
                - <strong><?php echo htmlspecialchars($userAgenceNom); ?></strong>
            <?php elseif ($isAdminOrCompta): ?>
                - <strong>Toutes les agences</strong>
            <?php endif; ?>
        </p>
        
        <div class="stats-grid">
            <div class="stat-item">
                <i class="fa fa-line-chart" style="color:var(--primary);"></i>
                <div>
                    <div class="stat-value"><?php echo $totalOperations; ?></div>
                    <div class="stat-label">Total opérations</div>
                </div>
            </div>
        </div>
        
        <div class="legend-grid">
            <div class="legend-item"><div class="legend-color green"></div><span>Confirmé</span></div>
            <div class="legend-item"><div class="legend-color orange"></div><span>En cours</span></div>
            <div class="legend-item"><div class="legend-color red"></div><span>Annulé</span></div>
        </div>
    </div>

    <!-- Filtres et recherche -->
    <div style="margin-bottom:20px;">
        <div style="display:flex; flex-wrap:wrap; gap:15px; align-items:flex-end;">
            <div style="min-width:200px;">
                <label class="modern-form-label"><i class="fa fa-filter"></i> Type d'opération</label>
                <select class="filter-select" id="filter-type" style="width:100%;">
                    <option value="">Tous les types</option>
                    <option value="cheque" <?php if ($type === 'cheque') echo 'selected'; ?>>Chèques</option>
                    <option value="virement" <?php if ($type === 'virement') echo 'selected'; ?>>Virements</option>
                </select>
            </div>
            
            <div style="flex:1; min-width:300px;">
                <label class="modern-form-label"><i class="fa fa-search"></i> Recherche</label>
                <div class="search-wrapper">
                    <div class="search-box">
                        <span class="search-icon"><i class="fa fa-search" style="color:#95a5a6;"></i></span>
                        <input type="text" id="search-operations" class="search-input" 
                               placeholder="<?php if ($isAdminOrCompta) { echo 'Rechercher dans toutes les agences...'; } else { echo 'Rechercher dans votre agence...'; } ?>"
                               value="<?php echo htmlspecialchars($search); ?>">
                        <button class="search-btn" id="btn-search">
                            <i class="fa fa-arrow-right"></i>
                        </button>
                    </div>
                    <?php if (!empty($search) || !empty($type)): ?>
                    <button class="clear-search" id="btn-clear-search" style="margin-top:10px;">
                        <i class="fa fa-times-circle"></i> Effacer les filtres
                    </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- NAVIGATION PAR ONGLETS -->
    <div style="margin-top: 20px; margin-bottom: 20px;">
        <ul class="nav nav-tabs" id="operationTabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="operations-tab" data-toggle="tab" href="#operations" role="tab" aria-controls="operations" aria-selected="true">
                    <i class="fa fa-list"></i> Opérations
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="clients-autorises-tab" data-toggle="tab" href="#clients-autorises" role="tab" aria-controls="clients-autorises" aria-selected="false">
                    <i class="fa fa-check-circle" style="color: var(--success);"></i> Clients autorisés par la direction
                    <span class="badge badge-success" id="clients-count" style="margin-left: 8px; background: var(--success); color: white; padding: 3px 8px; border-radius: 12px;"><?php echo $totalClients; ?></span>
                    <?php if (!$isAdminOrCompta && !empty($userSite)): ?>
                        <span class="site-badge">Site: <?php echo htmlspecialchars($userSite); ?></span>
                    <?php endif; ?>
                </a>
            </li>
        </ul>
    </div>

    <div class="tab-content" id="operationTabsContent">
        <!-- ONGLET 1 : OPÉRATIONS -->
        <div class="tab-pane active" id="operations" role="tabpanel" aria-labelledby="operations-tab">
            <!-- Actions -->
            <div class="actions-section">
                <div class="actions-grid">
                    <?php if($allowedToAdd) : ?>
                    <a href="#" class="modern-action-btn add-operation-btn" id="btn-ajouter-operation">
                        <i class="fa fa-plus btn-icon"></i>
                        <span class="btn-text">Ajouter</span>
                        <span class="btn-subtext">Nouvelle opération</span>
                    </a>
                    <?php endif; ?>
                    
                    <!-- BOUTON ARCHIVES - VISIBLE PAR TOUS -->
                    <a href="?p=confirmationCheque.archives" class="modern-action-btn" style="background: linear-gradient(135deg, #6c757d, #495057); color: white;">
                        <i class="fa fa-archive btn-icon"></i>
                        <span class="btn-text">Archives</span>
                        <span class="btn-subtext">Opérations terminées</span>
                    </a>
                </div>
            </div>

            <!-- Table -->
            <div class="modern-table-container">
                <div class="table-header-modern">
                    <div class="table-title">
                        <i class="fa fa-list"></i> 
                        Liste des opérations 
                        (<?php echo $totalOperations; ?> opération<?php if ($totalOperations > 1) echo 's'; ?>)
                    </div>
                    <div class="table-tools">
                        <span class="badge"><i class="fa fa-refresh fa-sm"></i> Actualisé à <?php echo date('H:i'); ?></span>
                    </div>
                </div>
                <div class="table-responsive">
                    <table id="dynamic-table" class="table table-hover">
                        <thead>
                            <tr>
                                <th class="text-center" width="50">Type</th>
                                <th class="text-center" width="90">Date Entrée</th>
                                <th class="text-left">Client</th>
                                <th class="text-left">N° / Réf</th>
                                <th class="text-right">Montant</th>
                                <th class="text-left">Banque</th>
                                <th class="text-center" width="80">Statut</th>
                                <th class="text-center" width="80">Confirmation</th>
                                <th class="text-center" width="80">Validation</th>
                                <th class="text-left">Agence</th>
                                <th class="text-center" width="140">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($operationsPagines)) : ?>
                                <?php 
                                $agencesCache = array();
                                if ($userAgenceId && !$isAdminOrCompta) {
                                    $agencesCache[$userAgenceId] = $userAgenceNom;
                                }
                                
                                foreach($operationsPagines as $operation) : 
                                    if (!is_array($operation)) continue;
                                    
                                    $agenceNom = '';
                                    $agenceId = '';
                                    if (isset($operation['agence_id'])) {
                                        $agenceId = $operation['agence_id'];
                                    }
                                    
                                    if ($agenceId) {
                                        if (!isset($agencesCache[$agenceId])) {
                                            try {
                                                $stmtAgenceOperation = Agence::searchById($agenceId);
                                                if ($stmtAgenceOperation) {
                                                    while ($agence = sqlsrv_fetch_array($stmtAgenceOperation, SQLSRV_FETCH_ASSOC)) {
                                                        if (isset($agence['designation'])) {
                                                            $agencesCache[$agenceId] = $agence['designation'];
                                                            $agenceNom = $agence['designation'];
                                                            break;
                                                        }
                                                    }
                                                }
                                            } catch (Exception $e) {
                                                $agencesCache[$agenceId] = "Agence " . $agenceId;
                                                $agenceNom = "Agence " . $agenceId;
                                            }
                                        } else {
                                            $agenceNom = $agencesCache[$agenceId];
                                        }
                                    }
                                    
                                    echo genererLigneOperationModerne($operation, $user, $agenceNom, $isAdminOrCompta, $isCompta, $allowedToAdd);
                                endforeach; 
                                ?>
                            <?php else : ?>
                            <tr>
                                <td colspan="11" class="text-center">
                                    <div class="empty-state">
                                        <i class="fa fa-credit-card"></i>
                                        <h4>Aucune opération trouvée</h4>
                                        <p>
                                            <?php if (!empty($search)): ?>
                                                <?php if ($isAdminOrCompta): ?>
                                                    Aucun résultat pour "<?php echo htmlspecialchars($search); ?>"
                                                <?php else: ?>
                                                    Aucun résultat pour "<?php echo htmlspecialchars($search); ?>" dans votre agence
                                                <?php endif; ?>
                                            <?php elseif (!empty($type)): ?>
                                                <?php if ($isAdminOrCompta): ?>
                                                    Aucun <?php if ($type === 'cheque') { echo 'chèque'; } else { echo 'virement'; } ?> enregistré
                                                <?php else: ?>
                                                    Aucun <?php if ($type === 'cheque') { echo 'chèque'; } else { echo 'virement'; } ?> enregistré dans votre agence
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <?php if ($isAdminOrCompta): ?>
                                                    Aucune opération enregistrée
                                                <?php else: ?>
                                                    Aucune opération enregistrée dans votre agence
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </p>
                                    </div>
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination moderne -->
                <?php if ($totalPages > 1) : ?>
                <div style="display:flex; flex-wrap:wrap; justify-content:space-between; align-items:center; margin-top:20px;">
                    <div class="pagination-info">
                        Affichage de <?php if ($totalOperations > 0) { echo $startIndex + 1; } else { echo 0; } ?> à <?php echo $endIndex; ?> sur <?php echo $totalOperations; ?> entrées
                    </div>
                    <div class="pagination-modern">
                        <?php
                        if ($page > 1) {
                            $prevUrl = '?p=confirmationCheque&page=' . ($page - 1);
                            if (!empty($search)) $prevUrl .= '&search=' . urlencode($search);
                            if (!empty($type)) $prevUrl .= '&type=' . urlencode($type);
                            echo '<a href="' . $prevUrl . '" class="pagination-item"><i class="fa fa-chevron-left"></i></a>';
                        } else {
                            echo '<span class="pagination-item disabled"><i class="fa fa-chevron-left"></i></span>';
                        }
                        
                        $maxPagesToShow = 5;
                        $startPage = max(1, $page - floor($maxPagesToShow / 2));
                        $endPage = min($totalPages, $startPage + $maxPagesToShow - 1);
                        
                        if ($endPage - $startPage + 1 < $maxPagesToShow) {
                            $startPage = max(1, $endPage - $maxPagesToShow + 1);
                        }
                        
                        if ($startPage > 1) {
                            $firstUrl = '?p=confirmationCheque&page=1';
                            if (!empty($search)) $firstUrl .= '&search=' . urlencode($search);
                            if (!empty($type)) $firstUrl .= '&type=' . urlencode($type);
                            echo '<a href="' . $firstUrl . '" class="pagination-item">1</a>';
                            if ($startPage > 2) {
                                echo '<span class="pagination-item disabled">...</span>';
                            }
                        }
                        
                        for ($i = $startPage; $i <= $endPage; $i++) {
                            if ($i == $page) {
                                echo '<span class="pagination-item active">' . $i . '</span>';
                            } else {
                                $pageUrl = '?p=confirmationCheque&page=' . $i;
                                if (!empty($search)) $pageUrl .= '&search=' . urlencode($search);
                                if (!empty($type)) $pageUrl .= '&type=' . urlencode($type);
                                echo '<a href="' . $pageUrl . '" class="pagination-item">' . $i . '</a>';
                            }
                        }
                        
                        if ($endPage < $totalPages) {
                            if ($endPage < $totalPages - 1) {
                                echo '<span class="pagination-item disabled">...</span>';
                            }
                            $lastUrl = '?p=confirmationCheque&page=' . $totalPages;
                            if (!empty($search)) $lastUrl .= '&search=' . urlencode($search);
                            if (!empty($type)) $lastUrl .= '&type=' . urlencode($type);
                            echo '<a href="' . $lastUrl . '" class="pagination-item">' . $totalPages . '</a>';
                        }
                        
                        if ($page < $totalPages) {
                            $nextUrl = '?p=confirmationCheque&page=' . ($page + 1);
                            if (!empty($search)) $nextUrl .= '&search=' . urlencode($search);
                            if (!empty($type)) $nextUrl .= '&type=' . urlencode($type);
                            echo '<a href="' . $nextUrl . '" class="pagination-item"><i class="fa fa-chevron-right"></i></a>';
                        } else {
                            echo '<span class="pagination-item disabled"><i class="fa fa-chevron-right"></i></span>';
                        }
                        ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- ONGLET 2 : CLIENTS AUTORISÉS -->
        <div class="tab-pane" id="clients-autorises" role="tabpanel" aria-labelledby="clients-autorises-tab">
            <div class="modern-table-container" style="margin-top: 20px;">
                <div class="table-header-modern">
                    <div class="table-title">
                        <i class="fa fa-check-circle" style="color: var(--success);"></i> 
                        Liste des clients autorisés par la direction
                        <span class="badge" style="background: var(--success); color: white; margin-left: 10px;"><?php echo $totalClients; ?></span>
                        <?php if (!$isAdminOrCompta && !empty($userSite)): ?>
                            <span class="site-badge">Site: <?php echo htmlspecialchars($userSite); ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="table-tools" style="display: flex; gap: 10px;">
                        <input type="text" id="search-clients-autorises" class="search-input" 
                               placeholder="Rechercher un client..." 
                               style="width: 250px; padding: 8px 15px; border-radius: 20px; border: 1px solid #dee2e6;">
                        <button class="btn-modern btn-modern-primary" id="btn-export-clients">
                            <i class="fa fa-download"></i> Exporter
                        </button>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table id="table-clients-autorises" class="table table-hover">
                        <thead>
                            <tr>
                                <th width="50">#</th>
                                <th>Site demandeur</th>
                                <th>Nom client</th>
                                <th>Contact</th>
                                <th class="text-right">Plafond (FCFA)</th>
                                <th>Parrainage / Conditions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($clientsAutorises)): ?>
                                <?php foreach($clientsAutorises as $index => $client): ?>
                                <tr>
                                    <td><?php echo $index + 1; ?></td>
                                    <td><strong><?php echo htmlspecialchars(isset($client['site_demandeur']) ? $client['site_demandeur'] : ''); ?></strong></td>
                                    <td><?php echo htmlspecialchars(isset($client['nom_client']) ? $client['nom_client'] : ''); ?></td>
                                    <td><?php echo htmlspecialchars(isset($client['contact']) ? $client['contact'] : ''); ?></td>
                                    <td class="text-right">
                                        <strong><?php echo isset($client['plafond']) ? number_format($client['plafond'], 0, ',', ' ') : '0'; ?></strong>
                                    </td>
                                    <td>
                                        <?php 
                                        $parrainage = isset($client['parrainage']) ? $client['parrainage'] : '';
                                        if (stripos($parrainage, 'jour') !== false || stripos($parrainage, 'mois') !== false):
                                        ?>
                                            <span class="badge" style="background: var(--info); color: white; padding: 3px 10px;"><?php echo htmlspecialchars($parrainage); ?></span>
                                        <?php else: ?>
                                            <?php echo htmlspecialchars($parrainage); ?>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center">
                                        <div class="empty-state">
                                            <i class="fa fa-users"></i>
                                            <h4>Aucun client autorisé</h4>
                                            <p>
                                                <?php if (!$isAdminOrCompta && !empty($userSite)): ?>
                                                    Aucun client trouvé pour le site "<?php echo htmlspecialchars($userSite); ?>"
                                                <?php else: ?>
                                                    La liste des clients autorisés par la direction est vide
                                                <?php endif; ?>
                                            </p>
                                            <p style="font-size:12px; color:#999;">
                                                <?php if ($totalClients == 0): ?>
                                                    Aucune donnée dans la table 'clients_autorises'. 
                                                    Veuillez exécuter le script d'import.
                                                <?php endif; ?>
                                            </p>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Statistiques -->
                <?php if (!empty($clientsAutorises)): ?>
                <div style="margin-top: 20px; padding: 15px; background: var(--gray-light); border-radius: var(--radius);">
                    <div style="display: flex; flex-wrap: wrap; gap: 20px;">
                        <div>
                            <i class="fa fa-users" style="color: var(--primary);"></i>
                            <strong><?php echo $totalClients; ?></strong> clients autorisés
                        </div>
                        <div>
                            <i class="fa fa-building" style="color: var(--primary);"></i>
                            <strong><?php 
                                $sites = array();
                                foreach($clientsAutorises as $client) {
                                    if(isset($client['site_demandeur']) && !empty($client['site_demandeur'])) {
                                        $sites[$client['site_demandeur']] = true;
                                    }
                                }
                                echo count($sites);
                            ?></strong> sites demandeurs
                        </div>
                        <div>
                            <i class="fa fa-line-chart" style="color: var(--primary);"></i>
                            <strong><?php 
                                $totalPlafond = 0;
                                foreach($clientsAutorises as $client) {
                                    if(isset($client['plafond'])) {
                                        $totalPlafond += $client['plafond'];
                                    }
                                }
                                echo number_format($totalPlafond, 0, ',', ' ');
                            ?></strong> FCFA (plafond total)
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- MODAL AJOUT OPÉRATION - AVEC LISTE DES CLIENTS AUTORISÉS -->
<div id="modal-ajout-operation" class="modal fade" style="z-index: 1050 !important;">
    <div class="modal-dialog modal-lg" style="margin: 80px 20px 20px 280px !important; max-width: 1000px !important;">
        <div class="modal-content">
            <div class="modal-header modern-modal-header">
                <h5 class="modal-title"><i class="fa fa-plus-circle"></i> Nouvelle opération</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body modern-modal-body" style="max-height: 70vh; overflow-y: auto; padding: 25px;">
                <form id="form-ajout-operation" enctype="multipart/form-data" autocomplete="off">
                    <input type="hidden" name="agence_id" value="<?php if ($userAgenceId) echo $userAgenceId; ?>">
                    <input type="hidden" name="created_by" value="<?php if (isset($user['idUser'])) { echo $user['idUser']; } elseif (isset($user['id'])) { echo $user['id']; } ?>">
                    
                    <?php if ($userAgenceNom): ?>
                    <div class="alert alert-info" style="background:var(--info-light); border-left:4px solid var(--info); padding:15px; border-radius:8px; margin-bottom:20px;">
                        <i class="fa fa-building" style="color:var(--info);"></i> 
                        <strong>Agence :</strong> <?php echo htmlspecialchars($userAgenceNom); ?>
                    </div>
                    <?php endif; ?>
                    
                    <div class="alert alert-info" style="background:var(--info-light); border-left:4px solid var(--info); padding:15px; border-radius:8px; margin-bottom:20px;">
                        <i class="fa fa-info-circle" style="color:var(--info);"></i> 
                        Tous les champs avec <span class="required">*</span> sont obligatoires
                    </div>
                    
                    <div class="alert alert-warning" style="background:var(--warning-light); border-left:4px solid var(--warning); padding:15px; border-radius:8px; margin-bottom:20px;">
                        <i class="fa fa-exclamation-triangle" style="color:var(--warning);"></i> 
                        <strong>Attention :</strong> Les numéros de chèques et références de virement doivent être uniques dans votre agence.
                    </div>
                    
                    <!-- Sélection du type d'opération -->
                    <div class="form-group">
                        <label class="modern-form-label"><i class="fa fa-tag"></i> Type d'opération <span class="required">*</span></label>
                        <div class="radio-group" style="display: flex; gap: 30px;">
                            <label class="radio-option" style="display: flex; align-items: center; gap: 8px;">
                                <input type="radio" name="type_operation" value="cheque" checked id="radio-cheque">
                                <i class="fa fa-money" style="color:var(--success);"></i> Chèque
                            </label>
                            <label class="radio-option" style="display: flex; align-items: center; gap: 8px;">
                                <input type="radio" name="type_operation" value="virement" id="radio-virement">
                                <i class="fa fa-exchange" style="color:var(--info);"></i> Virement
                            </label>
                        </div>
                    </div>
                    
                    <!-- NOUVELLE SECTION : TYPE DE CLIENT -->
                    <div class="form-group">
                        <label class="modern-form-label"><i class="fa fa-user"></i> Type de client <span class="required">*</span></label>
                        <div class="radio-group" style="display: flex; gap: 30px; margin-bottom: 15px;">
                            <label class="radio-option" style="display: flex; align-items: center; gap: 8px;">
                                <input type="radio" name="type_client" value="manuel" checked id="radio-client-manuel">
                                <i class="fa fa-pencil" style="color:var(--primary);"></i> Saisie manuelle
                            </label>
                            <label class="radio-option" style="display: flex; align-items: center; gap: 8px;">
                                <input type="radio" name="type_client" value="autorise" id="radio-client-autorise">
                                <i class="fa fa-check-circle" style="color:var(--success);"></i> Client autorisé
                            </label>
                        </div>
                    </div>
                    
                    <!-- SECTION CLIENT MANUEL (visible par défaut) -->
                    <div id="section-client-manuel" style="display: block;">
                        <div style="margin-bottom: 15px;">
                            <label class="modern-form-label"><i class="fa fa-user"></i> Nom Client <span class="required">*</span></label>
                            <input type="text" class="modern-form-control" id="input-nom-client-manuel" name="nom_client_manuel" placeholder="Nom du client" style="width: 100%;">
                        </div>
                    </div>
                    
                    <!-- SECTION CLIENT AUTORISÉ (cachée par défaut) -->
                    <div id="section-client-autorise" style="display: none;">
                        <div style="margin-bottom: 15px;">
                            <label class="modern-form-label"><i class="fa fa-list"></i> Sélectionner un client autorisé <span class="required">*</span></label>
                            <select class="modern-form-control" id="select-client-autorise" name="client_autorise_id" style="width: 100%;">
                                <option value="">-- Choisissez un client --</option>
                                <?php if (!empty($clientsAutorises)): ?>
                                    <?php foreach($clientsAutorises as $client): ?>
                                        <option value="<?php echo htmlspecialchars($client['nom_client']); ?>" 
                                                data-site="<?php echo htmlspecialchars(isset($client['site_demandeur']) ? $client['site_demandeur'] : ''); ?>"
                                                data-contact="<?php echo htmlspecialchars(isset($client['contact']) ? $client['contact'] : ''); ?>"
                                                data-plafond="<?php echo isset($client['plafond']) ? $client['plafond'] : 0; ?>">
                                            <?php echo htmlspecialchars($client['nom_client']); ?> 
                                            <?php if (isset($client['site_demandeur']) && !empty($client['site_demandeur'])): ?>
                                                (<?php echo htmlspecialchars($client['site_demandeur']); ?>)
                                            <?php endif; ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <option value="" disabled>Aucun client autorisé disponible</option>
                                <?php endif; ?>
                            </select>
                            <small class="text-muted" id="info-client-autorise" style="display: block; margin-top: 5px;"></small>
                        </div>
                        
                        <!-- Informations supplémentaires du client sélectionné -->
                        <div id="infos-client-autorise" class="info-client-card" style="display: none;">
                            <div style="display: flex; gap: 20px; flex-wrap: wrap;">
                                <div class="info-client-item">
                                    <i class="fa fa-building"></i> <span id="info-site"></span>
                                </div>
                                <div class="info-client-item">
                                    <i class="fa fa-phone"></i> <span id="info-contact"></span>
                                </div>
                                <div class="info-client-item">
                                    <i class="fa fa-money"></i> <span id="info-plafond"></span> FCFA
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Champ caché pour le nom client (celui qui sera envoyé au serveur) -->
                    <input type="hidden" id="input-nom-client" name="nom_client" value="">
                    
                    <!-- Ligne 2: Numéro Chèque -->
                    <div style="margin-bottom: 15px;">
                        <label class="modern-form-label" id="label-numero"><i class="fa fa-hashtag"></i> Numéro Chèque <span class="required">*</span></label>
                        <input type="text" class="modern-form-control" id="input-numero" name="numero_cheque" placeholder="Numéro du chèque" required style="width: 100%;">
                    </div>
                    
                    <!-- Ligne 3: Montant et Banque -->
                    <div style="display: flex; gap: 20px; margin-bottom: 15px;">
                        <div style="flex: 1;">
                            <label class="modern-form-label"><i class="fa fa-money"></i> Montant (FCFA) <span class="required">*</span></label>
                            <input type="number" class="modern-form-control" id="input-montant" name="montant" placeholder="Montant" step="0.01" min="1" required style="width: 100%;">
                        </div>
                        <div style="flex: 1;">
                            <label class="modern-form-label"><i class="fa fa-bank"></i> Banque <span class="required">*</span></label>
                            <input type="text" class="modern-form-control" id="input-banque" name="banque" placeholder="Nom de la banque" required style="width: 100%;">
                        </div>
                    </div>
                    
                    <!-- Ligne 4: Date de réception -->
                    <div style="margin-bottom: 15px;">
                        <label class="modern-form-label"><i class="fa fa-calendar"></i> Date de réception <span class="required">*</span></label>
                        <div style="display: flex; max-width: 50%;">
                            <input type="text" class="modern-form-control datepicker-operation" id="input-date-reception" name="date_reception" placeholder="JJ/MM/AAAA" required style="flex: 1; border-radius:8px 0 0 8px;">
                            <span class="btn-calendrier" style="background:var(--gray-light); border:1px solid #dee2e6; border-left:none; border-radius:0 8px 8px 0; padding:0 15px; display:flex; align-items:center; cursor:pointer;">
                                <i class="fa fa-calendar" style="color:var(--primary);"></i>
                            </span>
                        </div>
                    </div>
                    
                    <!-- Ligne 5: Scan du Chèque -->
                    <div style="margin-bottom: 15px;">
                        <label class="modern-form-label" id="label-scan"><i class="fa fa-file-image-o"></i> Scan du Chèque <span class="required">*</span></label>
                        <input type="file" class="modern-form-control" id="input-scan" name="scan_operation" 
                               accept=".jpg,.jpeg,.png,.pdf" required style="width: 100%; padding: 8px 15px;">
                        <small class="text-muted" id="scan-description">Formats acceptés: JPG, PNG, PDF (max 5MB) - OBLIGATOIRE</small>
                        <div id="preview-scan" class="file-preview" style="margin-top: 10px;"></div>
                    </div>
                    
                    <!-- Ligne 6: Observations -->
                    <div style="margin-bottom: 15px;">
                        <label class="modern-form-label"><i class="fa fa-comment"></i> Observations</label>
                        <textarea class="modern-form-control" id="input-observations" name="observations" placeholder="Observations supplémentaires" rows="2" style="width: 100%;"></textarea>
                    </div>
                    
                    <!-- Boutons -->
                    <div style="display: flex; justify-content: flex-end; gap: 15px; margin-top: 25px;">
                        <button type="button" class="btn-modern btn-modern-secondary" data-dismiss="modal" style="padding: 12px 30px;">
                            <i class="fa fa-times"></i> Annuler
                        </button>
                        <button type="button" id="btn-enregistrer-operation" class="btn-modern btn-modern-primary" style="padding: 12px 30px;">
                            <i class="fa fa-save"></i> Enregistrer
                        </button>
                    </div>
                    
                    <div class="modern-loader loaderRegister" style="display:none; text-align: center; padding: 20px;">
                        <i class="fa fa-spinner fa-spin" style="font-size: 32px; color: var(--primary);"></i>
                        <p style="margin-top: 10px;">Enregistrement en cours...</p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- MODAL MODIFICATION OPÉRATION -->
<div id="modal-modifier-operation" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header modern-modal-header">
                <h5 class="modal-title"><i class="fa fa-pencil"></i> Modifier l'opération</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body modern-modal-body">
                <form id="form-modifier-operation" enctype="multipart/form-data" autocomplete="off">
                    <input type="hidden" name="operation_id" id="modifier-operation-id">
                    <input type="hidden" name="agence_id" value="<?php if ($userAgenceId) echo $userAgenceId; ?>">
                    <input type="hidden" name="updated_by" value="<?php if (isset($user['idUser'])) { echo $user['idUser']; } elseif (isset($user['id'])) { echo $user['id']; } ?>">
                    
                    <div class="alert alert-info" style="background:var(--info-light); border-left:4px solid var(--info); padding:15px; border-radius:8px; margin-bottom:20px;">
                        <i class="fa fa-info-circle" style="color:var(--info);"></i> 
                        Tous les champs avec <span class="required">*</span> sont obligatoires
                    </div>
                    
                    <div class="alert alert-warning" style="background:var(--warning-light); border-left:4px solid var(--warning); padding:15px; border-radius:8px; margin-bottom:20px;">
                        <i class="fa fa-exclamation-triangle" style="color:var(--warning);"></i> 
                        <strong>Attention :</strong> Les numéros de chèques et références de virement doivent être uniques dans votre agence.
                    </div>
                    
                    <!-- Affichage du type d'opération (lecture seule) -->
                    <div class="form-group">
                        <label class="modern-form-label"><i class="fa fa-tag"></i> Type d'opération</label>
                        <div class="form-control-static" id="modifier-type-operation-display" style="padding:12px 15px; background:var(--gray-light); border-radius:8px;">
                            <!-- Rempli dynamiquement -->
                        </div>
                        <input type="hidden" id="modifier-input-type-operation" name="type_operation">
                    </div>
                    
                    <div class="form-group">
                        <label class="modern-form-label"><i class="fa fa-user"></i> Nom Client <span class="required">*</span></label>
                        <input type="text" class="modern-form-control" id="modifier-input-nom-client" name="nom_client" placeholder="Nom du client" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="modern-form-label" id="modifier-label-numero"><i class="fa fa-hashtag"></i> Numéro Chèque <span class="required">*</span></label>
                        <input type="text" class="modern-form-control" id="modifier-input-numero" name="numero_cheque" placeholder="Numéro du chèque" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="modern-form-label"><i class="fa fa-money"></i> Montant (FCFA) <span class="required">*</span></label>
                        <input type="number" class="modern-form-control" id="modifier-input-montant" name="montant" placeholder="Montant" step="0.01" min="1" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="modern-form-label"><i class="fa fa-bank"></i> Banque <span class="required">*</span></label>
                        <input type="text" class="modern-form-control" id="modifier-input-banque" name="banque" placeholder="Nom de la banque" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="modern-form-label"><i class="fa fa-calendar"></i> Date de réception <span class="required">*</span></label>
                        <div class="input-group" style="display:flex;">
                            <input type="text" class="modern-form-control datepicker-operation-modifier" id="modifier-input-date-reception" name="date_reception" placeholder="JJ/MM/AAAA" required style="border-radius:8px 0 0 8px; margin-bottom:0;">
                            <span class="btn-calendrier-modifier" style="background:var(--gray-light); border:1px solid #dee2e6; border-left:none; border-radius:0 8px 8px 0; padding:0 15px; display:flex; align-items:center; cursor:pointer;">
                                <i class="fa fa-calendar" style="color:var(--primary);"></i>
                            </span>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="modern-form-label" id="modifier-label-scan"><i class="fa fa-file-image-o"></i> Scan du Chèque</label>
                        <input type="file" class="modern-form-control" id="modifier-input-scan" name="scan_operation" 
                               accept=".jpg,.jpeg,.png,.pdf" style="padding:8px 15px;">
                        <small class="text-muted" id="modifier-scan-description">Formats acceptés: JPG, PNG, PDF (max 5MB) - Facultatif</small>
                        <div id="modifier-preview-scan" class="file-preview"></div>
                        <div id="modifier-scan-actuel" class="file-preview"></div>
                    </div>
                    
                    <div class="form-group">
                        <label class="modern-form-label"><i class="fa fa-comment"></i> Observations</label>
                        <textarea class="modern-form-control" id="modifier-input-observations" name="observations" placeholder="Observations supplémentaires" rows="2"></textarea>
                    </div>
                    
                    <div class="text-right" style="margin-top:20px;">
                        <button type="button" class="btn-modern btn-modern-secondary" data-dismiss="modal">
                            <i class="fa fa-times"></i> Annuler
                        </button>
                        <button type="button" id="btn-modifier-operation" class="btn-modern btn-modern-primary">
                            <i class="fa fa-save"></i> Modifier
                        </button>
                    </div>
                    
                    <div class="modern-loader loaderModifier" style="display:none;">
                        <i class="fa fa-spinner fa-spin"></i>
                        <p>Modification en cours...</p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- MODAL CHANGEMENT STATUT -->
<div id="modal-changement-statut" class="modal fade">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header modern-modal-header">
                <h5 class="modal-title"><i class="fa fa-exchange"></i> Changer le statut</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body modern-modal-body">
                <form id="form-changement-statut" autocomplete="off">
                    <input type="hidden" name="operation_id" id="statut-operation-id">
                    
                    <div style="text-align:center; padding:10px;">
                        <i class="fa fa-question-circle" style="font-size:48px; color:var(--warning); margin-bottom:15px;"></i>
                        <h4 style="margin-bottom:20px;">Changer le statut de l'opération</h4>
                    </div>
                    
                    <div class="form-group">
                        <label class="modern-form-label"><i class="fa fa-tag"></i> Nouveau statut <span class="required">*</span></label>
                        <div style="display:grid; grid-template-columns:repeat(2,1fr); gap:15px; margin-bottom:20px;">
                            <div class="statut-option" data-value="confirmé">
                                <i class="fa fa-check-circle" style="color:var(--success);"></i>
                                <div style="font-weight:600; font-size:13px;">Confirmé</div>
                            </div>
                            <div class="statut-option" data-value="annulé">
                                <i class="fa fa-times-circle" style="color:var(--danger);"></i>
                                <div style="font-weight:600; font-size:13px;">Annulé</div>
                            </div>
                        </div>
                        <input type="hidden" id="input-nouveau-statut" name="nouveau_statut">
                    </div>
                    
                    <div class="form-group">
                        <label class="modern-form-label"><i class="fa fa-comment"></i> Observation <span class="required">*</span></label>
                        <textarea class="modern-form-control" id="input-observation-statut" name="observation" 
                                  placeholder="Raison du changement de statut..." rows="3" required></textarea>
                    </div>
                    
                    <div class="text-right" style="margin-top:20px;">
                        <button type="button" class="btn-modern btn-modern-secondary" data-dismiss="modal">
                            <i class="fa fa-times"></i> Annuler
                        </button>
                        <button type="button" id="btn-enregistrer-statut" class="btn-modern btn-modern-primary">
                            <i class="fa fa-check"></i> Confirmer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- MODAL CHANGEMENT ÉTAT CONFIRMATION -->
<div id="modal-changement-etat-confirmation" class="modal fade">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header modern-modal-header">
                <h5 class="modal-title"><i class="fa fa-check-circle"></i> Changer l'état de confirmation</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body modern-modal-body">
                <form id="form-changement-etat-confirmation" autocomplete="off">
                    <input type="hidden" name="operation_id" id="etat-confirmation-operation-id">
                    
                    <div style="text-align:center; padding:10px;">
                        <i class="fa fa-question-circle" style="font-size:48px; color:var(--warning); margin-bottom:15px;"></i>
                        <h4 style="margin-bottom:20px;">Changer l'état de confirmation</h4>
                    </div>
                    
                    <div class="form-group">
                        <label class="modern-form-label"><i class="fa fa-tag"></i> Nouvel état <span class="required">*</span></label>
                        <div style="display:grid; grid-template-columns:repeat(2,1fr); gap:15px; margin-bottom:20px;">
                            <div class="statut-option" data-value="Oui">
                                <i class="fa fa-check-circle" style="color:var(--success);"></i>
                                <div style="font-weight:600; font-size:13px;">Oui</div>
                            </div>
                            <div class="statut-option" data-value="Non">
                                <i class="fa fa-times-circle" style="color:var(--danger);"></i>
                                <div style="font-weight:600; font-size:13px;">Non</div>
                            </div>
                        </div>
                        <input type="hidden" id="input-nouvel-etat-confirmation" name="nouvel_etat_confirmation">
                    </div>
                    
                    <div class="form-group">
                        <label class="modern-form-label"><i class="fa fa-comment"></i> Observation <span class="required">*</span></label>
                        <textarea class="modern-form-control" id="input-observation-etat-conf" name="observation" 
                                  placeholder="Raison du changement d'état..." rows="3" required></textarea>
                    </div>
                    
                    <div class="text-right" style="margin-top:20px;">
                        <button type="button" class="btn-modern btn-modern-secondary" data-dismiss="modal">
                            <i class="fa fa-times"></i> Annuler
                        </button>
                        <button type="button" id="btn-enregistrer-etat-confirmation" class="btn-modern btn-modern-primary">
                            <i class="fa fa-check"></i> Confirmer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- MODAL CHANGEMENT ÉTAT VALIDATION -->
<div id="modal-changement-etat" class="modal fade">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header modern-modal-header">
                <h5 class="modal-title"><i class="fa fa-check-square-o"></i> Changer l'état de validation</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body modern-modal-body">
                <form id="form-changement-etat" autocomplete="off">
                    <input type="hidden" name="operation_id" id="etat-operation-id">
                    
                    <div style="text-align:center; padding:10px;">
                        <i class="fa fa-question-circle" style="font-size:48px; color:var(--warning); margin-bottom:15px;"></i>
                        <h4 style="margin-bottom:20px;">Changer l'état de validation</h4>
                    </div>
                    
                    <div class="form-group">
                        <label class="modern-form-label"><i class="fa fa-tag"></i> Nouvel état <span class="required">*</span></label>
                        <div style="display:grid; grid-template-columns:repeat(2,1fr); gap:15px; margin-bottom:20px;">
                            <div class="statut-option" data-value="Oui">
                                <i class="fa fa-check-circle" style="color:var(--success);"></i>
                                <div style="font-weight:600; font-size:13px;">Oui</div>
                            </div>
                            <div class="statut-option" data-value="Non">
                                <i class="fa fa-times-circle" style="color:var(--danger);"></i>
                                <div style="font-weight:600; font-size:13px;">Non</div>
                            </div>
                        </div>
                        <input type="hidden" id="input-nouvel-etat" name="nouvel_etat_validation">
                    </div>
                    
                    <div class="form-group">
                        <label class="modern-form-label"><i class="fa fa-comment"></i> Observation <span class="required">*</span></label>
                        <textarea class="modern-form-control" id="input-observation-etat" name="observation" 
                                  placeholder="Raison du changement d'état..." rows="3" required></textarea>
                    </div>
                    
                    <div class="text-right" style="margin-top:20px;">
                        <button type="button" class="btn-modern btn-modern-secondary" data-dismiss="modal">
                            <i class="fa fa-times"></i> Annuler
                        </button>
                        <button type="button" id="btn-enregistrer-etat" class="btn-modern btn-modern-primary">
                            <i class="fa fa-check"></i> Confirmer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- MODAL DÉTAILS - RÉDUIT -->
<div id="modal-details-operation" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header modern-modal-header">
                <h5 class="modal-title"><i class="fa fa-info-circle"></i> Détails de l'opération</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body modern-modal-body">
                <div id="details-loader" class="modern-loader" style="display:none;">
                    <i class="fa fa-spinner fa-spin"></i>
                    <p>Chargement des détails...</p>
                </div>
                <div id="details-content"></div>
            </div>
            <div class="modal-footer modern-modal-footer">
                <button class="btn-modern btn-modern-secondary" data-dismiss="modal">
                    <i class="fa fa-times"></i> Fermer
                </button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL GÉNÉRATION CARTE -->
<div id="modal-generer-carte" class="modal fade">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header modern-modal-header">
                <h5 class="modal-title"><i class="fa fa-id-card"></i> Générer une carte de suivi</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body modern-modal-body">
                <form id="form-generer-carte" autocomplete="off">
                    <input type="hidden" name="operation_id" id="carte-operation-id">
                    
                    <div style="text-align:center; padding:10px;">
                        <i class="fa fa-id-card" style="font-size:48px; color:var(--primary); margin-bottom:15px;"></i>
                        <h4 style="margin-bottom:20px;">Générer une carte téléchargeable</h4>
                        <p style="color:var(--gray); margin-bottom:20px;">
                            La carte sera générée au format PNG (600x400px) et contiendra toutes les informations de l'opération.
                        </p>
                    </div>
                    
                    <div class="form-group">
                        <label class="modern-form-label"><i class="fa fa-header"></i> Titre de la carte <span class="required">*</span></label>
                        <input type="text" class="modern-form-control" id="input-titre-carte" name="titre_carte" 
                               placeholder="Ex: Carte de suivi Opération #123" required>
                    </div>
                    
                    <div class="text-right" style="margin-top:20px;">
                        <button type="button" class="btn-modern btn-modern-secondary" data-dismiss="modal">
                            <i class="fa fa-times"></i> Annuler
                        </button>
                        <button type="button" id="btn-generer-carte" class="btn-modern btn-modern-success">
                            <i class="fa fa-download"></i> Générer la carte
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// ============================================
// VARIABLES GLOBALES
// ============================================
var currentPage = <?php echo $page; ?>;
var currentSearch = <?php echo json_encode($search); ?>;
var currentType = <?php echo json_encode($type); ?>;
var isAdminOrCompta = <?php if ($isAdminOrCompta) { echo 'true'; } else { echo 'false'; } ?>;
var isCompta = <?php if ($isCompta) { echo 'true'; } else { echo 'false'; } ?>;
var userAgenceId = <?php if ($userAgenceId) { echo $userAgenceId; } else { echo 'null'; } ?>;
var userAgenceNom = <?php echo json_encode($userAgenceNom); ?>;
var userSite = <?php echo json_encode($userSite); ?>;
var itemsPerPage = 10;

// ============================================
// FONCTIONS UTILITAIRES
// ============================================
function escapeHtml(text) {
    var div = document.createElement('div');
    div.textContent = text || '';
    return div.innerHTML;
}

function notif(msg, type) {
    $('.modern-notification').remove();
    
    var notification = $('<div class="modern-notification"></div>');
    var icon = '';
    
    switch(type) {
        case 'success':
            icon = 'fa fa-check-circle';
            notification.addClass('success');
            break;
        case 'error':
            icon = 'fa fa-exclamation-circle';
            notification.addClass('error');
            break;
        case 'warning':
            icon = 'fa fa-exclamation-triangle';
            notification.addClass('warning');
            break;
        default:
            icon = 'fa fa-info-circle';
            notification.addClass('info');
    }
    
    notification.html('<i class="' + icon + ' mr-2"></i> ' + msg);
    $('body').append(notification);
    
    setTimeout(function(){ notification.addClass('show'); }, 10);
    setTimeout(function(){ 
        notification.removeClass('show'); 
        setTimeout(function(){ notification.remove(); }, 300); 
    }, 4000);
    
    notification.click(function(){ 
        notification.removeClass('show'); 
        setTimeout(function(){ notification.remove(); }, 300); 
    });
}

// ============================================
// GESTION DU TYPE D'OPÉRATION
// ============================================
function updateFormLabels(type) {
    var labelNumero = document.getElementById('label-numero');
    var inputNumero = document.getElementById('input-numero');
    var labelScan = document.getElementById('label-scan');
    var scanDescription = document.getElementById('scan-description');
    
    if (type === 'cheque') {
        labelNumero.innerHTML = '<i class="fa fa-hashtag"></i> Numéro Chèque <span class="required">*</span>';
        inputNumero.placeholder = 'Numéro du chèque';
        labelScan.innerHTML = '<i class="fa fa-file-image-o"></i> Scan du Chèque <span class="required">*</span>';
        scanDescription.innerHTML = 'Formats acceptés: JPG, PNG, PDF (max 5MB) - OBLIGATOIRE';
    } else {
        labelNumero.innerHTML = '<i class="fa fa-hashtag"></i> Référence Virement <span class="required">*</span>';
        inputNumero.placeholder = 'Référence du virement';
        labelScan.innerHTML = '<i class="fa fa-file-image-o"></i> Justificatif Virement <span class="required">*</span>';
        scanDescription.innerHTML = 'Formats acceptés: JPG, PNG, PDF (max 5MB) - OBLIGATOIRE';
    }
}

function updateModifierFormLabels(type) {
    var labelNumero = document.getElementById('modifier-label-numero');
    var labelScan = document.getElementById('modifier-label-scan');
    var scanDescription = document.getElementById('modifier-scan-description');
    
    if (type === 'cheque') {
        labelNumero.innerHTML = '<i class="fa fa-hashtag"></i> Numéro Chèque <span class="required">*</span>';
        labelScan.innerHTML = '<i class="fa fa-file-image-o"></i> Scan du Chèque';
        scanDescription.innerHTML = 'Formats acceptés: JPG, PNG, PDF (max 5MB) - Facultatif';
    } else {
        labelNumero.innerHTML = '<i class="fa fa-hashtag"></i> Référence Virement <span class="required">*</span>';
        labelScan.innerHTML = '<i class="fa fa-file-image-o"></i> Justificatif Virement';
        scanDescription.innerHTML = 'Formats acceptés: JPG, PNG, PDF (max 5MB) - Facultatif';
    }
}

// ============================================
// FILTRES ET RECHERCHE
// ============================================
function filterByType() {
    var typeSelect = document.getElementById('filter-type');
    var selectedType = typeSelect.value;
    
    var url = '?p=confirmationCheque&page=1';
    var searchInput = document.getElementById('search-operations');
    if (searchInput && searchInput.value.trim()) {
        url += '&search=' + encodeURIComponent(searchInput.value.trim());
    }
    if (selectedType) {
        url += '&type=' + selectedType;
    }
    
    window.location.href = url;
}

function effectuerRecherche() {
    var searchInput = document.getElementById('search-operations');
    var searchTerm = searchInput.value.trim();
    var typeSelect = document.getElementById('filter-type');
    var selectedType = typeSelect.value;
    
    var url = '?p=confirmationCheque&page=1';
    if (searchTerm !== '') {
        url += '&search=' + encodeURIComponent(searchTerm);
    }
    if (selectedType !== '') {
        url += '&type=' + selectedType;
    }
    
    window.location.href = url;
}

function effacerRecherche() {
    window.location.href = '?p=confirmationCheque&page=1';
}

// ============================================
// MODIFICATION D'OPÉRATION
// ============================================
function ouvrirModalModifier(operationId) {
    console.log('📋 Ouverture modal modification pour opération:', operationId);
    
    var form = document.getElementById('form-modifier-operation');
    if (form) {
        var erreurs = form.querySelectorAll('.is-invalid');
        for (var i = 0; i < erreurs.length; i++) {
            erreurs[i].classList.remove('is-invalid');
        }
    }
    
    var preview = document.getElementById('modifier-preview-scan');
    if (preview) preview.innerHTML = '';
    
    var scanActuel = document.getElementById('modifier-scan-actuel');
    if (scanActuel) scanActuel.innerHTML = '';
    
    var loader = document.querySelector('.loaderModifier');
    if (loader) loader.style.display = 'block';
    
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'index.php?p=confirmationCheque.getOperationData');
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    
    xhr.onload = function() {
        if (loader) loader.style.display = 'none';
        
        if (this.status === 200) {
            try {
                var response = JSON.parse(this.responseText);
                
                if (response.success && response.operation) {
                    var operation = response.operation;
                    
                    if (!response.canBeModified) {
                        notif('Cette opération ne peut plus être modifiée', 'error');
                        return;
                    }
                    
                    if (!isAdminOrCompta && !response.isCreator) {
                        notif('Vous ne pouvez modifier que les opérations que vous avez créées', 'error');
                        return;
                    }
                    
                    document.getElementById('modifier-operation-id').value = operationId;
                    
                    var typeDisplay = document.getElementById('modifier-type-operation-display');
                    var typeInput = document.getElementById('modifier-input-type-operation');
                    if (typeDisplay && typeInput) {
                        if (operation.type_operation === 'cheque') {
                            typeDisplay.innerHTML = '<i class="fa fa-money" style="color:var(--success);"></i> Chèque';
                            typeInput.value = 'cheque';
                        } else {
                            typeDisplay.innerHTML = '<i class="fa fa-exchange" style="color:var(--info);"></i> Virement';
                            typeInput.value = 'virement';
                        }
                        updateModifierFormLabels(operation.type_operation);
                    }
                    
                    document.getElementById('modifier-input-nom-client').value = operation.nom_client || '';
                    document.getElementById('modifier-input-numero').value = operation.numero_cheque || '';
                    document.getElementById('modifier-input-montant').value = operation.montant || '';
                    document.getElementById('modifier-input-banque').value = operation.banque || '';
                    
                    var dateReception = '';
                    if (operation.date_reception) {
                        if (operation.date_reception instanceof Object && operation.date_reception.date) {
                            var dateObj = new Date(operation.date_reception.date);
                            var jour = ('0' + dateObj.getDate()).slice(-2);
                            var mois = ('0' + (dateObj.getMonth() + 1)).slice(-2);
                            var annee = dateObj.getFullYear();
                            dateReception = jour + '/' + mois + '/' + annee;
                        } else {
                            var dateStr = operation.date_reception.toString();
                            if (dateStr.match(/^\d{4}-\d{2}-\d{2}/)) {
                                var parts = dateStr.split('-');
                                dateReception = parts[2] + '/' + parts[1] + '/' + parts[0];
                            } else if (dateStr.match(/^\d{2}\/\d{2}\/\d{4}/)) {
                                dateReception = dateStr;
                            }
                        }
                    }
                    document.getElementById('modifier-input-date-reception').value = dateReception;
                    
                    document.getElementById('modifier-input-observations').value = operation.observations || '';
                    
                    if (operation.scan_path) {
                        var scanActuel = document.getElementById('modifier-scan-actuel');
                        if (scanActuel) {
                            var fileName = operation.scan_path.split('/').pop();
                            scanActuel.innerHTML = 
                                '<div style="background:var(--info-light); padding:10px; border-radius:8px;">' +
                                '<i class="fa fa-file" style="color:var(--info);"></i> Justificatif actuel: ' + 
                                '<a href="' + escapeHtml(operation.scan_path) + '" target="_blank" style="color:var(--info); text-decoration:none;">' +
                                escapeHtml(fileName) +
                                '</a><br>' +
                                '<small class="text-muted">Laissez vide pour conserver ce justificatif</small>' +
                                '</div>';
                        }
                    }
                    
                    if (typeof jQuery !== 'undefined' && jQuery.fn.modal) {
                        jQuery('#modal-modifier-operation').modal('show');
                    }
                    
                } else {
                    notif(response.message || 'Erreur lors du chargement des données', 'error');
                }
            } catch (e) {
                console.error('❌ Parse JSON:', e);
                notif('Réponse invalide du serveur', 'error');
            }
        } else {
            notif('Erreur HTTP ' + this.status, 'error');
        }
    };
    
    xhr.onerror = function() {
        if (loader) loader.style.display = 'none';
        notif('Erreur réseau lors du chargement', 'error');
    };
    
    xhr.send('operation_id=' + encodeURIComponent(operationId));
}

function validerFormulaireModification() {
    var valide = true;
    
    var champs = [
        { id: 'modifier-input-nom-client', nom: 'Nom client' },
        { id: 'modifier-input-numero', nom: 'Numéro/Référence' },
        { id: 'modifier-input-montant', nom: 'Montant' },
        { id: 'modifier-input-banque', nom: 'Banque' },
        { id: 'modifier-input-date-reception', nom: 'Date de réception' }
    ];
    
    for (var i = 0; i < champs.length; i++) {
        var champ = document.getElementById(champs[i].id);
        if (!champ || !champ.value.trim()) {
            if (champ) {
                champ.classList.add('is-invalid');
                champ.focus();
            }
            notif('Le champ "' + champs[i].nom + '" est obligatoire', 'warning');
            valide = false;
            break;
        }
    }
    
    if (!valide) return false;
    
    var montant = parseFloat(document.getElementById('modifier-input-montant').value);
    if (isNaN(montant) || montant <= 0) {
        document.getElementById('modifier-input-montant').classList.add('is-invalid');
        document.getElementById('modifier-input-montant').focus();
        notif('Le montant doit être un nombre positif', 'warning');
        return false;
    }
    
    var dateInput = document.getElementById('modifier-input-date-reception');
    var regex = /^(\d{2})\/(\d{2})\/(\d{4})$/;
    if (!regex.test(dateInput.value)) {
        dateInput.classList.add('is-invalid');
        dateInput.focus();
        notif('Le format de la date doit être JJ/MM/AAAA', 'warning');
        return false;
    }
    
    var scanInput = document.getElementById('modifier-input-scan');
    if (scanInput && scanInput.files && scanInput.files.length > 0) {
        var file = scanInput.files[0];
        if (file.size > 5 * 1024 * 1024) {
            scanInput.classList.add('is-invalid');
            scanInput.focus();
            notif('Le fichier est trop volumineux (max 5MB)', 'warning');
            return false;
        }
    }
    
    return true;
}

function envoyerModification() {
    console.log('🔄 Envoi modification...');
    
    if (!validerFormulaireModification()) return;
    
    var form = document.getElementById('form-modifier-operation');
    if (!form) return;
    
    var formData = new FormData(form);
    
    var dateInput = document.getElementById('modifier-input-date-reception');
    if (dateInput && dateInput.value) {
        var parts = dateInput.value.split('/');
        if (parts.length === 3) {
            var dateMySQL = parts[2] + '-' + parts[1] + '-' + parts[0];
            formData.set('date_reception', dateMySQL);
        }
    }
    
    var btnSubmit = document.getElementById('btn-modifier-operation');
    var texteOriginal = btnSubmit.innerHTML;
    btnSubmit.disabled = true;
    btnSubmit.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Modification...';
    
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'index.php?p=confirmationCheque.updateOperation');
    
    xhr.onload = function() {
        btnSubmit.disabled = false;
        btnSubmit.innerHTML = texteOriginal;
        
        if (this.status === 200) {
            try {
                var response = JSON.parse(this.responseText);
                
                if (response.success) {
                    if (typeof jQuery !== 'undefined' && jQuery.fn.modal) {
                        jQuery('#modal-modifier-operation').modal('hide');
                    }
                    
                    notif(response.message, 'success');
                    
                    setTimeout(function() {
                        var url = '?p=confirmationCheque&page=' + currentPage;
                        if (currentSearch !== '') {
                            url += '&search=' + encodeURIComponent(currentSearch);
                        }
                        if (currentType !== '') {
                            url += '&type=' + encodeURIComponent(currentType);
                        }
                        window.location.href = url;
                    }, 1500);
                    
                } else {
                    notif(response.message, 'error');
                }
            } catch (e) {
                console.error('❌ Parse JSON:', e);
                notif('Réponse invalide du serveur', 'error');
            }
        } else {
            notif('Erreur HTTP ' + this.status, 'error');
        }
    };
    
    xhr.onerror = function() {
        btnSubmit.disabled = false;
        btnSubmit.innerHTML = texteOriginal;
        notif('Erreur réseau lors de l\'envoi', 'error');
    };
    
    xhr.send(formData);
}

// ============================================
// AJOUT D'OPÉRATION
// ============================================
function ouvrirModalAjout() {
    console.log('📋 Ouverture modal ajout');
    
    var form = document.getElementById('form-ajout-operation');
    if (form) {
        var erreurs = form.querySelectorAll('.is-invalid');
        for (var i = 0; i < erreurs.length; i++) {
            erreurs[i].classList.remove('is-invalid');
        }
    }
    
    var aujourdhui = new Date();
    var dateInput = document.getElementById('input-date-reception');
    if (dateInput) {
        var jour = ('0' + aujourdhui.getDate()).slice(-2);
        var mois = ('0' + (aujourdhui.getMonth() + 1)).slice(-2);
        var annee = aujourdhui.getFullYear();
        dateInput.value = jour + '/' + mois + '/' + annee;
    }
    
    var preview = document.getElementById('preview-scan');
    if (preview) preview.innerHTML = '';
    
    var radioCheque = document.getElementById('radio-cheque');
    if (radioCheque) radioCheque.checked = true;
    updateFormLabels('cheque');
    
    // Réinitialiser le type de client
    $('#radio-client-manuel').prop('checked', true);
    $('#section-client-manuel').show();
    $('#section-client-autorise').hide();
    $('#infos-client-autorise').hide();
    $('#input-nom-client-manuel').prop('required', true);
    $('#select-client-autorise').prop('required', false);
    $('#input-nom-client').val('');
    
    if (typeof jQuery !== 'undefined' && jQuery.fn.modal) {
        jQuery('#modal-ajout-operation').modal('show');
    }
}

// ============================================
// GESTION DES CLIENTS AUTORISÉS DANS LE MODAL AJOUT
// ============================================
// Gestion du changement de type de client
$('input[name="type_client"]').change(function() {
    if ($(this).val() === 'manuel') {
        $('#section-client-manuel').show();
        $('#section-client-autorise').hide();
        $('#input-nom-client-manuel').prop('required', true);
        $('#select-client-autorise').prop('required', false);
        $('#input-nom-client').val(''); // Vider le champ caché
    } else {
        $('#section-client-manuel').hide();
        $('#section-client-autorise').show();
        $('#input-nom-client-manuel').prop('required', false);
        $('#select-client-autorise').prop('required', true);
    }
});

// Affichage des informations du client sélectionné
$('#select-client-autorise').change(function() {
    var selectedOption = $(this).find('option:selected');
    var nomClient = selectedOption.val();
    var site = selectedOption.data('site');
    var contact = selectedOption.data('contact');
    var plafond = selectedOption.data('plafond');
    
    if (nomClient) {
        // Mettre à jour le champ caché avec le nom du client
        $('#input-nom-client').val(nomClient);
        
        // Afficher les informations supplémentaires
        $('#info-site').text(site || 'Non spécifié');
        $('#info-contact').text(contact || 'Non spécifié');
        $('#info-plafond').text(plafond ? plafond.toLocaleString('fr-FR') : '0');
        $('#infos-client-autorise').show();
        
        // Optionnel : vérifier le plafond par rapport au montant saisi
        verifierPlafond();
    } else {
        $('#input-nom-client').val('');
        $('#infos-client-autorise').hide();
    }
});

// Vérification du plafond lors de la saisie du montant
function verifierPlafond() {
    var montant = parseFloat($('#input-montant').val()) || 0;
    var selectedOption = $('#select-client-autorise').find('option:selected');
    var plafond = selectedOption.data('plafond') || 0;
    var nomClient = selectedOption.val();
    
    if (nomClient && montant > 0 && plafond > 0 && montant > plafond) {
        $('#info-plafond').css('color', 'var(--danger)');
        if (!$('#alerte-plafond').length) {
            $('#infos-client-autorise').after(
                '<div id="alerte-plafond" class="alert alert-warning" style="margin-top: 10px; padding: 8px; font-size: 12px;">' +
                '<i class="fa fa-exclamation-triangle"></i> Attention : Le montant dépasse le plafond autorisé pour ce client !' +
                '</div>'
            );
        }
    } else {
        $('#info-plafond').css('color', '');
        $('#alerte-plafond').remove();
    }
}

// Vérifier le plafond quand le montant change
$('#input-montant').on('input', function() {
    verifierPlafond();
});

// Réinitialiser le formulaire quand le modal est fermé
$('#modal-ajout-operation').on('hidden.bs.modal', function() {
    $('#form-ajout-operation')[0].reset();
    $('#section-client-manuel').show();
    $('#section-client-autorise').hide();
    $('#infos-client-autorise').hide();
    $('#input-nom-client-manuel').prop('required', true);
    $('#select-client-autorise').prop('required', false);
    $('#preview-scan').empty();
    $('#alerte-plafond').remove();
    $('.is-invalid').removeClass('is-invalid');
});

// Modifier la fonction validerFormulaireAjout pour prendre en compte le nouveau champ
function validerFormulaireAjout() {
    var typeClient = $('input[name="type_client"]:checked').val();
    
    if (typeClient === 'manuel') {
        var nomClient = $('#input-nom-client-manuel').val().trim();
        if (!nomClient) {
            $('#input-nom-client-manuel').addClass('is-invalid').focus();
            notif('Veuillez saisir un nom client', 'warning');
            return false;
        }
        $('#input-nom-client').val(nomClient);
    } else {
        var clientId = $('#select-client-autorise').val();
        if (!clientId) {
            $('#select-client-autorise').addClass('is-invalid').focus();
            notif('Veuillez sélectionner un client autorisé', 'warning');
            return false;
        }
    }
    
    var valide = true;
    
    var champs = [
        { id: 'input-numero', nom: 'Numéro/Référence' },
        { id: 'input-montant', nom: 'Montant' },
        { id: 'input-banque', nom: 'Banque' },
        { id: 'input-date-reception', nom: 'Date de réception' }
    ];
    
    for (var i = 0; i < champs.length; i++) {
        var champ = document.getElementById(champs[i].id);
        if (!champ || !champ.value.trim()) {
            if (champ) {
                champ.classList.add('is-invalid');
                champ.focus();
            }
            notif('Le champ "' + champs[i].nom + '" est obligatoire', 'warning');
            valide = false;
            break;
        }
    }
    
    if (!valide) return false;
    
    var typeCheque = document.getElementById('radio-cheque');
    var typeVirement = document.getElementById('radio-virement');
    if (!typeCheque.checked && !typeVirement.checked) {
        notif('Veuillez sélectionner un type d\'opération', 'warning');
        return false;
    }
    
    var montant = parseFloat(document.getElementById('input-montant').value);
    if (isNaN(montant) || montant <= 0) {
        document.getElementById('input-montant').classList.add('is-invalid');
        document.getElementById('input-montant').focus();
        notif('Le montant doit être un nombre positif', 'warning');
        return false;
    }
    
    // Vérification du plafond pour les clients autorisés
    if (typeClient === 'autorise') {
        var plafond = $('#select-client-autorise').find('option:selected').data('plafond') || 0;
        if (plafond > 0 && montant > plafond) {
            if (!confirm('Le montant saisi (' + montant.toLocaleString('fr-FR') + ' FCFA) dépasse le plafond autorisé pour ce client (' + plafond.toLocaleString('fr-FR') + ' FCFA). Voulez-vous continuer ?')) {
                return false;
            }
        }
    }
    
    var dateInput = document.getElementById('input-date-reception');
    var regex = /^(\d{2})\/(\d{2})\/(\d{4})$/;
    if (!regex.test(dateInput.value)) {
        dateInput.classList.add('is-invalid');
        dateInput.focus();
        notif('Le format de la date doit être JJ/MM/AAAA', 'warning');
        return false;
    }
    
    var scanInput = document.getElementById('input-scan');
    if (!scanInput || !scanInput.files || scanInput.files.length === 0) {
        scanInput.classList.add('is-invalid');
        scanInput.focus();
        notif('Le scan du justificatif est obligatoire', 'warning');
        return false;
    }
    
    var file = scanInput.files[0];
    if (file.size > 5 * 1024 * 1024) {
        scanInput.classList.add('is-invalid');
        scanInput.focus();
        notif('Le fichier est trop volumineux (max 5MB)', 'warning');
        return false;
    }
    
    return true;
}

function envoyerAjout() {
    console.log('🔄 Envoi ajout...');
    
    if (!validerFormulaireAjout()) return;
    
    var form = document.getElementById('form-ajout-operation');
    if (!form) return;
    
    var formData = new FormData(form);
    
    var dateInput = document.getElementById('input-date-reception');
    if (dateInput && dateInput.value) {
        var parts = dateInput.value.split('/');
        if (parts.length === 3) {
            var dateMySQL = parts[2] + '-' + parts[1] + '-' + parts[0];
            formData.set('date_reception', dateMySQL);
        }
    }
    
    var btnSubmit = document.getElementById('btn-enregistrer-operation');
    var texteOriginal = btnSubmit.innerHTML;
    btnSubmit.disabled = true;
    btnSubmit.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Envoi...';
    
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'index.php?p=confirmationCheque.ajoutOperation');
    
    xhr.onload = function() {
        btnSubmit.disabled = false;
        btnSubmit.innerHTML = texteOriginal;
        
        if (this.status === 200) {
            try {
                var response = JSON.parse(this.responseText);
                
                if (response.success) {
                    if (typeof jQuery !== 'undefined' && jQuery.fn.modal) {
                        jQuery('#modal-ajout-operation').modal('hide');
                    }
                    
                    notif(response.message, 'success');
                    
                    setTimeout(function() {
                        var url = '?p=confirmationCheque&page=1';
                        if (currentSearch !== '') {
                            url += '&search=' + encodeURIComponent(currentSearch);
                        }
                        if (currentType !== '') {
                            url += '&type=' + encodeURIComponent(currentType);
                        }
                        window.location.href = url;
                    }, 1500);
                    
                } else {
                    notif(response.message, 'error');
                }
            } catch (e) {
                console.error('❌ Parse JSON:', e);
                notif('Réponse invalide du serveur', 'error');
            }
        } else {
            notif('Erreur HTTP ' + this.status, 'error');
        }
    };
    
    xhr.onerror = function() {
        btnSubmit.disabled = false;
        btnSubmit.innerHTML = texteOriginal;
        notif('Erreur réseau lors de l\'envoi', 'error');
    };
    
    xhr.send(formData);
}

// ============================================
// CHANGEMENT STATUT
// ============================================
function ouvrirModalChangerStatut(operationId) {
    if (!isCompta) {
        notif('Seule la comptabilité peut changer le statut', 'error');
        return;
    }
    console.log('📋 Ouverture modal statut pour opération:', operationId);
    
    document.getElementById('statut-operation-id').value = operationId;
    document.getElementById('input-nouveau-statut').value = '';
    document.getElementById('input-observation-statut').value = '';
    
    $('.statut-option').removeClass('active');
    
    if (typeof jQuery !== 'undefined' && jQuery.fn.modal) {
        jQuery('#modal-changement-statut').modal('show');
    }
}

function validerFormulaireStatut() {
    var statut = document.getElementById('input-nouveau-statut').value;
    var observation = document.getElementById('input-observation-statut').value;
    
    if (!statut) {
        notif('Veuillez sélectionner un statut', 'warning');
        return false;
    }
    
    if (!observation || observation.trim() === '') {
        notif('L\'observation est obligatoire', 'warning');
        document.getElementById('input-observation-statut').focus();
        return false;
    }
    
    return true;
}

function envoyerChangementStatut() {
    if (!validerFormulaireStatut()) return;
    
    var operationId = document.getElementById('statut-operation-id').value;
    var nouveauStatut = document.getElementById('input-nouveau-statut').value;
    var observation = document.getElementById('input-observation-statut').value;
    
    var btnSubmit = document.getElementById('btn-enregistrer-statut');
    var texteOriginal = btnSubmit.innerHTML;
    btnSubmit.disabled = true;
    btnSubmit.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Envoi...';
    
    var formData = new FormData();
    formData.append('operation_id', operationId);
    formData.append('nouveau_statut', nouveauStatut);
    formData.append('observation', observation);
    
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'index.php?p=confirmationCheque.changerStatut');
    
    xhr.onload = function() {
        btnSubmit.disabled = false;
        btnSubmit.innerHTML = texteOriginal;
        
        if (this.status === 200) {
            try {
                var response = JSON.parse(this.responseText);
                
                if (response.success) {
                    jQuery('#modal-changement-statut').modal('hide');
                    notif(response.message, 'success');
                    setTimeout(function() { window.location.reload(); }, 1500);
                } else {
                    notif(response.message, 'error');
                }
            } catch (e) {
                console.error('❌ Parse JSON:', e);
                notif('Réponse invalide du serveur', 'error');
            }
        } else {
            notif('Erreur HTTP ' + this.status, 'error');
        }
    };
    
    xhr.onerror = function() {
        btnSubmit.disabled = false;
        btnSubmit.innerHTML = texteOriginal;
        notif('Erreur réseau', 'error');
    };
    
    xhr.send(formData);
}

// ============================================
// CHANGEMENT ÉTAT CONFIRMATION
// ============================================
function ouvrirModalChangerEtatConfirmation(operationId) {
    if (!isCompta) {
        notif('Seule la comptabilité peut changer l\'état de confirmation', 'error');
        return;
    }
    console.log('📋 Ouverture modal état confirmation pour opération:', operationId);
    
    document.getElementById('etat-confirmation-operation-id').value = operationId;
    document.getElementById('input-nouvel-etat-confirmation').value = '';
    document.getElementById('input-observation-etat-conf').value = '';
    
    $('.statut-option').removeClass('active');
    
    if (typeof jQuery !== 'undefined' && jQuery.fn.modal) {
        jQuery('#modal-changement-etat-confirmation').modal('show');
    }
}

function validerFormulaireEtatConfirmation() {
    var etat = document.getElementById('input-nouvel-etat-confirmation').value;
    var observation = document.getElementById('input-observation-etat-conf').value;
    
    if (!etat) {
        notif('Veuillez sélectionner un état', 'warning');
        return false;
    }
    
    if (!observation || observation.trim() === '') {
        notif('L\'observation est obligatoire', 'warning');
        document.getElementById('input-observation-etat-conf').focus();
        return false;
    }
    
    return true;
}

function envoyerChangementEtatConfirmation() {
    if (!validerFormulaireEtatConfirmation()) return;
    
    var operationId = document.getElementById('etat-confirmation-operation-id').value;
    var nouvelEtat = document.getElementById('input-nouvel-etat-confirmation').value;
    var observation = document.getElementById('input-observation-etat-conf').value;
    
    var btnSubmit = document.getElementById('btn-enregistrer-etat-confirmation');
    var texteOriginal = btnSubmit.innerHTML;
    btnSubmit.disabled = true;
    btnSubmit.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Envoi...';
    
    var formData = new FormData();
    formData.append('operation_id', operationId);
    formData.append('nouvel_etat_confirmation', nouvelEtat);
    formData.append('observation', observation);
    
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'index.php?p=confirmationCheque.changerEtatConfirmation');
    
    xhr.onload = function() {
        btnSubmit.disabled = false;
        btnSubmit.innerHTML = texteOriginal;
        
        if (this.status === 200) {
            try {
                var response = JSON.parse(this.responseText);
                
                if (response.success) {
                    jQuery('#modal-changement-etat-confirmation').modal('hide');
                    notif(response.message, 'success');
                    setTimeout(function() { window.location.reload(); }, 1500);
                } else {
                    notif(response.message, 'error');
                }
            } catch (e) {
                console.error('❌ Parse JSON:', e);
                notif('Réponse invalide du serveur', 'error');
            }
        } else {
            notif('Erreur HTTP ' + this.status, 'error');
        }
    };
    
    xhr.onerror = function() {
        btnSubmit.disabled = false;
        btnSubmit.innerHTML = texteOriginal;
        notif('Erreur réseau', 'error');
    };
    
    xhr.send(formData);
}

// ============================================
// CHANGEMENT ÉTAT VALIDATION
// ============================================
function ouvrirModalChangerEtat(operationId) {
    if (!isCompta) {
        notif('Seule la comptabilité peut changer l\'état de validation', 'error');
        return;
    }
    console.log('📋 Ouverture modal état validation pour opération:', operationId);
    
    document.getElementById('etat-operation-id').value = operationId;
    document.getElementById('input-nouvel-etat').value = '';
    document.getElementById('input-observation-etat').value = '';
    
    $('.statut-option').removeClass('active');
    
    if (typeof jQuery !== 'undefined' && jQuery.fn.modal) {
        jQuery('#modal-changement-etat').modal('show');
    }
}

function validerFormulaireEtat() {
    var etat = document.getElementById('input-nouvel-etat').value;
    var observation = document.getElementById('input-observation-etat').value;
    
    if (!etat) {
        notif('Veuillez sélectionner un état', 'warning');
        return false;
    }
    
    if (!observation || observation.trim() === '') {
        notif('L\'observation est obligatoire', 'warning');
        document.getElementById('input-observation-etat').focus();
        return false;
    }
    
    return true;
}

function envoyerChangementEtat() {
    if (!validerFormulaireEtat()) return;
    
    var operationId = document.getElementById('etat-operation-id').value;
    var nouvelEtat = document.getElementById('input-nouvel-etat').value;
    var observation = document.getElementById('input-observation-etat').value;
    
    var btnSubmit = document.getElementById('btn-enregistrer-etat');
    var texteOriginal = btnSubmit.innerHTML;
    btnSubmit.disabled = true;
    btnSubmit.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Envoi...';
    
    var formData = new FormData();
    formData.append('operation_id', operationId);
    formData.append('nouvel_etat_validation', nouvelEtat);
    formData.append('observation', observation);
    
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'index.php?p=confirmationCheque.changerEtat');
    
    xhr.onload = function() {
        btnSubmit.disabled = false;
        btnSubmit.innerHTML = texteOriginal;
        
        if (this.status === 200) {
            try {
                var response = JSON.parse(this.responseText);
                
                if (response.success) {
                    jQuery('#modal-changement-etat').modal('hide');
                    notif(response.message, 'success');
                    setTimeout(function() { window.location.reload(); }, 1500);
                } else {
                    notif(response.message, 'error');
                }
            } catch (e) {
                console.error('❌ Parse JSON:', e);
                notif('Réponse invalide du serveur', 'error');
            }
        } else {
            notif('Erreur HTTP ' + this.status, 'error');
        }
    };
    
    xhr.onerror = function() {
        btnSubmit.disabled = false;
        btnSubmit.innerHTML = texteOriginal;
        notif('Erreur réseau', 'error');
    };
    
    xhr.send(formData);
}

// ============================================
// DÉTAILS
// ============================================
function ouvrirModalDetails(operationId) {
    console.log('📋 Ouverture détails pour opération:', operationId);
    
    var loader = document.getElementById('details-loader');
    var content = document.getElementById('details-content');
    
    loader.style.display = 'block';
    content.innerHTML = '';
    
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'index.php?p=confirmationCheque.getDetails');
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    
    xhr.onload = function() {
        loader.style.display = 'none';
        
        if (this.status === 200) {
            try {
                var response = JSON.parse(this.responseText);
                
                if (response.success) {
                    content.innerHTML = response.html;
                } else {
                    content.innerHTML = '<div class="alert alert-danger">' + response.message + '</div>';
                }
            } catch (e) {
                content.innerHTML = '<div class="alert alert-danger">Erreur de chargement</div>';
            }
        } else {
            content.innerHTML = '<div class="alert alert-danger">Erreur HTTP ' + this.status + '</div>';
        }
    };
    
    xhr.onerror = function() {
        loader.style.display = 'none';
        content.innerHTML = '<div class="alert alert-danger">Erreur réseau</div>';
    };
    
    xhr.send('operation_id=' + encodeURIComponent(operationId));
    
    if (typeof jQuery !== 'undefined' && jQuery.fn.modal) {
        jQuery('#modal-details-operation').modal('show');
    }
}

// ============================================
// GÉNÉRATION CARTE
// ============================================
function ouvrirModalGenererCarte(operationId) {
    console.log('📋 Ouverture modal génération carte pour opération:', operationId);
    
    document.getElementById('carte-operation-id').value = operationId;
    document.getElementById('input-titre-carte').value = 'Carte de suivi Opération #' + operationId;
    
    if (typeof jQuery !== 'undefined' && jQuery.fn.modal) {
        jQuery('#modal-generer-carte').modal('show');
    }
}

function validerFormulaireCarte() {
    var titre = document.getElementById('input-titre-carte').value;
    
    if (!titre || titre.trim() === '') {
        notif('Veuillez saisir un titre', 'warning');
        return false;
    }
    
    return true;
}

function genererCartePNG() {
    if (!validerFormulaireCarte()) return;
    
    var operationId = document.getElementById('carte-operation-id').value;
    var titre = document.getElementById('input-titre-carte').value;
    
    var btnSubmit = document.getElementById('btn-generer-carte');
    var texteOriginal = btnSubmit.innerHTML;
    btnSubmit.disabled = true;
    btnSubmit.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Génération...';
    
    var formData = new FormData();
    formData.append('operation_id', operationId);
    formData.append('titre_carte', titre);
    
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'index.php?p=confirmationCheque.genererCartePNG');
    xhr.responseType = 'blob';
    
    xhr.onload = function() {
        btnSubmit.disabled = false;
        btnSubmit.innerHTML = texteOriginal;
        
        if (this.status === 200) {
            try {
                var blob = new Blob([this.response], { type: 'image/png' });
                var url = window.URL.createObjectURL(blob);
                var a = document.createElement('a');
                a.href = url;
                a.download = 'carte_operation_' + operationId + '.png';
                document.body.appendChild(a);
                a.click();
                window.URL.revokeObjectURL(url);
                document.body.removeChild(a);
                
                jQuery('#modal-generer-carte').modal('hide');
                notif('Carte générée avec succès', 'success');
            } catch (e) {
                console.error('❌ Erreur génération PNG:', e);
                notif('Erreur lors de la génération', 'error');
            }
        } else {
            notif('Erreur HTTP ' + this.status, 'error');
        }
    };
    
    xhr.onerror = function() {
        btnSubmit.disabled = false;
        btnSubmit.innerHTML = texteOriginal;
        notif('Erreur réseau', 'error');
    };
    
    xhr.send(formData);
}

// ============================================
// GESTION DE L'ONGLET CLIENTS AUTORISÉS
// ============================================
$(document).ready(function() {
    // Recherche dans la liste des clients
    $('#search-clients-autorises').on('keyup', function() {
        var searchTerm = $(this).val().toLowerCase();
        
        $('#table-clients-autorises tbody tr').each(function() {
            var nomClient = $(this).find('td:eq(2)').text().toLowerCase();
            var site = $(this).find('td:eq(1)').text().toLowerCase();
            var contact = $(this).find('td:eq(3)').text().toLowerCase();
            
            if (nomClient.indexOf(searchTerm) > -1 || site.indexOf(searchTerm) > -1 || contact.indexOf(searchTerm) > -1) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });

    // Export de la liste des clients
    $('#btn-export-clients').click(function() {
        var csv = "Site demandeur,Nom client,Contact,Plafond (FCFA),Parrainage\n";
        
        $('#table-clients-autorises tbody tr:visible').each(function() {
            var site = $(this).find('td:eq(1)').text().trim();
            var nom = $(this).find('td:eq(2)').text().trim();
            var contact = $(this).find('td:eq(3)').text().trim();
            var plafond = $(this).find('td:eq(4)').text().trim().replace(/\s/g, '');
            var parrainage = $(this).find('td:eq(5)').text().trim();
            
            csv += '"' + site + '","' + nom + '","' + contact + '","' + plafond + '","' + parrainage + '"\n';
        });
        
        var blob = new Blob(["\uFEFF" + csv], { type: 'text/csv;charset=utf-8;' });
        var link = document.createElement("a");
        var url = URL.createObjectURL(blob);
        link.href = url;
        link.download = "clients_autorises_" + new Date().toISOString().slice(0,10) + ".csv";
        link.click();
        URL.revokeObjectURL(url);
    });
});

// ============================================
// INITIALISATION
// ============================================
$(document).ready(function() {
    console.log('🚀 Page gestion opérations initialisée');
    console.log('🏢 Agence utilisateur:', userAgenceNom, '(ID:', userAgenceId + ')');
    console.log('📍 Site utilisateur:', userSite);
    console.log('📊 Nombre de clients autorisés:', <?php echo $totalClients; ?>);
    
    // Forcer l'activation de l'onglet opérations au chargement
    $('#operations-tab').tab('show');
    
    // Tooltips
    $('[title]').tooltip({placement:'top', trigger:'hover'});
    
    // Gestion des modales
    $('.modal').on('show.bs.modal', function() { 
        $(this).css('z-index','1050');
        if(!$(this).find('.modal-dialog').hasClass('modal-dialog-centered')) {
            $(this).find('.modal-dialog').css('margin-top','80px');
        }
    });
    
    // Boutons
    $('#btn-ajouter-operation').click(function(e) {
        e.preventDefault();
        ouvrirModalAjout();
    });
    
    $('#btn-enregistrer-operation').click(function(e) {
        e.preventDefault();
        envoyerAjout();
    });
    
    $('#btn-modifier-operation').click(function(e) {
        e.preventDefault();
        envoyerModification();
    });
    
    $('#btn-enregistrer-statut').click(function(e) {
        e.preventDefault();
        envoyerChangementStatut();
    });
    
    $('#btn-enregistrer-etat-confirmation').click(function(e) {
        e.preventDefault();
        envoyerChangementEtatConfirmation();
    });
    
    $('#btn-enregistrer-etat').click(function(e) {
        e.preventDefault();
        envoyerChangementEtat();
    });
    
    $('#btn-generer-carte').click(function(e) {
        e.preventDefault();
        genererCartePNG();
    });
    
    // Recherche et filtres
    $('#filter-type').change(function() {
        filterByType();
    });
    
    $('#btn-search').click(function() {
        effectuerRecherche();
    });
    
    $('#search-operations').keypress(function(e) {
        if (e.which === 13) {
            effectuerRecherche();
        }
    });
    
    $('#btn-clear-search').click(function() {
        effacerRecherche();
    });
    
    // Gestion du type d'opération
    $('input[name="type_operation"]').change(function() {
        updateFormLabels(this.value);
    });
    
    // Gestion des statuts dans les modals
    $('.statut-option').click(function() {
        $('.statut-option').removeClass('active');
        $(this).addClass('active');
        
        var modal = $(this).closest('.modal');
        var value = $(this).data('value');
        
        if (modal.attr('id') === 'modal-changement-statut') {
            $('#input-nouveau-statut').val(value);
        } else if (modal.attr('id') === 'modal-changement-etat-confirmation') {
            $('#input-nouvel-etat-confirmation').val(value);
        } else if (modal.attr('id') === 'modal-changement-etat') {
            $('#input-nouvel-etat').val(value);
        }
    });
    
    // Gestion des événements sur les boutons d'action
    $(document).on('click', '.modifier-operation', function(e) {
        e.preventDefault();
        var operationId = $(this).data('id');
        ouvrirModalModifier(operationId);
    });
    
    $(document).on('click', '.changer-statut', function(e) {
        e.preventDefault();
        var operationId = $(this).data('id');
        ouvrirModalChangerStatut(operationId);
    });
    
    $(document).on('click', '.changer-etat-confirmation', function(e) {
        e.preventDefault();
        var operationId = $(this).data('id');
        ouvrirModalChangerEtatConfirmation(operationId);
    });
    
    $(document).on('click', '.changer-etat', function(e) {
        e.preventDefault();
        var operationId = $(this).data('id');
        ouvrirModalChangerEtat(operationId);
    });
    
    $(document).on('click', '.details-operation', function(e) {
        e.preventDefault();
        var operationId = $(this).data('id');
        ouvrirModalDetails(operationId);
    });
    
    $(document).on('click', '.generer-carte', function(e) {
        e.preventDefault();
        var operationId = $(this).data('id');
        ouvrirModalGenererCarte(operationId);
    });
    
    // Datepicker
    if (typeof jQuery.fn.datepicker !== 'undefined') {
        $('.datepicker-operation, .datepicker-operation-modifier').datepicker({
            format: 'dd/mm/yyyy',
            language: 'fr',
            autoclose: true,
            todayHighlight: true,
            startDate: new Date(2020, 0, 1),
            endDate: new Date()
        });
        
        $('.btn-calendrier, .btn-calendrier-modifier').click(function(e) {
            e.preventDefault();
            var input = $(this).closest('.input-group').find('input[type="text"]');
            if (input.length) {
                input.datepicker('show');
            }
        });
    }
    
    // Prévisualisation des scans
    $('#input-scan').change(function(e) {
        var file = e.target.files[0];
        var preview = $('#preview-scan');
        preview.empty();
        
        if (!file) return;
        
        if (file.size > 5 * 1024 * 1024) {
            notif('Le fichier est trop volumineux (max 5MB)', 'warning');
            $(this).val('');
            return;
        }
        
        if (file.type.indexOf('image/') === 0) {
            var reader = new FileReader();
            reader.onload = function(e) {
                preview.html(
                    '<div style="background:var(--success-light); padding:10px; border-radius:8px;">' +
                    '<img src="' + e.target.result + '" style="max-width:200px; max-height:150px; border:1px solid #ddd; padding:3px;">' +
                    '<div style="margin-top:5px; font-size:12px;">' + escapeHtml(file.name) + ' (' + Math.round(file.size / 1024) + ' KB)</div>' +
                    '</div>'
                );
            };
            reader.readAsDataURL(file);
        } else {
            preview.html(
                '<div style="background:var(--info-light); padding:10px; border-radius:8px;">' +
                '<i class="fa fa-file" style="color:var(--info);"></i> ' + escapeHtml(file.name) + ' (' + Math.round(file.size / 1024) + ' KB)' +
                '</div>'
            );
        }
    });
    
    $('#modifier-input-scan').change(function(e) {
        var file = e.target.files[0];
        var preview = $('#modifier-preview-scan');
        preview.empty();
        
        if (!file) return;
        
        if (file.size > 5 * 1024 * 1024) {
            notif('Le fichier est trop volumineux (max 5MB)', 'warning');
            $(this).val('');
            return;
        }
        
        if (file.type.indexOf('image/') === 0) {
            var reader = new FileReader();
            reader.onload = function(e) {
                preview.html(
                    '<div style="background:var(--success-light); padding:10px; border-radius:8px;">' +
                    '<img src="' + e.target.result + '" style="max-width:200px; max-height:150px; border:1px solid #ddd; padding:3px;">' +
                    '<div style="margin-top:5px; font-size:12px;">Nouveau: ' + escapeHtml(file.name) + ' (' + Math.round(file.size / 1024) + ' KB)</div>' +
                    '</div>'
                );
            };
            reader.readAsDataURL(file);
        } else {
            preview.html(
                '<div style="background:var(--info-light); padding:10px; border-radius:8px;">' +
                '<i class="fa fa-file" style="color:var(--info);"></i> Nouveau: ' + escapeHtml(file.name) + ' (' + Math.round(file.size / 1024) + ' KB)' +
                '</div>'
            );
        }
    });
});

// ============================================
// FONCTION DE NAVIGATION
// ============================================
function naviguerVers(url) {
    if (url && url !== '#') {
        window.location.href = url;
    }
    return false;
}
</script>

<?php
function genererLigneOperationModerne($operation, $user, $agenceNom, $isAdminOrCompta = false, $isCompta = false, $allowedToAdd = false) {
    if (!is_array($operation)) return '';
    
    // Déterminer le statut
    $statusClass = 'status-en-cours';
    $statut = isset($operation['statut']) ? strtolower($operation['statut']) : 'en cours';
    if ($statut === 'confirmé' || $statut === 'confirme') {
        $statusClass = 'status-termine';
        $statut = 'Confirmé';
    } elseif ($statut === 'annulé' || $statut === 'annule') {
        $statusClass = 'status-annule';
        $statut = 'Annulé';
    } else {
        $statut = 'En cours';
    }
    
    // États de confirmation et validation
    $etatConfText = isset($operation['etat_confirmation']) ? $operation['etat_confirmation'] : 'Non';
    $etatValidText = isset($operation['etat_validation']) ? $operation['etat_validation'] : 'Non';
    
    // Informations de l'opération
    $operation_id = isset($operation['id']) ? $operation['id'] : 0;
    $type_operation = isset($operation['type_operation']) ? $operation['type_operation'] : 'cheque';
    $nom_client = isset($operation['nom_client']) ? htmlspecialchars($operation['nom_client']) : '';
    $numero_cheque = isset($operation['numero_cheque']) ? htmlspecialchars($operation['numero_cheque']) : '';
    $montant = isset($operation['montant']) ? number_format($operation['montant'], 0, ',', ' ') : '0';
    $banque = isset($operation['banque']) ? htmlspecialchars($operation['banque']) : '';
    
    // Dates
    $date_entree = '';
    if (isset($operation['date_entree'])) {
        if ($operation['date_entree'] instanceof DateTime) {
            $date_entree = $operation['date_entree']->format('d/m/Y H:i');
        } elseif (is_string($operation['date_entree'])) {
            $date_entree = date('d/m/Y H:i', strtotime($operation['date_entree']));
        }
    }
    
    // Icône du type
    $typeIcon = '';
    if ($type_operation === 'virement') {
        $typeIcon = '<i class="fa fa-exchange type-virement" title="Virement" style="font-size:16px; color:var(--info);"></i>';
    } else {
        $typeIcon = '<i class="fa fa-money type-cheque" title="Chèque" style="font-size:16px; color:var(--success);"></i>';
    }
    
    // Vérifier si l'opération peut être modifiée
    $canBeModified = ($statut === 'En cours' && $etatConfText === 'Non' && $etatValidText === 'Non');
    
    // Vérifier si l'utilisateur est le créateur
    $isCreator = false;
    $userId = isset($user['idUser']) ? $user['idUser'] : (isset($user['id']) ? $user['id'] : null);
    $operationCreatedBy = isset($operation['created_by']) ? $operation['created_by'] : null;
    
    if (empty($operationCreatedBy) || $operationCreatedBy == $userId) {
        $isCreator = true;
    }
    
    $html = '<tr id="operation-' . $operation_id . '">';
    $html .= '<td class="text-center">' . $typeIcon . '</td>';
    $html .= '<td class="text-center"><span style="font-size:11px;">' . $date_entree . '</span></td>';
    $html .= '<td><strong>' . $nom_client . '</strong></td>';
    $html .= '<td><span style="font-size:12px;">' . $numero_cheque . '</span></td>';
    $html .= '<td class="text-right"><strong>' . $montant . '</strong> FCFA</td>';
    $html .= '<td>' . $banque . '</td>';
    $html .= '<td class="text-center"><span class="status-badge ' . $statusClass . '">' . $statut . '</span></td>';
    $html .= '<td class="text-center"><span class="status-badge ' . ($etatConfText === 'Oui' ? 'status-termine' : 'status-en-cours') . '">' . $etatConfText . '</span></td>';
    $html .= '<td class="text-center"><span class="status-badge ' . ($etatValidText === 'Oui' ? 'status-termine' : 'status-en-cours') . '">' . $etatValidText . '</span></td>';
    $html .= '<td>' . htmlspecialchars($agenceNom) . '</td>';
    $html .= '<td class="text-center"><div class="action-icons">';
    
    // BOUTON DÉTAILS - TOUJOURS VISIBLE POUR TOUS
    $html .= '<a href="#" class="details-operation action-link purple" data-id="' . $operation_id . '" title="Détails">';
    $html .= '<i class="fa fa-info-circle"></i></a>';
    
    // BOUTON GÉNÉRER CARTE - TOUJOURS VISIBLE POUR TOUS
    $html .= '<a href="#" class="generer-carte action-link green" data-id="' . $operation_id . '" title="Générer carte PNG">';
    $html .= '<i class="fa fa-credit-card"></i></a>';
    
    // Bouton Modifier (si autorisé)
    if ($allowedToAdd && $canBeModified && ($isAdminOrCompta || $isCreator)) {
        $html .= '<a href="#" class="modifier-operation action-link blue" data-id="' . $operation_id . '" title="Modifier">';
        $html .= '<i class="fa fa-pencil"></i></a>';
    }
    
    // Actions pour Comptabilité uniquement (pas Admin)
    if ($isCompta) {
        if ($statut === 'En cours') {
            $html .= '<a href="#" class="changer-statut action-link orange" data-id="' . $operation_id . '" title="Changer statut">';
            $html .= '<i class="fa fa-exchange"></i></a>';
        }
        
        if ($etatConfText === 'Non' && $statut === 'En cours') {
            $html .= '<a href="#" class="changer-etat-confirmation action-link green" data-id="' . $operation_id . '" title="Changer état confirmation">';
            $html .= '<i class="fa fa-check-circle"></i></a>';
        }
        
        if ($etatValidText === 'Non' && $statut === 'En cours') {
            $html .= '<a href="#" class="changer-etat action-link blue" data-id="' . $operation_id . '" title="Changer état validation">';
            $html .= '<i class="fa fa-check-square-o"></i></a>';
        }
    }
    
    $html .= '</div></td></tr>';
    
    return $html;
}
?>