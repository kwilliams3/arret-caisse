<?php
use Core\Model\App;
use Core\Model\Session;
use Core\Database\Agence;

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

// Admin et Compta voient tout
$isAdminOrCompta = (stripos($userPrivilege, 'admin') !== false || stripos($userPrivilege, 'compta') !== false);
$isCompta = ($userPrivilege === 'Comptabilite');

// Récupération des paramètres
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$type = isset($_GET['type']) ? $_GET['type'] : '';

// Les données sont passées depuis le contrôleur
$operations = isset($operations) ? $operations : array();
$totalOperations = isset($totalOperations) ? $totalOperations : 0;
$totalPages = isset($totalPages) ? $totalPages : 1;
$allowedToAdd = isset($allowedToAdd) ? $allowedToAdd : false;
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

.type-bo { color: var(--primary); font-weight:600; font-size:16px; }
.type-lc { color: var(--info); font-weight:600; font-size:16px; }

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

/* Échéance */
.echeance-group { display:flex; gap:10px; margin-bottom:15px; }
.echeance-group .form-group { flex:1; }
.echeance-group select { width:120px; }

/* FontAwesome Fixes pour FA4 */
.fa { font-family:FontAwesome !important; }

@media (max-width:768px) { 
    .actions-grid { grid-template-columns:repeat(2,1fr); } 
    .table-header-modern { flex-direction:column; align-items:flex-start; } 
    .search-wrapper { width:100%; }
    .modal-dialog { margin:10px !important; max-width:calc(100% - 20px) !important; } 
    .stats-grid { justify-content:center; }
    .pagination-modern { justify-content:center; }
    .details-row { flex-direction:column; }
    .details-label { width:100%; margin-bottom:5px; }
    .echeance-group { flex-direction:column; }
    .echeance-group select { width:100%; }
    /* Reset des marges sur mobile */
    .modal-dialog.modal-lg {
        margin: 20px !important;
        max-width: calc(100% - 40px) !important;
    }
}
@media (max-width:576px) { 
    .modern-header-banner,.modern-table-container { padding:15px; } 
    .actions-grid { grid-template-columns:1fr; } 
    .modal-footer.modern-modal-footer { flex-direction:column; } 
    .btn-modern { width:100%; justify-content:center; } 
    .stats-grid { flex-direction:column; }
    .stat-item { width:100%; }
    .radio-group { flex-direction:column; gap:10px; }
}
</style>

<div class="operations-container">
    <!-- Fil d'Ariane -->
    <div class="modern-breadcrumbs" style="margin-bottom:15px;">
        <ul class="breadcrumb" style="background:none; padding:0;">
            <li style="display:inline-block;">
                <i class="fa fa-home" style="color:var(--primary);"></i>
                <a href="#" onclick="return naviguerVers('<?php echo App::url("dashboard"); ?>')" style="color:var(--dark);">Accueil</a>
            </li>
            <li style="display:inline-block; margin-left:8px;">
                <i class="fa fa-angle-right" style="color:var(--gray);"></i>
                Effet de Commerce
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
                <i class="fa <?php echo $type === 'billet_ordre' ? 'fa-file-text' : 'fa-exchange'; ?>" style="color:<?php echo $type === 'billet_ordre' ? 'var(--primary)' : 'var(--info)'; ?>"></i>
                <?php echo $type === 'billet_ordre' ? 'Billet à Ordre' : 'Lettre de Change'; ?>
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
                    <span class="announcement-text">⚠️ RESPONSABILITÉ EXCLUSIVE DU CHEF D'AGENCE</span>
                </h4>
                <p style="margin: 0; font-size: 15px; font-weight: 600; color: #721c24;">
                    <strong>Tout ajout d'effet de commerce engage pleinement la responsabilité du chef d'agence.</strong>
                </p>
            </div>
            <div class="alert-icon" style="font-size: 40px; color: #dc3545; opacity: 0.8; animation: rotate 10s linear infinite;">
                <i class="fa fa-gavel"></i>
            </div>
        </div>
    </div>

    <!-- Header avec statistiques -->
    <div class="modern-header-banner">
        <h1 class="banner-title"><i class="fa fa-bank"></i> GESTION DES EFFETS DE COMMERCE</h1>
        <p class="banner-subtitle">
            Gestion des Billets à Ordre et Lettres de Change • Suivi en temps réel
            <?php if (!$isAdminOrCompta && !empty($userAgenceNom)): ?>
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
                    <div class="stat-label">Total effets</div>
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
                <label class="modern-form-label"><i class="fa fa-filter"></i> Type d'effet</label>
                <select class="filter-select" id="filter-type" style="width:100%;">
                    <option value="">Tous les types</option>
                    <option value="billet_ordre" <?php if ($type === 'billet_ordre') echo 'selected'; ?>>Billet à Ordre</option>
                    <option value="lettre_change" <?php if ($type === 'lettre_change') echo 'selected'; ?>>Lettre de Change</option>
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

    <!-- Actions -->
    <div class="actions-section">
        <div class="actions-grid">
            <?php if($allowedToAdd) : ?>
            <a href="#" class="modern-action-btn add-operation-btn" id="btn-ajouter-operation">
                <i class="fa fa-plus btn-icon"></i>
                <span class="btn-text">Ajouter</span>
                <span class="btn-subtext">Billet à Ordre / Lettre de Change</span>
            </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Tableau des opérations -->
    <div class="modern-table-container">
        <div class="table-header-modern">
            <div class="table-title">
                <i class="fa fa-list"></i> 
                Liste des effets de commerce
                (<?php echo $totalOperations; ?> effet<?php if ($totalOperations > 1) echo 's'; ?>)
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
                        <th class="text-center" width="100">N° Effet</th>
                        <th class="text-left">Tireur</th>
                        <th class="text-left">Tiré</th>
                        <th class="text-center">Échéance</th>
                        <th class="text-left">Banque</th>
                        <th class="text-center" width="80">Statut</th>
                        <th class="text-center" width="80">Confirmation</th>
                        <th class="text-center" width="80">Validation</th>
                        <th class="text-left">Agence</th>
                        <th class="text-center" width="140">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($operations)) : ?>
                        <?php 
                        $agencesCache = array();
                        if ($userAgenceId && !$isAdminOrCompta) {
                            $agencesCache[$userAgenceId] = $userAgenceNom;
                        }
                        
                        foreach($operations as $operation) : 
                            if (!is_array($operation)) continue;
                            
                            $agenceNom = '';
                            $agenceId = isset($operation['agence_id']) ? $operation['agence_id'] : null;
                            
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
                            
                            echo genererLigneEffetModerne($operation, $user, $agenceNom, $isAdminOrCompta, $isCompta, $allowedToAdd);
                        endforeach; 
                        ?>
                    <?php else : ?>
                    <tr>
                        <td colspan="11" class="text-center">
                            <div class="empty-state">
                                <i class="fa fa-bank"></i>
                                <h4>Aucun effet trouvé</h4>
                                <p>
                                    <?php if (!empty($search)): ?>
                                        <?php if ($isAdminOrCompta): ?>
                                            Aucun résultat pour "<?php echo htmlspecialchars($search); ?>"
                                        <?php else: ?>
                                            Aucun résultat pour "<?php echo htmlspecialchars($search); ?>" dans votre agence
                                        <?php endif; ?>
                                    <?php elseif (!empty($type)): ?>
                                        <?php if ($isAdminOrCompta): ?>
                                            Aucun <?php if ($type === 'billet_ordre') { echo 'billet à ordre'; } else { echo 'lettre de change'; } ?> enregistré
                                        <?php else: ?>
                                            Aucun <?php if ($type === 'billet_ordre') { echo 'billet à ordre'; } else { echo 'lettre de change'; } ?> enregistré dans votre agence
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <?php if ($isAdminOrCompta): ?>
                                            Aucun effet enregistré
                                        <?php else: ?>
                                            Aucun effet enregistré dans votre agence
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
        
        <!-- Pagination -->
        <?php if ($totalPages > 1) : ?>
        <div style="display:flex; flex-wrap:wrap; justify-content:space-between; align-items:center; margin-top:20px;">
            <div class="pagination-info">
                Affichage de <?php if ($totalOperations > 0) { echo (($page - 1) * 10) + 1; } else { echo 0; } ?> à <?php echo min($page * 10, $totalOperations); ?> sur <?php echo $totalOperations; ?> entrées
            </div>
            <div class="pagination-modern">
                <?php
                if ($page > 1) {
                    $prevUrl = '?p=effetCommerce&page=' . ($page - 1);
                    if (!empty($search)) $prevUrl .= '&search=' . urlencode($search);
                    if (!empty($type)) $prevUrl .= '&type=' . urlencode($type);
                    echo '<a href="' . $prevUrl . '" class="pagination-item"><i class="fa fa-chevron-left"></i></a>';
                } else {
                    echo '<span class="pagination-item disabled"><i class="fa fa-chevron-left"></i></span>';
                }
                
                $maxPagesToShow = 5;
                $startPage = max(1, $page - floor($maxPagesToShow / 2));
                $endPage = min($totalPages, $startPage + $maxPagesToShow - 1);
                
                if ($startPage > 1) {
                    $firstUrl = '?p=effetCommerce&page=1';
                    if (!empty($search)) $firstUrl .= '&search=' . urlencode($search);
                    if (!empty($type)) $firstUrl .= '&type=' . urlencode($type);
                    echo '<a href="' . $firstUrl . '" class="pagination-item">1</a>';
                    if ($startPage > 2) echo '<span class="pagination-item disabled">...</span>';
                }
                
                for ($i = $startPage; $i <= $endPage; $i++) {
                    if ($i == $page) {
                        echo '<span class="pagination-item active">' . $i . '</span>';
                    } else {
                        $pageUrl = '?p=effetCommerce&page=' . $i;
                        if (!empty($search)) $pageUrl .= '&search=' . urlencode($search);
                        if (!empty($type)) $pageUrl .= '&type=' . urlencode($type);
                        echo '<a href="' . $pageUrl . '" class="pagination-item">' . $i . '</a>';
                    }
                }
                
                if ($endPage < $totalPages) {
                    if ($endPage < $totalPages - 1) echo '<span class="pagination-item disabled">...</span>';
                    $lastUrl = '?p=effetCommerce&page=' . $totalPages;
                    if (!empty($search)) $lastUrl .= '&search=' . urlencode($search);
                    if (!empty($type)) $lastUrl .= '&type=' . urlencode($type);
                    echo '<a href="' . $lastUrl . '" class="pagination-item">' . $totalPages . '</a>';
                }
                
                if ($page < $totalPages) {
                    $nextUrl = '?p=effetCommerce&page=' . ($page + 1);
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

<!-- MODAL AJOUT EFFET -->
<div id="modal-ajout-operation" class="modal fade" style="z-index: 1050 !important;">
    <div class="modal-dialog modal-lg" style="margin: 80px 20px 20px 280px !important; max-width: 1000px !important;">
        <div class="modal-content">
            <div class="modal-header modern-modal-header">
                <h5 class="modal-title"><i class="fa fa-plus-circle"></i> Nouvel effet de commerce</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body modern-modal-body" style="max-height: 70vh; overflow-y: auto; padding: 25px;">
                <form id="form-ajout-operation" enctype="multipart/form-data" autocomplete="off">
                    <input type="hidden" name="agence_id" value="<?php echo $userAgenceId; ?>">
                    <input type="hidden" name="created_by" value="<?php echo isset($user['idUser']) ? $user['idUser'] : (isset($user['id']) ? $user['id'] : ''); ?>">
                    
                    <?php if (!empty($userAgenceNom)): ?>
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
                        <strong>Attention :</strong> Chaque effet doit être unique dans votre agence.
                    </div>
                    
                    <!-- Type d'effet -->
                    <div class="form-group">
                        <label class="modern-form-label"><i class="fa fa-tag"></i> Type d'effet <span class="required">*</span></label>
                        <div class="radio-group" style="display: flex; gap: 30px;">
                            <label class="radio-option" style="display: flex; align-items: center; gap: 8px;">
                                <input type="radio" name="type_operation" value="billet_ordre" checked id="radio-bo">
                                <i class="fa fa-file-text" style="color:var(--primary);"></i> Billet à Ordre
                            </label>
                            <label class="radio-option" style="display: flex; align-items: center; gap: 8px;">
                                <input type="radio" name="type_operation" value="lettre_change" id="radio-lc">
                                <i class="fa fa-exchange" style="color:var(--info);"></i> Lettre de Change
                            </label>
                        </div>
                    </div>
                    
                    <!-- Ligne 1: Tireur et Tiré -->
                    <div style="display: flex; gap: 20px; margin-bottom: 15px;">
                        <div style="flex: 1;">
                            <label class="modern-form-label"><i class="fa fa-user"></i> Tireur <span class="required">*</span></label>
                            <input type="text" class="modern-form-control" id="input-nom-tireur" name="nom_tireur" placeholder="Nom du tireur" required style="width: 100%;">
                        </div>
                        <div style="flex: 1;">
                            <label class="modern-form-label"><i class="fa fa-user"></i> Tiré <span class="required">*</span></label>
                            <input type="text" class="modern-form-control" id="input-nom-tire" name="nom_tire" placeholder="Nom du tiré" required style="width: 100%;">
                        </div>
                    </div>
                    
                    <!-- Ligne 2: Date d'émission et Banque -->
                    <div style="display: flex; gap: 20px; margin-bottom: 15px;">
                        <div style="flex: 1;">
                            <label class="modern-form-label"><i class="fa fa-calendar"></i> Date d'émission <span class="required">*</span></label>
                            <div style="display: flex;">
                                <input type="text" class="modern-form-control datepicker-operation" id="input-date-emission" name="date_emission" placeholder="JJ/MM/AAAA" required style="flex: 1; border-radius:8px 0 0 8px;">
                                <span class="btn-calendrier" style="background:var(--gray-light); border:1px solid #dee2e6; border-left:none; border-radius:0 8px 8px 0; padding:0 15px; display:flex; align-items:center; cursor:pointer;">
                                    <i class="fa fa-calendar" style="color:var(--primary);"></i>
                                </span>
                            </div>
                        </div>
                        <div style="flex: 1;">
                            <label class="modern-form-label"><i class="fa fa-bank"></i> Banque (Domiciliation) <span class="required">*</span></label>
                            <input type="text" class="modern-form-control" id="input-banque" name="banque" placeholder="Nom de la banque" required style="width: 100%;">
                        </div>
                    </div>
                    
                    <!-- Ligne 3: Échéance -->
                    <div style="margin-bottom: 15px;">
                        <label class="modern-form-label"><i class="fa fa-clock-o"></i> Échéance <span class="required">*</span></label>
                        <div class="echeance-group" style="display: flex; gap: 10px;">
                            <select class="modern-form-control" id="echeance-type" name="echeance_type" style="width: 150px;">
                                <option value="date">Date fixe</option>
                                <option value="jours">Nombre de jours</option>
                            </select>
                            <div id="echeance-date-container" style="flex: 1;">
                                <div style="display: flex;">
                                    <input type="text" class="modern-form-control datepicker-operation" id="echeance-date" name="echeance_date" placeholder="JJ/MM/AAAA" style="flex: 1; border-radius:8px 0 0 8px;">
                                    <span class="btn-calendrier" style="background:var(--gray-light); border:1px solid #dee2e6; border-left:none; border-radius:0 8px 8px 0; padding:0 15px; display:flex; align-items:center; cursor:pointer;">
                                        <i class="fa fa-calendar" style="color:var(--primary);"></i>
                                    </span>
                                </div>
                            </div>
                            <div id="echeance-jours-container" style="flex: 1; display: none;">
                                <input type="number" class="modern-form-control" id="echeance-jours" name="echeance_jours" placeholder="Nombre de jours" min="1" style="width: 100%;">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Ligne 4: Scan du document -->
                    <div style="margin-bottom: 15px;">
                        <label class="modern-form-label"><i class="fa fa-file-image-o"></i> Scan du document <span class="required">*</span></label>
                        <input type="file" class="modern-form-control" id="input-scan" name="scan_operation" 
                               accept=".jpg,.jpeg,.png,.pdf" required style="width: 100%; padding: 8px 15px;">
                        <small class="text-muted">Formats acceptés: JPG, PNG, PDF (max 5MB) - OBLIGATOIRE</small>
                        <div id="preview-scan" class="file-preview" style="margin-top: 10px;"></div>
                    </div>
                    
                    <!-- Ligne 5: Observations -->
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

<!-- MODAL MODIFICATION -->
<div id="modal-modifier-operation" class="modal fade" style="z-index: 1050 !important;">
    <div class="modal-dialog modal-lg" style="margin: 80px 20px 20px 280px !important; max-width: 1000px !important;">
        <div class="modal-content">
            <div class="modal-header modern-modal-header">
                <h5 class="modal-title"><i class="fa fa-pencil"></i> Modifier l'effet</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body modern-modal-body" style="max-height: 70vh; overflow-y: auto; padding: 25px;">
                <form id="form-modifier-operation" enctype="multipart/form-data" autocomplete="off">
                    <input type="hidden" name="operation_id" id="modifier-operation-id">
                    <input type="hidden" name="agence_id" value="<?php echo $userAgenceId; ?>">
                    
                    <div class="alert alert-info" style="background:var(--info-light); border-left:4px solid var(--info); padding:15px; border-radius:8px; margin-bottom:20px;">
                        <i class="fa fa-info-circle" style="color:var(--info);"></i> 
                        Tous les champs avec <span class="required">*</span> sont obligatoires
                    </div>
                    
                    <div class="alert alert-warning" style="background:var(--warning-light); border-left:4px solid var(--warning); padding:15px; border-radius:8px; margin-bottom:20px;">
                        <i class="fa fa-exclamation-triangle" style="color:var(--warning);"></i> 
                        <strong>Attention :</strong> Les numéros doivent être uniques dans votre agence.
                    </div>
                    
                    <!-- Type (lecture seule) -->
                    <div class="form-group">
                        <label class="modern-form-label"><i class="fa fa-tag"></i> Type d'effet</label>
                        <div class="form-control-static" id="modifier-type-operation-display" style="padding:12px 15px; background:var(--gray-light); border-radius:8px;"></div>
                        <input type="hidden" id="modifier-input-type-operation" name="type_operation">
                    </div>
                    
                    <!-- Tireur et Tiré -->
                    <div style="display: flex; gap: 20px; margin-bottom: 15px;">
                        <div style="flex: 1;">
                            <label class="modern-form-label"><i class="fa fa-user"></i> Tireur <span class="required">*</span></label>
                            <input type="text" class="modern-form-control" id="modifier-input-nom-tireur" name="nom_tireur" required style="width: 100%;">
                        </div>
                        <div style="flex: 1;">
                            <label class="modern-form-label"><i class="fa fa-user"></i> Tiré <span class="required">*</span></label>
                            <input type="text" class="modern-form-control" id="modifier-input-nom-tire" name="nom_tire" required style="width: 100%;">
                        </div>
                    </div>
                    
                    <!-- Date d'émission et Banque -->
                    <div style="display: flex; gap: 20px; margin-bottom: 15px;">
                        <div style="flex: 1;">
                            <label class="modern-form-label"><i class="fa fa-calendar"></i> Date d'émission <span class="required">*</span></label>
                            <div style="display: flex;">
                                <input type="text" class="modern-form-control datepicker-operation-modifier" id="modifier-input-date-emission" name="date_emission" required style="flex: 1; border-radius:8px 0 0 8px;">
                                <span class="btn-calendrier-modifier" style="background:var(--gray-light); border:1px solid #dee2e6; border-left:none; border-radius:0 8px 8px 0; padding:0 15px; display:flex; align-items:center; cursor:pointer;">
                                    <i class="fa fa-calendar" style="color:var(--primary);"></i>
                                </span>
                            </div>
                        </div>
                        <div style="flex: 1;">
                            <label class="modern-form-label"><i class="fa fa-bank"></i> Banque <span class="required">*</span></label>
                            <input type="text" class="modern-form-control" id="modifier-input-banque" name="banque" required style="width: 100%;">
                        </div>
                    </div>
                    
                    <!-- Échéance -->
                    <div style="margin-bottom: 15px;">
                        <label class="modern-form-label"><i class="fa fa-clock-o"></i> Échéance</label>
                        <div class="echeance-group" style="display: flex; gap: 10px;">
                            <select class="modern-form-control" id="modifier-echeance-type" name="echeance_type" style="width: 150px;">
                                <option value="date">Date fixe</option>
                                <option value="jours">Nombre de jours</option>
                            </select>
                            <div id="modifier-echeance-date-container" style="flex: 1;">
                                <div style="display: flex;">
                                    <input type="text" class="modern-form-control datepicker-operation-modifier" id="modifier-echeance-date" name="echeance_date" placeholder="JJ/MM/AAAA" style="flex: 1; border-radius:8px 0 0 8px;">
                                    <span class="btn-calendrier-modifier" style="background:var(--gray-light); border:1px solid #dee2e6; border-left:none; border-radius:0 8px 8px 0; padding:0 15px; display:flex; align-items:center; cursor:pointer;">
                                        <i class="fa fa-calendar" style="color:var(--primary);"></i>
                                    </span>
                                </div>
                            </div>
                            <div id="modifier-echeance-jours-container" style="flex: 1; display: none;">
                                <input type="number" class="modern-form-control" id="modifier-echeance-jours" name="echeance_jours" placeholder="Nombre de jours" min="1" style="width: 100%;">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Scan (optionnel) -->
                    <div style="margin-bottom: 15px;">
                        <label class="modern-form-label"><i class="fa fa-file-image-o"></i> Scan du document</label>
                        <input type="file" class="modern-form-control" id="modifier-input-scan" name="scan_operation" accept=".jpg,.jpeg,.png,.pdf" style="width: 100%; padding: 8px 15px;">
                        <small class="text-muted">Laissez vide pour conserver le document actuel</small>
                        <div id="modifier-preview-scan" class="file-preview" style="margin-top: 10px;"></div>
                        <div id="modifier-scan-actuel" class="file-preview" style="margin-top: 10px;"></div>
                    </div>
                    
                    <!-- Observations -->
                    <div style="margin-bottom: 15px;">
                        <label class="modern-form-label"><i class="fa fa-comment"></i> Observations</label>
                        <textarea class="modern-form-control" id="modifier-input-observations" name="observations" rows="2" style="width: 100%;"></textarea>
                    </div>
                    
                    <!-- Boutons -->
                    <div style="display: flex; justify-content: flex-end; gap: 15px; margin-top: 25px;">
                        <button type="button" class="btn-modern btn-modern-secondary" data-dismiss="modal" style="padding: 12px 30px;">
                            <i class="fa fa-times"></i> Annuler
                        </button>
                        <button type="button" id="btn-modifier-operation" class="btn-modern btn-modern-primary" style="padding: 12px 30px;">
                            <i class="fa fa-save"></i> Modifier
                        </button>
                    </div>
                    
                    <div class="modern-loader loaderModifier" style="display:none; text-align: center; padding: 20px;">
                        <i class="fa fa-spinner fa-spin" style="font-size: 32px; color: var(--primary);"></i>
                        <p style="margin-top: 10px;">Modification en cours...</p>
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
                        <h4 style="margin-bottom:20px;">Changer le statut de l'effet</h4>
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
                        <input type="hidden" id="input-nouveau-statut" name="nouvel_etat">
                    </div>
                    
                    <div class="form-group">
                        <label class="modern-form-label"><i class="fa fa-comment"></i> Observation <span class="required">*</span></label>
                        <textarea class="modern-form-control" id="input-observation-statut" name="observation" placeholder="Raison du changement..." rows="3" required></textarea>
                    </div>
                    
                    <div class="text-right" style="margin-top:20px;">
                        <button type="button" class="btn-modern btn-modern-secondary" data-dismiss="modal">Annuler</button>
                        <button type="button" id="btn-enregistrer-statut" class="btn-modern btn-modern-primary">Confirmer</button>
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
                        <input type="hidden" id="input-nouvel-etat-confirmation" name="nouvel_etat">
                    </div>
                    
                    <div class="form-group">
                        <label class="modern-form-label"><i class="fa fa-comment"></i> Observation <span class="required">*</span></label>
                        <textarea class="modern-form-control" id="input-observation-etat-conf" name="observation" placeholder="Raison du changement..." rows="3" required></textarea>
                    </div>
                    
                    <div class="text-right" style="margin-top:20px;">
                        <button type="button" class="btn-modern btn-modern-secondary" data-dismiss="modal">Annuler</button>
                        <button type="button" id="btn-enregistrer-etat-confirmation" class="btn-modern btn-modern-primary">Confirmer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- MODAL CHANGEMENT ÉTAT VALIDATION -->
<div id="modal-changement-etat-validation" class="modal fade">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header modern-modal-header">
                <h5 class="modal-title"><i class="fa fa-check-square-o"></i> Changer l'état de validation</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body modern-modal-body">
                <form id="form-changement-etat-validation" autocomplete="off">
                    <input type="hidden" name="operation_id" id="etat-validation-operation-id">
                    
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
                        <input type="hidden" id="input-nouvel-etat-validation" name="nouvel_etat">
                    </div>
                    
                    <div class="form-group">
                        <label class="modern-form-label"><i class="fa fa-comment"></i> Observation <span class="required">*</span></label>
                        <textarea class="modern-form-control" id="input-observation-etat-validation" name="observation" placeholder="Raison du changement..." rows="3" required></textarea>
                    </div>
                    
                    <div class="text-right" style="margin-top:20px;">
                        <button type="button" class="btn-modern btn-modern-secondary" data-dismiss="modal">Annuler</button>
                        <button type="button" id="btn-enregistrer-etat-validation" class="btn-modern btn-modern-primary">Confirmer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- MODAL DÉTAILS -->
<div id="modal-details-operation" class="modal fade">
    <div class="modal-dialog modal-lg" style="margin: 80px 20px 20px 280px !important; max-width: 900px !important;">
        <div class="modal-content">
            <div class="modal-header modern-modal-header">
                <h5 class="modal-title"><i class="fa fa-info-circle"></i> Détails de l'effet</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body modern-modal-body">
                <div id="details-loader" class="modern-loader" style="display:none;">
                    <i class="fa fa-spinner fa-spin"></i>
                    <p>Chargement...</p>
                </div>
                <div id="details-content"></div>
            </div>
            <div class="modal-footer modern-modal-footer">
                <button class="btn-modern btn-modern-secondary" data-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL GÉNÉRATION CARTE -->
<div id="modal-generer-carte" class="modal fade">
    <div class="modal-dialog modal-dialog-centered" style="margin: 80px 20px 20px 280px !important; max-width: 500px !important;">
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
                            La carte sera générée au format PNG (750x500px) avec toutes les informations.
                        </p>
                    </div>
                    
                    <div class="form-group">
                        <label class="modern-form-label"><i class="fa fa-header"></i> Titre de la carte <span class="required">*</span></label>
                        <input type="text" class="modern-form-control" id="input-titre-carte" name="titre_carte" placeholder="Ex: Carte de suivi Effet #..." required>
                    </div>
                    
                    <div class="text-right" style="margin-top:20px;">
                        <button type="button" class="btn-modern btn-modern-secondary" data-dismiss="modal">Annuler</button>
                        <button type="button" id="btn-generer-carte" class="btn-modern btn-modern-success">Générer</button>
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
var isAdminOrCompta = <?php echo $isAdminOrCompta ? 'true' : 'false'; ?>;
var isCompta = <?php echo $isCompta ? 'true' : 'false'; ?>;
var userAgenceId = <?php echo $userAgenceId ? $userAgenceId : 'null'; ?>;
var userAgenceNom = <?php echo json_encode($userAgenceNom); ?>;

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
        case 'success': icon = 'fa fa-check-circle'; notification.addClass('success'); break;
        case 'error': icon = 'fa fa-exclamation-circle'; notification.addClass('error'); break;
        case 'warning': icon = 'fa fa-exclamation-triangle'; notification.addClass('warning'); break;
        default: icon = 'fa fa-info-circle'; notification.addClass('info');
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
// GESTION DE L'ÉCHÉANCE
// ============================================
$(document).ready(function() {
    $('#echeance-type').change(function() {
        if ($(this).val() === 'date') {
            $('#echeance-date-container').show();
            $('#echeance-jours-container').hide();
            $('#echeance-jours').val('');
        } else {
            $('#echeance-date-container').hide();
            $('#echeance-jours-container').show();
            $('#echeance-date').val('');
        }
    });
    
    $('#modifier-echeance-type').change(function() {
        if ($(this).val() === 'date') {
            $('#modifier-echeance-date-container').show();
            $('#modifier-echeance-jours-container').hide();
            $('#modifier-echeance-jours').val('');
        } else {
            $('#modifier-echeance-date-container').hide();
            $('#modifier-echeance-jours-container').show();
            $('#modifier-echeance-date').val('');
        }
    });
});

// ============================================
// FILTRES ET RECHERCHE
// ============================================
function filterByType() {
    var typeSelect = $('#filter-type').val();
    var url = '?p=effetCommerce&page=1';
    var searchInput = $('#search-operations').val().trim();
    
    if (searchInput) url += '&search=' + encodeURIComponent(searchInput);
    if (typeSelect) url += '&type=' + typeSelect;
    
    window.location.href = url;
}

function effectuerRecherche() {
    var searchTerm = $('#search-operations').val().trim();
    var typeSelect = $('#filter-type').val();
    var url = '?p=effetCommerce&page=1';
    
    if (searchTerm) url += '&search=' + encodeURIComponent(searchTerm);
    if (typeSelect) url += '&type=' + typeSelect;
    
    window.location.href = url;
}

function effacerRecherche() {
    window.location.href = '?p=effetCommerce&page=1';
}

// ============================================
// AJOUT
// ============================================
function ouvrirModalAjout() {
    var form = $('#form-ajout-operation')[0];
    if (form) $(form).find('.is-invalid').removeClass('is-invalid');
    
    // Date d'émission = aujourd'hui par défaut
    var aujourdhui = new Date();
    var jour = ('0' + aujourdhui.getDate()).slice(-2);
    var mois = ('0' + (aujourdhui.getMonth() + 1)).slice(-2);
    var annee = aujourdhui.getFullYear();
    $('#input-date-emission').val(jour + '/' + mois + '/' + annee);
    
    // Reset échéance
    $('#echeance-type').val('date');
    $('#echeance-date-container').show();
    $('#echeance-jours-container').hide();
    $('#echeance-date').val('');
    $('#echeance-jours').val('');
    
    $('#preview-scan').empty();
    $('#modal-ajout-operation').modal('show');
}

function validerFormulaireAjout() {
    var required = [
        { id: '#input-nom-tireur', name: 'Tireur' },
        { id: '#input-nom-tire', name: 'Tiré' },
        { id: '#input-date-emission', name: "Date d'émission" },
        { id: '#input-banque', name: 'Banque' }
    ];
    
    for (var i = 0; i < required.length; i++) {
        var $field = $(required[i].id);
        if (!$field.val().trim()) {
            $field.addClass('is-invalid').focus();
            notif('Le champ "' + required[i].name + '" est obligatoire', 'warning');
            return false;
        }
        $field.removeClass('is-invalid');
    }
    
    var echeanceType = $('#echeance-type').val();
    if (echeanceType === 'date') {
        if (!$('#echeance-date').val().trim()) {
            $('#echeance-date').addClass('is-invalid').focus();
            notif('La date d\'échéance est obligatoire', 'warning');
            return false;
        }
        $('#echeance-date').removeClass('is-invalid');
    } else {
        if (!$('#echeance-jours').val().trim() || parseInt($('#echeance-jours').val()) <= 0) {
            $('#echeance-jours').addClass('is-invalid').focus();
            notif('Le nombre de jours doit être positif', 'warning');
            return false;
        }
        $('#echeance-jours').removeClass('is-invalid');
    }
    
    var scanInput = $('#input-scan')[0];
    if (!scanInput || !scanInput.files || scanInput.files.length === 0) {
        $('#input-scan').addClass('is-invalid').focus();
        notif('Le scan du document est obligatoire', 'warning');
        return false;
    }
    
    if (scanInput.files[0].size > 5 * 1024 * 1024) {
        $('#input-scan').addClass('is-invalid').focus();
        notif('Le fichier est trop volumineux (max 5MB)', 'warning');
        return false;
    }
    $('#input-scan').removeClass('is-invalid');
    
    return true;
}

function envoyerAjout() {
    if (!validerFormulaireAjout()) return;
    
    var formData = new FormData($('#form-ajout-operation')[0]);
    
    // Conversion des dates
    var dateEmission = $('#input-date-emission').val().split('/');
    if (dateEmission.length === 3) {
        formData.set('date_emission', dateEmission[2] + '-' + dateEmission[1] + '-' + dateEmission[0]);
    }
    
    var echeanceDate = $('#echeance-date').val().split('/');
    if (echeanceDate.length === 3) {
        formData.set('echeance_date', echeanceDate[2] + '-' + echeanceDate[1] + '-' + echeanceDate[0]);
    }
    
    var btn = $('#btn-enregistrer-operation');
    var originalText = btn.html();
    btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Envoi...');
    
    $.ajax({
        url: 'index.php?p=effetCommerce.ajoutOperation',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.success) {
                $('#modal-ajout-operation').modal('hide');
                notif(response.message, 'success');
                setTimeout(function() { window.location.reload(); }, 1500);
            } else {
                notif(response.message, 'error');
            }
        },
        error: function() {
            notif('Erreur réseau', 'error');
        },
        complete: function() {
            btn.prop('disabled', false).html(originalText);
        }
    });
}

// ============================================
// MODIFICATION
// ============================================
function ouvrirModalModifier(operationId) {
    $('#form-modifier-operation .is-invalid').removeClass('is-invalid');
    $('#modifier-preview-scan').empty();
    $('#modifier-scan-actuel').empty();
    
    $.ajax({
        url: 'index.php?p=effetCommerce.getOperationData',
        type: 'POST',
        data: { operation_id: operationId },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                var op = response.operation;
                
                if (!response.canBeModified) {
                    notif('Cette opération ne peut plus être modifiée', 'error');
                    return;
                }
                
                if (!isAdminOrCompta && !response.isCreator) {
                    notif('Vous ne pouvez modifier que vos propres opérations', 'error');
                    return;
                }
                
                $('#modifier-operation-id').val(operationId);
                
                var typeDisplay = $('#modifier-type-operation-display');
                if (op.type_operation === 'billet_ordre') {
                    typeDisplay.html('<i class="fa fa-file-text" style="color:var(--primary);"></i> Billet à Ordre');
                    $('#modifier-input-type-operation').val('billet_ordre');
                } else {
                    typeDisplay.html('<i class="fa fa-exchange" style="color:var(--info);"></i> Lettre de Change');
                    $('#modifier-input-type-operation').val('lettre_change');
                }
                
                $('#modifier-input-nom-tireur').val(op.nom_tireur || '');
                $('#modifier-input-nom-tire').val(op.nom_tire || '');
                
                var dateEm = op.date_emission ? new Date(op.date_emission) : new Date();
                if (dateEm instanceof Date && !isNaN(dateEm)) {
                    $('#modifier-input-date-emission').val(
                        ('0' + dateEm.getDate()).slice(-2) + '/' + 
                        ('0' + (dateEm.getMonth() + 1)).slice(-2) + '/' + 
                        dateEm.getFullYear()
                    );
                }
                
                $('#modifier-input-banque').val(op.banque || '');
                
                // Échéance
                if (op.nb_jours && op.nb_jours > 0) {
                    $('#modifier-echeance-type').val('jours');
                    $('#modifier-echeance-date-container').hide();
                    $('#modifier-echeance-jours-container').show();
                    $('#modifier-echeance-jours').val(op.nb_jours);
                } else if (op.echeance) {
                    $('#modifier-echeance-type').val('date');
                    $('#modifier-echeance-date-container').show();
                    $('#modifier-echeance-jours-container').hide();
                    var echeance = new Date(op.echeance);
                    if (!isNaN(echeance)) {
                        $('#modifier-echeance-date').val(
                            ('0' + echeance.getDate()).slice(-2) + '/' + 
                            ('0' + (echeance.getMonth() + 1)).slice(-2) + '/' + 
                            echeance.getFullYear()
                        );
                    }
                }
                
                $('#modifier-input-observations').val(op.observations || '');
                
                if (op.scan_path) {
                    var fileName = op.scan_path.split('/').pop();
                    $('#modifier-scan-actuel').html(
                        '<div style="background:var(--info-light); padding:10px; border-radius:8px;">' +
                        '<i class="fa fa-file" style="color:var(--info);"></i> Document actuel: ' +
                        '<a href="' + escapeHtml(op.scan_path) + '" target="_blank">' + escapeHtml(fileName) + '</a><br>' +
                        '<small>Laissez vide pour conserver ce document</small>' +
                        '</div>'
                    );
                }
                
                $('#modal-modifier-operation').modal('show');
            } else {
                notif(response.message, 'error');
            }
        },
        error: function() {
            notif('Erreur de chargement', 'error');
        }
    });
}

function envoyerModification() {
    var formData = new FormData($('#form-modifier-operation')[0]);
    
    var dateEmission = $('#modifier-input-date-emission').val().split('/');
    if (dateEmission.length === 3) {
        formData.set('date_emission', dateEmission[2] + '-' + dateEmission[1] + '-' + dateEmission[0]);
    }
    
    var echeanceDate = $('#modifier-echeance-date').val().split('/');
    if (echeanceDate.length === 3) {
        formData.set('echeance_date', echeanceDate[2] + '-' + echeanceDate[1] + '-' + echeanceDate[0]);
    }
    
    var btn = $('#btn-modifier-operation');
    var originalText = btn.html();
    btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Modification...');
    
    $.ajax({
        url: 'index.php?p=effetCommerce.updateOperation',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.success) {
                $('#modal-modifier-operation').modal('hide');
                notif(response.message, 'success');
                setTimeout(function() { window.location.reload(); }, 1500);
            } else {
                notif(response.message, 'error');
            }
        },
        error: function() {
            notif('Erreur réseau', 'error');
        },
        complete: function() {
            btn.prop('disabled', false).html(originalText);
        }
    });
}

// ============================================
// CHANGEMENTS D'ÉTAT (Comptabilité)
// ============================================
function ouvrirModalChangerStatut(operationId) {
    if (!isCompta) { notif('Seule la comptabilité peut changer le statut', 'error'); return; }
    $('#statut-operation-id').val(operationId);
    $('#input-nouveau-statut').val('');
    $('#input-observation-statut').val('');
    $('.statut-option').removeClass('active');
    $('#modal-changement-statut').modal('show');
}

function ouvrirModalChangerEtatConfirmation(operationId) {
    if (!isCompta) { notif('Seule la comptabilité peut changer l\'état', 'error'); return; }
    $('#etat-confirmation-operation-id').val(operationId);
    $('#input-nouvel-etat-confirmation').val('');
    $('#input-observation-etat-conf').val('');
    $('.statut-option').removeClass('active');
    $('#modal-changement-etat-confirmation').modal('show');
}

function ouvrirModalChangerEtatValidation(operationId) {
    if (!isCompta) { notif('Seule la comptabilité peut changer l\'état', 'error'); return; }
    $('#etat-validation-operation-id').val(operationId);
    $('#input-nouvel-etat-validation').val('');
    $('#input-observation-etat-validation').val('');
    $('.statut-option').removeClass('active');
    $('#modal-changement-etat-validation').modal('show');
}

function envoyerChangement(url, data, modalId) {
    $.ajax({
        url: url,
        type: 'POST',
        data: data,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                $(modalId).modal('hide');
                notif(response.message, 'success');
                setTimeout(function() { window.location.reload(); }, 1500);
            } else {
                notif(response.message, 'error');
            }
        },
        error: function() {
            notif('Erreur réseau', 'error');
        }
    });
}

// ============================================
// DÉTAILS
// ============================================
function ouvrirModalDetails(operationId) {
    $('#details-loader').show();
    $('#details-content').empty();
    
    $.ajax({
        url: 'index.php?p=effetCommerce.getDetails',
        type: 'POST',
        data: { operation_id: operationId },
        dataType: 'json',
        success: function(response) {
            $('#details-loader').hide();
            if (response.success) {
                $('#details-content').html(response.html);
            } else {
                $('#details-content').html('<div class="alert alert-danger">' + response.message + '</div>');
            }
        },
        error: function() {
            $('#details-loader').hide();
            $('#details-content').html('<div class="alert alert-danger">Erreur de chargement</div>');
        }
    });
    
    $('#modal-details-operation').modal('show');
}

// ============================================
// GÉNÉRATION CARTE
// ============================================
function ouvrirModalGenererCarte(operationId) {
    $('#carte-operation-id').val(operationId);
    $('#input-titre-carte').val('Carte de suivi Effet #' + operationId);
    $('#modal-generer-carte').modal('show');
}

function genererCartePNG() {
    var titre = $('#input-titre-carte').val().trim();
    if (!titre) {
        notif('Veuillez saisir un titre', 'warning');
        return;
    }
    
    var btn = $('#btn-generer-carte');
    var originalText = btn.html();
    btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Génération...');
    
    var formData = new FormData($('#form-generer-carte')[0]);
    
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'index.php?p=effetCommerce.genererCartePNG');
    xhr.responseType = 'blob';
    
    xhr.onload = function() {
        if (this.status === 200) {
            var blob = new Blob([this.response], { type: 'image/png' });
            var url = window.URL.createObjectURL(blob);
            var a = document.createElement('a');
            a.href = url;
            a.download = 'carte_effet_' + $('#carte-operation-id').val() + '.png';
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            document.body.removeChild(a);
            
            $('#modal-generer-carte').modal('hide');
            notif('Carte générée avec succès', 'success');
        } else {
            notif('Erreur lors de la génération', 'error');
        }
        btn.prop('disabled', false).html(originalText);
    };
    
    xhr.onerror = function() {
        btn.prop('disabled', false).html(originalText);
        notif('Erreur réseau', 'error');
    };
    
    xhr.send(formData);
}

// ============================================
// INITIALISATION
// ============================================
$(document).ready(function() {
    console.log('🚀 Module Effet de Commerce chargé');
    
    // Datepicker
    if ($.fn.datepicker) {
        $('.datepicker-operation, .datepicker-operation-modifier').datepicker({
            format: 'dd/mm/yyyy',
            language: 'fr',
            autoclose: true,
            todayHighlight: true
        });
        
        $('.btn-calendrier, .btn-calendrier-modifier').click(function(e) {
            e.preventDefault();
            var input = $(this).closest('div').find('input[type="text"]');
            if (input.length) {
                input.datepicker('show');
            }
        });
    }
    
    // Événements
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
    
    $('#filter-type').change(filterByType);
    $('#btn-search').click(effectuerRecherche);
    $('#search-operations').keypress(function(e) {
        if (e.which === 13) effectuerRecherche();
    });
    $('#btn-clear-search').click(effacerRecherche);
    
    // Gestion des actions sur les lignes
    $(document).on('click', '.details-operation', function(e) {
        e.preventDefault();
        ouvrirModalDetails($(this).data('id'));
    });
    
    $(document).on('click', '.generer-carte', function(e) {
        e.preventDefault();
        ouvrirModalGenererCarte($(this).data('id'));
    });
    
    $(document).on('click', '.modifier-operation', function(e) {
        e.preventDefault();
        ouvrirModalModifier($(this).data('id'));
    });
    
    $(document).on('click', '.changer-statut', function(e) {
        e.preventDefault();
        ouvrirModalChangerStatut($(this).data('id'));
    });
    
    $(document).on('click', '.changer-etat-confirmation', function(e) {
        e.preventDefault();
        ouvrirModalChangerEtatConfirmation($(this).data('id'));
    });
    
    $(document).on('click', '.changer-etat-validation', function(e) {
        e.preventDefault();
        ouvrirModalChangerEtatValidation($(this).data('id'));
    });
    
    // Gestion des options de statut
    $('.statut-option').click(function() {
        $('.statut-option').removeClass('active');
        $(this).addClass('active');
        var modal = $(this).closest('.modal');
        var value = $(this).data('value');
        
        if (modal.is('#modal-changement-statut')) {
            $('#input-nouveau-statut').val(value);
        } else if (modal.is('#modal-changement-etat-confirmation')) {
            $('#input-nouvel-etat-confirmation').val(value);
        } else if (modal.is('#modal-changement-etat-validation')) {
            $('#input-nouvel-etat-validation').val(value);
        }
    });
    
    // Boutons d'envoi
    $('#btn-enregistrer-statut').click(function(e) {
        e.preventDefault();
        var operationId = $('#statut-operation-id').val();
        var nouvelEtat = $('#input-nouveau-statut').val();
        var observation = $('#input-observation-statut').val();
        
        if (!nouvelEtat) { notif('Veuillez sélectionner un statut', 'warning'); return; }
        if (!observation.trim()) { notif('L\'observation est obligatoire', 'warning'); return; }
        
        envoyerChangement('index.php?p=effetCommerce.changerStatut', {
            operation_id: operationId,
            nouvel_etat: nouvelEtat,
            observation: observation
        }, '#modal-changement-statut');
    });
    
    $('#btn-enregistrer-etat-confirmation').click(function(e) {
        e.preventDefault();
        var operationId = $('#etat-confirmation-operation-id').val();
        var nouvelEtat = $('#input-nouvel-etat-confirmation').val();
        var observation = $('#input-observation-etat-conf').val();
        
        if (!nouvelEtat) { notif('Veuillez sélectionner un état', 'warning'); return; }
        if (!observation.trim()) { notif('L\'observation est obligatoire', 'warning'); return; }
        
        envoyerChangement('index.php?p=effetCommerce.changerEtatConfirmation', {
            operation_id: operationId,
            nouvel_etat: nouvelEtat,
            observation: observation
        }, '#modal-changement-etat-confirmation');
    });
    
    $('#btn-enregistrer-etat-validation').click(function(e) {
        e.preventDefault();
        var operationId = $('#etat-validation-operation-id').val();
        var nouvelEtat = $('#input-nouvel-etat-validation').val();
        var observation = $('#input-observation-etat-validation').val();
        
        if (!nouvelEtat) { notif('Veuillez sélectionner un état', 'warning'); return; }
        if (!observation.trim()) { notif('L\'observation est obligatoire', 'warning'); return; }
        
        envoyerChangement('index.php?p=effetCommerce.changerEtatValidation', {
            operation_id: operationId,
            nouvel_etat: nouvelEtat,
            observation: observation
        }, '#modal-changement-etat-validation');
    });
    
    $('#btn-generer-carte').click(function(e) {
        e.preventDefault();
        genererCartePNG();
    });
    
    // Prévisualisation scan
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
                    '<img src="' + e.target.result + '" style="max-width:200px; max-height:150px;">' +
                    '<div style="margin-top:5px;">' + escapeHtml(file.name) + ' (' + Math.round(file.size/1024) + ' KB)</div>' +
                    '</div>'
                );
            };
            reader.readAsDataURL(file);
        } else {
            preview.html(
                '<div style="background:var(--info-light); padding:10px; border-radius:8px;">' +
                '<i class="fa fa-file" style="color:var(--info);"></i> ' + escapeHtml(file.name) + ' (' + Math.round(file.size/1024) + ' KB)' +
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
                    '<img src="' + e.target.result + '" style="max-width:200px; max-height:150px;">' +
                    '<div style="margin-top:5px;">Nouveau: ' + escapeHtml(file.name) + ' (' + Math.round(file.size/1024) + ' KB)</div>' +
                    '</div>'
                );
            };
            reader.readAsDataURL(file);
        } else {
            preview.html(
                '<div style="background:var(--info-light); padding:10px; border-radius:8px;">' +
                '<i class="fa fa-file" style="color:var(--info);"></i> Nouveau: ' + escapeHtml(file.name) + ' (' + Math.round(file.size/1024) + ' KB)' +
                '</div>'
            );
        }
    });
});

function naviguerVers(url) {
    if (url) window.location.href = url;
    return false;
}
</script>

<?php
function genererLigneEffetModerne($effet, $user, $agenceNom, $isAdminOrCompta = false, $isCompta = false, $allowedToAdd = false) {
    if (!is_array($effet)) return '';
    
    // Statut
    $statusClass = 'status-en-cours';
    $statut = isset($effet['statut']) ? strtolower($effet['statut']) : 'en cours';
    if ($statut === 'confirmé' || $statut === 'confirme') {
        $statusClass = 'status-termine';
        $statut = 'Confirmé';
    } elseif ($statut === 'annulé' || $statut === 'annule') {
        $statusClass = 'status-annule';
        $statut = 'Annulé';
    } else {
        $statut = 'En cours';
    }
    
    // États
    $etatConfText = isset($effet['etat_confirmation']) ? $effet['etat_confirmation'] : 'Non';
    $etatValidText = isset($effet['etat_validation']) ? $effet['etat_validation'] : 'Non';
    
    // Données
    $id = isset($effet['id']) ? $effet['id'] : 0;
    $type = isset($effet['type_operation']) ? $effet['type_operation'] : 'billet_ordre';
    $tireur = htmlspecialchars(isset($effet['nom_tireur']) ? $effet['nom_tireur'] : '');
    $tire = htmlspecialchars(isset($effet['nom_tire']) ? $effet['nom_tire'] : '');
    $numero = htmlspecialchars(isset($effet['numero']) ? $effet['numero'] : '');
    $banque = htmlspecialchars(isset($effet['banque']) ? $effet['banque'] : '');
    
    // Dates (gardées pour les détails mais plus affichées dans le tableau)
    $date_emission = '';
    if (isset($effet['date_emission'])) {
        if ($effet['date_emission'] instanceof DateTime) {
            $date_emission = $effet['date_emission']->format('d/m/Y');
        } elseif (is_string($effet['date_emission'])) {
            $date_emission = date('d/m/Y', strtotime($effet['date_emission']));
        }
    }
    
    $echeance = '';
    if (isset($effet['echeance'])) {
        if ($effet['echeance'] instanceof DateTime) {
            $echeance = $effet['echeance']->format('d/m/Y');
        } elseif (is_string($effet['echeance'])) {
            $echeance = date('d/m/Y', strtotime($effet['echeance']));
        }
        if (!empty($effet['nb_jours'])) {
            $echeance .= '<br><small class="text-muted">(' . $effet['nb_jours'] . ' j)</small>';
        }
    }
    
    // Icône
    $typeIcon = '';
    if ($type === 'lettre_change') {
        $typeIcon = '<i class="fa fa-exchange type-lc" title="Lettre de Change"></i>';
    } else {
        $typeIcon = '<i class="fa fa-file-text type-bo" title="Billet à Ordre"></i>';
    }
    
    // Vérifications
    $canBeModified = ($statut === 'En cours' && $etatConfText === 'Non' && $etatValidText === 'Non');
    
    $userId = isset($user['idUser']) ? $user['idUser'] : (isset($user['id']) ? $user['id'] : null);
    $operationCreatedBy = isset($effet['created_by']) ? $effet['created_by'] : null;
    $isCreator = (empty($operationCreatedBy) || $operationCreatedBy == $userId);
    
    $html = '<tr id="effet-' . $id . '">';
    $html .= '<td class="text-center">' . $typeIcon . '</td>';
    $html .= '<td class="text-center"><span style="font-size:11px; font-weight:600;">' . $numero . '</span></td>';
    $html .= '<td><strong>' . $tireur . '</strong></td>';
    $html .= '<td>' . $tire . '</td>';
    $html .= '<td class="text-center">' . $echeance . '</td>';
    $html .= '<td>' . $banque . '</td>';
    $html .= '<td class="text-center"><span class="status-badge ' . $statusClass . '">' . $statut . '</span></td>';
    $html .= '<td class="text-center"><span class="status-badge ' . ($etatConfText === 'Oui' ? 'status-termine' : 'status-en-cours') . '">' . $etatConfText . '</span></td>';
    $html .= '<td class="text-center"><span class="status-badge ' . ($etatValidText === 'Oui' ? 'status-termine' : 'status-en-cours') . '">' . $etatValidText . '</span></td>';
    $html .= '<td>' . htmlspecialchars($agenceNom) . '</td>';
    $html .= '<td class="text-center"><div class="action-icons">';
    
    // Détails - toujours visible
    $html .= '<a href="#" class="details-operation action-link purple" data-id="' . $id . '" title="Détails"><i class="fa fa-info-circle"></i></a>';
    
    // Générer carte - toujours visible
    $html .= '<a href="#" class="generer-carte action-link green" data-id="' . $id . '" title="Générer carte PNG"><i class="fa fa-credit-card"></i></a>';
    
    // Modifier (si autorisé)
    if ($allowedToAdd && $canBeModified && ($isAdminOrCompta || $isCreator)) {
        $html .= '<a href="#" class="modifier-operation action-link blue" data-id="' . $id . '" title="Modifier"><i class="fa fa-pencil"></i></a>';
    }
    
    // Actions Comptabilité
    if ($isCompta) {
        if ($statut === 'En cours') {
            $html .= '<a href="#" class="changer-statut action-link orange" data-id="' . $id . '" title="Changer statut"><i class="fa fa-exchange"></i></a>';
        }
        if ($etatConfText === 'Non' && $statut === 'En cours') {
            $html .= '<a href="#" class="changer-etat-confirmation action-link green" data-id="' . $id . '" title="Changer état confirmation"><i class="fa fa-check-circle"></i></a>';
        }
        if ($etatValidText === 'Non' && $statut === 'En cours') {
            $html .= '<a href="#" class="changer-etat-validation action-link blue" data-id="' . $id . '" title="Changer état validation"><i class="fa fa-check-square-o"></i></a>';
        }
    }
    
    $html .= '</div></td></tr>';
    
    return $html;
}
?>