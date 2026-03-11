<?php
use Core\Model\App;
use Core\Model\Session;
use Core\Database\Ramassage;

$session = Session::getInstance();
$user = $_SESSION['user'];

// Récupération des paramètres
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Récupération de tous les ramassages ou ceux filtrés par recherche
if (!empty($search)) {
    $ramassages = Ramassage::getAll();
    if (!is_array($ramassages)) {
        $ramassages = array();
    }
    
    $ramassagesFiltres = array();
    foreach ($ramassages as $ramassage) {
        if (is_array($ramassage)) {
            $found = false;
            $fieldsToSearch = ['periode', 'entite_nom', 'agence_nom', 'created_by_name', 'observations'];
            
            foreach ($fieldsToSearch as $field) {
                if (isset($ramassage[$field]) && 
                    stripos($ramassage[$field], $search) !== false) {
                    $found = true;
                    break;
                }
            }
            
            $dateFields = ['date_debut', 'date_fin', 'date_creation'];
            foreach ($dateFields as $field) {
                if (isset($ramassage[$field])) {
                    $dateStr = is_string($ramassage[$field]) ? $ramassage[$field] : '';
                    if ($dateStr && stripos($dateStr, $search) !== false) {
                        $found = true;
                        break;
                    }
                }
            }
            
            if ($found) {
                $ramassagesFiltres[] = $ramassage;
            }
        }
    }
    $ramassages = $ramassagesFiltres;
} else {
    $ramassages = Ramassage::getAll();
    if (!is_array($ramassages)) {
        $ramassages = array();
    }
}

// PAGINATION MANUELLE
$itemsPerPage = 10;
$totalRamassages = count($ramassages);
$totalPages = ceil($totalRamassages / $itemsPerPage);

if ($page < 1) $page = 1;
if ($totalPages > 0 && $page > $totalPages) $page = $totalPages;

$startIndex = ($page - 1) * $itemsPerPage;
$endIndex = min($startIndex + $itemsPerPage, $totalRamassages);
$ramassagesPagines = array_slice($ramassages, $startIndex, $itemsPerPage);

$allowedToAdd = in_array($user['privilege'], ['Administration', 'SuperAdministration']);

// Récupération de la liste des agences pour le select - METHODE DIRECTE
$agences = array();
try {
    $sqlAgences = "SELECT [idAgence], [designation] FROM [dbo].[Tb_Agence] ORDER BY [designation]";
    $resultAgences = Ramassage::querySelect($sqlAgences);
    
    if (is_resource($resultAgences)) {
        while ($row = sqlsrv_fetch_array($resultAgences, SQLSRV_FETCH_ASSOC)) {
            $agences[] = $row;
        }
        sqlsrv_free_stmt($resultAgences);
    } elseif (is_array($resultAgences)) {
        $agences = $resultAgences;
    }
} catch (\Exception $e) {
    $agences = array();
    error_log("Erreur récupération agences: " . $e->getMessage());
}
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

.ramassages-container { max-width:100%; margin:0 auto; font-family:'Inter',sans-serif; }
.modern-header-banner { background:white; border-radius:var(--radius); padding:20px 25px; margin-bottom:25px; color:var(--dark); box-shadow:var(--shadow); border:1px solid var(--gray-border); }
.banner-title { font-size:18px; font-weight:700; margin-bottom:10px; display:flex; align-items:center; gap:10px; color:var(--primary); }
.banner-subtitle { font-size:13px; color:var(--gray); margin-bottom:15px; }

.stats-grid { display:flex; flex-wrap:wrap; gap:15px; margin-top:15px; }
.stat-item { background:var(--gray-light); padding:10px 15px; border-radius:var(--radius); display:flex; align-items:center; gap:10px; }
.stat-value { font-size:20px; font-weight:700; color:var(--primary); }
.stat-label { font-size:12px; color:var(--gray); }

.legend-grid { display:flex; flex-wrap:wrap; gap:12px; margin-top:15px; }
.legend-item { display:flex; align-items:center; gap:8px; font-size:12px; background:var(--gray-light); padding:6px 10px; border-radius:6px; color:var(--dark); }
.legend-color { width:14px; height:14px; border-radius:3px; }
.legend-color.green { background:var(--success); }
.legend-color.orange { background:var(--warning); }
.legend-color.red { background:var(--danger); }
.legend-color.purple { background:#8e44ad; }

.actions-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(200px, 1fr)); gap:12px; margin-bottom:20px; }
.modern-action-btn { display:flex; flex-direction:column; align-items:center; padding:15px 10px; background:white; border:1px solid var(--gray-border); border-radius:var(--radius); text-decoration:none; color:var(--dark); transition:all 0.2s ease; box-shadow:var(--shadow); }
.modern-action-btn:hover { transform:translateY(-2px); box-shadow:var(--shadow-hover); border-color:var(--primary); text-decoration:none; color:var(--dark); }
.btn-icon { font-size:18px; margin-bottom:8px; }
.btn-text { font-weight:600; font-size:13px; }
.btn-subtext { font-size:10px; color:var(--gray); margin-top:3px; }
.add-ramassage-btn { background:linear-gradient(135deg, var(--primary), var(--primary-dark)); border:none; color:white; }
.add-ramassage-btn:hover { color:white; }

.modern-table-container { background:white; border-radius:var(--radius); padding:20px; box-shadow:var(--shadow); margin-bottom:30px; border:1px solid var(--gray-border); }
.table-header-modern { display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; padding-bottom:15px; border-bottom:1px solid var(--gray-border); flex-wrap:wrap; gap:15px; }
.table-title { font-size:16px; font-weight:600; color:var(--dark); display:flex; align-items:center; gap:10px; }

/* Barre de recherche moderne */
.search-wrapper {
    width: 400px;
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

#dynamic-table { width:100% !important; font-size:13px; }
#dynamic-table thead th { background:var(--gray-light); color:var(--dark); font-weight:600; font-size:11px; padding:12px 8px; }
#dynamic-table tbody td { padding:12px 8px; border-bottom:1px solid var(--gray-border); vertical-align:middle; }
#dynamic-table tbody tr:hover { background:var(--gray-light); }

.status-badge { display:inline-flex; align-items:center; gap:5px; padding:4px 10px; border-radius:12px; font-size:11px; font-weight:600; }
.status-valide { background:var(--success-light); color:#13863c; }
.status-expire-bientot { background:var(--warning-light); color:#b36b00; }
.status-expire { background:var(--danger-light); color:#c0392b; }

.entite-badge {
    display:inline-block;
    background:var(--primary-light);
    color:var(--primary);
    padding:4px 8px;
    border-radius:12px;
    font-weight:600;
    font-size:12px;
}
.entite-badge i {
    margin-right:4px;
}

.action-icons { display:flex; gap:8px; flex-wrap: wrap; justify-content: center; }
.action-link { display:inline-flex; align-items:center; justify-content:center; text-decoration:none; width:32px; height:32px; border-radius:4px; transition:all 0.2s ease; }
.action-link:hover { transform:translateY(-2px); box-shadow:var(--shadow); text-decoration:none; }
.action-link.purple i { color:#8e44ad; }
.action-link.green i { color:var(--success); }
.action-link.teal i { color:#008080; }

.modal-header.modern-modal-header { background:linear-gradient(135deg,#0d1b3e,#1a2b5c); padding:20px 25px; border-radius:12px 12px 0 0; }
.modern-modal-header .modal-title { color:white; font-weight:600; font-size:16px; display:flex; align-items:center; gap:12px; }
.modern-modal-header .close { color:white; opacity:0.8; width:32px; height:32px; background:rgba(255,255,255,0.1); border-radius:50%; display:flex; align-items:center; justify-content:center; border:none; }
.modern-modal-header .close:hover { opacity:1; background:rgba(255,255,255,0.2); }
.modal-body.modern-modal-body { padding:25px; background:#f8fafc; max-height:70vh; overflow-y:auto; }
.modal-footer.modern-modal-footer { padding:20px 25px; background:white; border-top:1px solid #e9ecef; border-radius:0 0 12px 12px; display:flex; gap:10px; justify-content:flex-end; }

.modern-form-control { width:100%; padding:12px 15px; border:1px solid #dee2e6; border-radius:8px; font-size:14px; transition:all 0.3s ease; margin-bottom:15px; background:white; }
.modern-form-control:focus { border-color:#4361ee; box-shadow:0 0 0 3px rgba(67,97,238,0.1); outline:none; }
.modern-form-control[readonly] { background:var(--gray-light); cursor:not-allowed; }
.modern-form-select { width:100%; padding:12px 15px; border:1px solid #dee2e6; border-radius:8px; font-size:14px; transition:all 0.3s ease; margin-bottom:15px; background:white; height:46px; }
.modern-form-select:focus { border-color:#4361ee; box-shadow:0 0 0 3px rgba(67,97,238,0.1); outline:none; }
.modern-form-label { font-weight:600; font-size:13px; color:var(--dark); margin-bottom:5px; display:block; }
.modern-form-label i { margin-right:5px; color:var(--primary); }
.modern-form-label .required { color:var(--danger); margin-left:3px; }

.btn-modern { padding:10px 24px; border-radius:8px; font-weight:600; font-size:14px; border:none; cursor:pointer; transition:all 0.3s ease; display:inline-flex; align-items:center; gap:8px; }
.btn-modern-primary { background:linear-gradient(135deg,#4361ee,#3a56d4); color:white; }
.btn-modern-primary:hover { background:linear-gradient(135deg,#3a56d4,#2c3e50); color:white; }
.btn-modern-secondary { background:#f8f9fa; color:#6c757d; border:1px solid #dee2e6; }
.btn-modern-secondary:hover { background:#e9ecef; }

.modern-loader { text-align:center; padding:40px 20px; }
.modern-loader i { font-size:32px; color:var(--primary); margin-bottom:15px; animation:spin 1s linear infinite; }
@keyframes spin { 100% { transform:rotate(360deg); } }

.modern-notification { position:fixed; top:20px; right:20px; padding:15px 20px; border-radius:var(--radius); z-index:10001; font-size:14px; transform:translateX(100%); opacity:0; transition:all 0.3s ease; box-shadow:var(--shadow-hover); cursor:pointer; }
.modern-notification.show { transform:translateX(0); opacity:1; }
.modern-notification.success { background:var(--success-light); color:#13863c; border-left:4px solid var(--success); }
.modern-notification.error { background:var(--danger-light); color:#c0392b; border-left:4px solid var(--danger); }
.modern-notification.warning { background:var(--warning-light); color:#b36b00; border-left:4px solid var(--warning); }

.empty-state { text-align:center; padding:40px; }
.empty-state i { font-size:48px; color:var(--gray); margin-bottom:15px; }
.empty-state h4 { color:var(--dark); margin-bottom:10px; }
.empty-state p { color:var(--gray); }

.file-preview { margin-top:10px; padding:10px; border-radius:8px; background:white; border:1px dashed #dee2e6; }

/* Pagination */
.pagination-modern { display:flex; justify-content:flex-end; gap:5px; margin-top:20px; }
.pagination-item { display:inline-flex; align-items:center; justify-content:center; min-width:36px; height:36px; padding:0 6px; border-radius:8px; background:white; border:1px solid #dee2e6; color:var(--dark); text-decoration:none; transition:all 0.2s ease; }
.pagination-item:hover { background:var(--primary-light); border-color:var(--primary); color:var(--primary); text-decoration:none; }
.pagination-item.active { background:var(--primary); border-color:var(--primary); color:white; }
.pagination-item.disabled { opacity:0.5; pointer-events:none; }
.pagination-info { padding:8px 0; color:var(--gray); }

/* Styles pour les détails */
.details-card { background:white; border-radius:8px; padding:20px; margin-bottom:15px; border:1px solid var(--gray-border); }
.details-row { display:flex; padding:10px 0; border-bottom:1px solid var(--gray-border); }
.details-label { width:150px; font-weight:600; color:var(--dark); }
.details-value { flex:1; color:var(--gray); }
.details-value a { color:var(--primary); text-decoration:none; }
.details-value a:hover { text-decoration:underline; }

/* FontAwesome Fixes pour FA4 */
.fa {
    font-family: FontAwesome !important;
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
}
@media (max-width:576px) { 
    .modern-header-banner,.modern-table-container { padding:15px; } 
    .actions-grid { grid-template-columns:1fr; } 
    .modal-footer.modern-modal-footer { flex-direction:column; } 
    .btn-modern { width:100%; justify-content:center; } 
    .stats-grid { flex-direction:column; }
    .stat-item { width:100%; }
}
</style>

<div class="ramassages-container">
    <!-- Fil d'Ariane moderne -->
    <div class="modern-breadcrumbs" style="margin-bottom:15px;">
        <ul class="breadcrumb" style="background:none; padding:0;">
            <li style="display:inline-block;">
                <i class="fa fa-home" style="color:var(--primary);"></i>
                <a href="#" onclick="return naviguerVers('<?php echo App::url("dashboard"); ?>')" style="color:var(--dark);">Arrêts Caisses</a>
            </li>
            <li style="display:inline-block; margin-left:8px;">
                <i class="fa fa-angle-right" style="color:var(--gray);"></i>
                Plans de Ramassage
            </li>
            <?php if (!empty($search)): ?>
            <li style="display:inline-block; margin-left:8px;">
                <i class="fa fa-angle-right" style="color:var(--gray);"></i>
                <i class="fa fa-search" style="color:var(--warning);"></i>
                Recherche: "<?php echo htmlspecialchars($search); ?>"
            </li>
            <?php endif; ?>
        </ul>
    </div>

    <!-- Header avec statistiques -->
    <div class="modern-header-banner">
        <h1 class="banner-title"><i class="fa fa-truck"></i> PLANS DE RAMASSAGE</h1>
        <p class="banner-subtitle">Gestion des plans de ramassage • Suivi des validités</p>
        
        <div class="stats-grid">
            <div class="stat-item">
                <i class="fa fa-line-chart" style="color:var(--primary);"></i>
                <div>
                    <div class="stat-value"><?php echo $totalRamassages; ?></div>
                    <div class="stat-label">Total plans</div>
                </div>
            </div>
        </div>
        
        <div class="legend-grid">
            <div class="legend-item"><div class="legend-color green"></div><span>Valide</span></div>
            <div class="legend-item"><div class="legend-color orange"></div><span>Expire bientôt</span></div>
            <div class="legend-item"><div class="legend-color red"></div><span>Expiré</span></div>
            <div class="legend-item"><i class="fa fa-info-circle" style="color:#8e44ad;"></i><span>Détails</span></div>
        </div>
    </div>

    <!-- Actions -->
    <div class="actions-section">
        <div class="actions-grid">
            <?php if($allowedToAdd) : ?>
            <a href="#" class="modern-action-btn add-ramassage-btn" id="btn-ajouter-ramassage">
                <i class="fa fa-plus btn-icon"></i>
                <span class="btn-text">Ajouter</span>
                <span class="btn-subtext">Nouveau plan</span>
            </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Barre de recherche -->
    <div style="margin-bottom:20px;">
        <div style="display:flex; flex-wrap:wrap; gap:15px; align-items:flex-end;">
            <div style="flex:1; min-width:300px;">
                <label class="modern-form-label"><i class="fa fa-search"></i> Recherche</label>
                <div class="search-wrapper">
                    <div class="search-box">
                        <span class="search-icon"><i class="fa fa-search" style="color:#95a5a6;"></i></span>
                        <input type="text" id="search-ramassages" class="search-input" 
                               placeholder="Période, entité, agence, observations..."
                               value="<?php echo htmlspecialchars($search); ?>">
                        <button class="search-btn" id="btn-search">
                            <i class="fa fa-arrow-right"></i>
                        </button>
                    </div>
                    <?php if (!empty($search)): ?>
                    <button class="clear-search" id="btn-clear-search" style="margin-top:10px;">
                        <i class="fa fa-times-circle"></i> Effacer la recherche
                    </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="modern-table-container">
        <div class="table-header-modern">
            <div class="table-title">
                <i class="fa fa-list"></i> 
                Liste des plans de ramassage
                <?php if (!empty($search)): ?>
                    (<?php echo $totalRamassages; ?> résultat<?php echo $totalRamassages > 1 ? 's' : ''; ?>)
                <?php else: ?>
                    (<?php echo $totalRamassages; ?>)
                <?php endif; ?>
            </div>
            <div class="table-tools">
                <span class="badge"><i class="fa fa-refresh fa-sm"></i> Actualisé à <?php echo date('H:i'); ?></span>
            </div>
        </div>
        <div class="table-responsive">
            <table id="dynamic-table" class="table table-hover">
                <thead>
                    <tr>
                        <th class="text-center" width="90">Date Création</th>
                        <th class="text-left">Entité (qui ramasse)</th>
                        <th class="text-left">Agence à ramasser</th>
                        <th class="text-left">Période</th>
                        <th class="text-center" width="80">Date début</th>
                        <th class="text-center" width="80">Date fin</th>
                        <th class="text-center" width="100">État validité</th>
                        <th class="text-center" width="100">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($ramassagesPagines)) : ?>
                        <?php foreach($ramassagesPagines as $ramassage) : 
                            if (!is_array($ramassage)) continue;
                            echo genererLigneRamassageModerne($ramassage, $user);
                        endforeach; 
                        ?>
                    <?php else : ?>
                    <tr>
                        <td colspan="8" class="text-center">
                            <div class="empty-state">
                                <i class="fa fa-truck"></i>
                                <h4>Aucun plan de ramassage trouvé</h4>
                                <p>
                                    <?php if (!empty($search)): ?>
                                        Aucun résultat pour "<?php echo htmlspecialchars($search); ?>"
                                    <?php else: ?>
                                        Commencez par ajouter un nouveau plan
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
                Affichage de <?php echo ($totalRamassages > 0 ? $startIndex + 1 : 0); ?> à <?php echo $endIndex; ?> sur <?php echo $totalRamassages; ?> entrées
            </div>
            <div class="pagination-modern">
                <?php
                if ($page > 1) {
                    $prevUrl = '?p=ramassage&page=' . ($page - 1);
                    if (!empty($search)) $prevUrl .= '&search=' . urlencode($search);
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
                    $firstUrl = '?p=ramassage&page=1';
                    if (!empty($search)) $firstUrl .= '&search=' . urlencode($search);
                    echo '<a href="' . $firstUrl . '" class="pagination-item">1</a>';
                    if ($startPage > 2) {
                        echo '<span class="pagination-item disabled">...</span>';
                    }
                }
                
                for ($i = $startPage; $i <= $endPage; $i++) {
                    if ($i == $page) {
                        echo '<span class="pagination-item active">' . $i . '</span>';
                    } else {
                        $pageUrl = '?p=ramassage&page=' . $i;
                        if (!empty($search)) $pageUrl .= '&search=' . urlencode($search);
                        echo '<a href="' . $pageUrl . '" class="pagination-item">' . $i . '</a>';
                    }
                }
                
                if ($endPage < $totalPages) {
                    if ($endPage < $totalPages - 1) {
                        echo '<span class="pagination-item disabled">...</span>';
                    }
                    $lastUrl = '?p=ramassage&page=' . $totalPages;
                    if (!empty($search)) $lastUrl .= '&search=' . urlencode($search);
                    echo '<a href="' . $lastUrl . '" class="pagination-item">' . $totalPages . '</a>';
                }
                
                if ($page < $totalPages) {
                    $nextUrl = '?p=ramassage&page=' . ($page + 1);
                    if (!empty($search)) $nextUrl .= '&search=' . urlencode($search);
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

<!-- MODAL AJOUT RAMASSAGE - VERSION ÉLARGIE ET OPTIMISÉE -->
<div id="modal-ajout-ramassage" class="modal fade" style="z-index: 1050 !important;">
    <div class="modal-dialog modal-lg" style="margin: 80px 20px 20px 280px !important; max-width: 1000px !important;">
        <div class="modal-content">
            <div class="modal-header modern-modal-header">
                <h5 class="modal-title"><i class="fa fa-plus-circle"></i> Ajouter un Plan de Ramassage</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body modern-modal-body" style="max-height: 70vh; overflow-y: auto; padding: 25px;">
                <form id="form-ajout-ramassage" enctype="multipart/form-data" autocomplete="off">
                    <input type="hidden" name="created_by" value="<?php echo isset($user['idUser']) ? $user['idUser'] : (isset($user['id']) ? $user['id'] : ''); ?>">
                    
                    <div class="alert alert-info" style="background:var(--info-light); border-left:4px solid var(--info); padding:15px; border-radius:8px; margin-bottom:20px;">
                        <i class="fa fa-info-circle" style="color:var(--info);"></i> 
                        Tous les champs avec <span class="required">*</span> sont obligatoires
                    </div>
                    
                    <!-- Ligne 1: Entité et Agence -->
                    <div style="display: flex; gap: 20px; margin-bottom: 15px;">
                        <div style="flex: 1;">
                            <label class="modern-form-label"><i class="fa fa-building"></i> Entité (qui ramasse) <span class="required">*</span></label>
                            <input type="text" class="modern-form-control" id="input-entite" name="entite_nom" 
                                   placeholder="Ex: DIRECTION REGIONALE, SERVICE COMPTABILITE" required style="width: 100%;">
                            <small class="text-muted">Saisie en majuscules automatique</small>
                        </div>
                        <div style="flex: 1;">
                            <label class="modern-form-label"><i class="fa fa-building-o"></i> Agence à ramasser <span class="required">*</span></label>
                            <select class="modern-form-select" id="input-agence" name="agence_nom" required style="width: 100%; height: 46px;">
                                <option value="">Sélectionnez une agence</option>
                                <?php
                                if (!empty($agences)) {
                                    foreach ($agences as $agence) {
                                        $designation = isset($agence['designation']) ? $agence['designation'] : '';
                                        if (!empty($designation)) {
                                            echo '<option value="' . htmlspecialchars($designation) . '">' . htmlspecialchars($designation) . '</option>';
                                        }
                                    }
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Ligne 2: Période -->
                    <div style="margin-bottom: 15px;">
                        <label class="modern-form-label"><i class="fa fa-calendar"></i> Période <span class="required">*</span></label>
                        <input type="text" class="modern-form-control" id="input-periode" name="periode" 
                               placeholder="Ex: JANVIER 2024, SEMAINE 5" required style="width: 100%;">
                    </div>
                    
                    <!-- Ligne 3: Dates (côte à côte) -->
                    <div style="display: flex; gap: 20px; margin-bottom: 15px;">
                        <div style="flex: 1;">
                            <label class="modern-form-label"><i class="fa fa-calendar-plus-o"></i> Date de début <span class="required">*</span></label>
                            <div class="input-group" style="display:flex;">
                                <input type="text" class="modern-form-control datepicker-ramassage" id="input-date-debut" name="date_debut" placeholder="JJ/MM/AAAA" required style="flex: 1; border-radius:8px 0 0 8px; margin-bottom:0;">
                                <span class="btn-calendrier-debut" style="background:var(--gray-light); border:1px solid #dee2e6; border-left:none; border-radius:0 8px 8px 0; padding:0 15px; display:flex; align-items:center; cursor:pointer;">
                                    <i class="fa fa-calendar" style="color:var(--primary);"></i>
                                </span>
                            </div>
                        </div>
                        <div style="flex: 1;">
                            <label class="modern-form-label"><i class="fa fa-calendar-times-o"></i> Date de fin <span class="required">*</span></label>
                            <div class="input-group" style="display:flex;">
                                <input type="text" class="modern-form-control datepicker-ramassage" id="input-date-fin" name="date_fin" placeholder="JJ/MM/AAAA" required style="flex: 1; border-radius:8px 0 0 8px; margin-bottom:0;">
                                <span class="btn-calendrier-fin" style="background:var(--gray-light); border:1px solid #dee2e6; border-left:none; border-radius:0 8px 8px 0; padding:0 15px; display:flex; align-items:center; cursor:pointer;">
                                    <i class="fa fa-calendar" style="color:var(--primary);"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Ligne 4: Fichier PDF -->
                    <div style="margin-bottom: 15px;">
                        <label class="modern-form-label"><i class="fa fa-file-pdf-o"></i> Liste des ramasseurs (PDF) <span class="required">*</span></label>
                        <input type="file" class="modern-form-control" id="input-liste" name="liste_ramasseurs" 
                               accept=".pdf" required style="width: 100%; padding: 8px 15px;">
                        <small class="text-muted">Format accepté: PDF (max 10MB) - OBLIGATOIRE</small>
                        <div id="preview-pdf" class="file-preview" style="margin-top: 10px;"></div>
                    </div>
                    
                    <!-- Ligne 5: Observations -->
                    <div style="margin-bottom: 15px;">
                        <label class="modern-form-label"><i class="fa fa-comment"></i> Observations</label>
                        <textarea class="modern-form-control" id="input-observations" name="observations" 
                                  placeholder="Observations supplémentaires" rows="3" style="width: 100%;"></textarea>
                    </div>
                    
                    <!-- Message d'avertissement -->
                    <div class="alert alert-warning" style="background:var(--warning-light); border-left:4px solid var(--warning); padding:15px; border-radius:8px;">
                        <i class="fa fa-info-circle" style="color:var(--warning);"></i> 
                        <strong>Note importante :</strong> La période ne doit pas dépasser 1 mois (31 jours). La liste des ramasseurs en PDF est obligatoire.
                    </div>
                    
                    <!-- Boutons -->
                    <div style="display: flex; justify-content: flex-end; gap: 15px; margin-top: 25px;">
                        <button type="button" class="btn-modern btn-modern-secondary" data-dismiss="modal" style="padding: 12px 30px;">
                            <i class="fa fa-times"></i> Annuler
                        </button>
                        <button type="button" id="btn-enregistrer-ramassage" class="btn-modern btn-modern-primary" style="padding: 12px 30px;">
                            <i class="fa fa-save"></i> Enregistrer
                        </button>
                    </div>
                    
                    <div class="modern-loader loaderRegister" style="display:none; text-align: center; padding: 20px;">
                        <i class="fa fa-spinner fa-spin" style="font-size: 32px; color: var(--primary);"></i>
                        <p>Enregistrement en cours...</p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- MODAL MODIFICATION RAMASSAGE -->
<div id="modal-modifier-ramassage" class="modal fade">
    <div class="modal-dialog modal-lg" style="margin: 80px 20px 20px 280px !important; max-width: 1000px !important;">
        <div class="modal-content">
            <div class="modal-header modern-modal-header">
                <h5 class="modal-title"><i class="fa fa-pencil"></i> Modifier le Plan de Ramassage</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body modern-modal-body" style="max-height: 70vh; overflow-y: auto; padding: 25px;">
                <form id="form-modifier-ramassage" enctype="multipart/form-data" autocomplete="off">
                    <input type="hidden" name="ramassage_id" id="modifier-ramassage-id">
                    <input type="hidden" name="updated_by" value="<?php echo isset($user['idUser']) ? $user['idUser'] : (isset($user['id']) ? $user['id'] : ''); ?>">
                    
                    <div class="alert alert-info" style="background:var(--info-light); border-left:4px solid var(--info); padding:15px; border-radius:8px; margin-bottom:20px;">
                        <i class="fa fa-info-circle" style="color:var(--info);"></i> 
                        Les champs avec <span class="required">*</span> sont obligatoires.
                        <div id="admin-note" style="display: none; margin-top:10px; color: #f0ad4e;">
                            <i class="fa fa-shield"></i> En tant qu'administrateur, vous pouvez modifier tous les champs.
                        </div>
                        <div id="non-admin-note" style="display: none; margin-top:10px; color: #5bc0de;">
                            <i class="fa fa-user"></i> Vous ne pouvez modifier que les observations.
                        </div>
                    </div>
                    
                    <!-- Ligne 1: Entité et Agence -->
                    <div style="display: flex; gap: 20px; margin-bottom: 15px;">
                        <div style="flex: 1;">
                            <label class="modern-form-label"><i class="fa fa-building"></i> Entité (qui ramasse) <span class="required">*</span></label>
                            <input type="text" class="modern-form-control" id="modifier-input-entite" name="entite_nom" 
                                   placeholder="Ex: DIRECTION REGIONALE, SERVICE COMPTABILITE" required style="width: 100%;" disabled>
                        </div>
                        <div style="flex: 1;">
                            <label class="modern-form-label"><i class="fa fa-building-o"></i> Agence à ramasser <span class="required">*</span></label>
                            <select class="modern-form-select" id="modifier-input-agence" name="agence_nom" required style="width: 100%; height: 46px;" disabled>
                                <option value="">Sélectionnez une agence</option>
                                <?php
                                if (!empty($agences)) {
                                    foreach ($agences as $agence) {
                                        $designation = isset($agence['designation']) ? $agence['designation'] : '';
                                        if (!empty($designation)) {
                                            echo '<option value="' . htmlspecialchars($designation) . '">' . htmlspecialchars($designation) . '</option>';
                                        }
                                    }
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Ligne 2: Période -->
                    <div style="margin-bottom: 15px;">
                        <label class="modern-form-label"><i class="fa fa-calendar"></i> Période <span class="required">*</span></label>
                        <input type="text" class="modern-form-control" id="modifier-input-periode" name="periode" 
                               placeholder="Ex: JANVIER 2024, SEMAINE 5" required style="width: 100%;" disabled>
                    </div>
                    
                    <!-- Ligne 3: Dates (côte à côte) -->
                    <div style="display: flex; gap: 20px; margin-bottom: 15px;">
                        <div style="flex: 1;">
                            <label class="modern-form-label"><i class="fa fa-calendar-plus-o"></i> Date de début <span class="required">*</span></label>
                            <div class="input-group" style="display:flex;">
                                <input type="text" class="modern-form-control datepicker-ramassage-modifier" id="modifier-input-date-debut" name="date_debut" placeholder="JJ/MM/AAAA" required style="flex: 1; border-radius:8px 0 0 8px; margin-bottom:0;" disabled>
                                <span class="btn-calendrier-debut-modifier" style="background:var(--gray-light); border:1px solid #dee2e6; border-left:none; border-radius:0 8px 8px 0; padding:0 15px; display:flex; align-items:center; cursor:pointer;">
                                    <i class="fa fa-calendar" style="color:var(--primary);"></i>
                                </span>
                            </div>
                        </div>
                        <div style="flex: 1;">
                            <label class="modern-form-label"><i class="fa fa-calendar-times-o"></i> Date de fin <span class="required">*</span></label>
                            <div class="input-group" style="display:flex;">
                                <input type="text" class="modern-form-control datepicker-ramassage-modifier" id="modifier-input-date-fin" name="date_fin" placeholder="JJ/MM/AAAA" required style="flex: 1; border-radius:8px 0 0 8px; margin-bottom:0;" disabled>
                                <span class="btn-calendrier-fin-modifier" style="background:var(--gray-light); border:1px solid #dee2e6; border-left:none; border-radius:0 8px 8px 0; padding:0 15px; display:flex; align-items:center; cursor:pointer;">
                                    <i class="fa fa-calendar" style="color:var(--primary);"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Ligne 4: Fichier PDF -->
                    <div style="margin-bottom: 15px;">
                        <label class="modern-form-label"><i class="fa fa-file-pdf-o"></i> Liste des ramasseurs (PDF)</label>
                        <input type="file" class="modern-form-control" id="modifier-input-liste" name="liste_ramasseurs" 
                               accept=".pdf" style="width: 100%; padding: 8px 15px;">
                        <small class="text-muted">Format accepté: PDF (max 10MB) - Facultatif (conserver l'ancien si non fourni)</small>
                        <div id="modifier-preview-pdf" class="file-preview" style="margin-top: 10px;"></div>
                        <div id="modifier-liste-actuelle" class="file-preview" style="margin-top: 10px;"></div>
                    </div>
                    
                    <!-- Ligne 5: Observations -->
                    <div style="margin-bottom: 15px;">
                        <label class="modern-form-label"><i class="fa fa-comment"></i> Observations <span class="required">*</span></label>
                        <textarea class="modern-form-control" id="modifier-input-observations" name="observations" 
                                  placeholder="Observations supplémentaires" rows="3" style="width: 100%;" required></textarea>
                    </div>
                    
                    <!-- Message d'avertissement -->
                    <div class="alert alert-warning" style="background:var(--warning-light); border-left:4px solid var(--warning); padding:15px; border-radius:8px;">
                        <i class="fa fa-info-circle" style="color:var(--warning);"></i> 
                        <strong>Note :</strong> Tous les utilisateurs autorisés peuvent modifier les observations. Seuls les administrateurs peuvent modifier les autres champs.
                    </div>
                    
                    <!-- Boutons -->
                    <div style="display: flex; justify-content: flex-end; gap: 15px; margin-top: 25px;">
                        <button type="button" class="btn-modern btn-modern-secondary" data-dismiss="modal" style="padding: 12px 30px;">
                            <i class="fa fa-times"></i> Annuler
                        </button>
                        <button type="button" id="btn-modifier-ramassage" class="btn-modern btn-modern-primary" style="padding: 12px 30px;">
                            <i class="fa fa-save"></i> Modifier
                        </button>
                    </div>
                    
                    <div class="modern-loader loaderModifier" style="display:none; text-align: center; padding: 20px;">
                        <i class="fa fa-spinner fa-spin" style="font-size: 32px; color: var(--primary);"></i>
                        <p>Modification en cours...</p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- MODAL DÉTAILS -->
<div id="modal-details-ramassage" class="modal fade">
    <div class="modal-dialog" style="margin: 80px 20px 20px 280px !important; max-width: 800px !important;">
        <div class="modal-content">
            <div class="modal-header modern-modal-header">
                <h5 class="modal-title"><i class="fa fa-info-circle"></i> Détails du Plan de Ramassage</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body modern-modal-body" style="max-height: 70vh; overflow-y: auto; padding: 25px;">
                <div id="details-loader" class="modern-loader" style="display:none;">
                    <i class="fa fa-spinner fa-spin"></i>
                    <p>Chargement des détails...</p>
                </div>
                <div id="details-content"></div>
            </div>
            <div class="modal-footer modern-modal-footer" style="padding: 20px 25px;">
                <button class="btn-modern btn-modern-secondary" data-dismiss="modal" style="padding: 12px 30px;">
                    <i class="fa fa-times"></i> Fermer
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// ============================================
// VARIABLES GLOBALES
// ============================================
let currentPage = <?php echo $page; ?>;
let currentSearch = '<?php echo addslashes($search); ?>';

// ============================================
// FONCTIONS UTILITAIRES
// ============================================
function escapeHtml(text) {
    const div = document.createElement('div');
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

function formaterDate(dateStr) {
    if (!dateStr) return '';
    try {
        if (dateStr.match(/^\d{2}\/\d{2}\/\d{4}$/)) {
            return dateStr;
        }
        
        if (dateStr.match(/^\d{4}-\d{2}-\d{2}$/)) {
            const parts = dateStr.split('-');
            return parts[2] + '/' + parts[1] + '/' + parts[0];
        }
        
        const date = new Date(dateStr);
        if (isNaN(date.getTime())) return dateStr;
        
        const jour = ('0' + date.getDate()).slice(-2);
        const mois = ('0' + (date.getMonth() + 1)).slice(-2);
        const annee = date.getFullYear();
        return jour + '/' + mois + '/' + annee;
    } catch (e) { 
        return dateStr; 
    }
}

// Transformation en majuscules
function mettreEnMajuscules(champ) {
    if (champ && champ.value) {
        champ.value = champ.value.toUpperCase();
    }
}

// ============================================
// RECHERCHE
// ============================================
function effectuerRecherche() {
    const searchInput = document.getElementById('search-ramassages');
    const searchTerm = searchInput.value.trim();
    
    let url = '?p=ramassage&page=1';
    if (searchTerm !== '') {
        url += '&search=' + encodeURIComponent(searchTerm);
    }
    
    window.location.href = url;
}

function effacerRecherche() {
    window.location.href = '?p=ramassage&page=1';
}

// ============================================
// MODIFICATION DE RAMASSAGE
// ============================================
function ouvrirModalModifier(ramassageId) {
    console.log('📋 Ouverture modal modification pour ramassage:', ramassageId);
    
    const form = document.getElementById('form-modifier-ramassage');
    if (form) {
        const erreurs = form.querySelectorAll('.is-invalid');
        for (let i = 0; i < erreurs.length; i++) {
            erreurs[i].classList.remove('is-invalid');
        }
    }
    
    const preview = document.getElementById('modifier-preview-pdf');
    if (preview) preview.innerHTML = '';
    
    const listeActuelle = document.getElementById('modifier-liste-actuelle');
    if (listeActuelle) listeActuelle.innerHTML = '';
    
    const loader = document.querySelector('.loaderModifier');
    if (loader) loader.style.display = 'block';
    
    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'index.php?p=ramassage.getRamassageData');
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    
    xhr.onload = function() {
        if (loader) loader.style.display = 'none';
        
        if (this.status === 200) {
            try {
                const response = JSON.parse(this.responseText);
                
                if (response.success && response.ramassage) {
                    const ramassage = response.ramassage;
                    
                    document.getElementById('modifier-ramassage-id').value = ramassageId;
                    document.getElementById('modifier-input-entite').value = ramassage.entite_nom || '';
                    
                    // Sélectionner l'agence dans le select
                    const agenceSelect = document.getElementById('modifier-input-agence');
                    if (agenceSelect) {
                        agenceSelect.value = ramassage.agence_nom || '';
                    }
                    
                    document.getElementById('modifier-input-periode').value = ramassage.periode || '';
                    document.getElementById('modifier-input-date-debut').value = formaterDate(ramassage.date_debut);
                    document.getElementById('modifier-input-date-fin').value = formaterDate(ramassage.date_fin);
                    document.getElementById('modifier-input-observations').value = ramassage.observations || '';
                    
                    if (ramassage.liste_path) {
                        const listeActuelle = document.getElementById('modifier-liste-actuelle');
                        if (listeActuelle) {
                            const fileName = ramassage.liste_path.split('/').pop();
                            listeActuelle.innerHTML = 
                                '<div style="background:var(--info-light); padding:10px; border-radius:8px;">' +
                                '<i class="fa fa-file-pdf-o" style="color:var(--danger);"></i> Liste actuelle: ' + 
                                '<a href="' + escapeHtml(ramassage.liste_path) + '" target="_blank" style="color:var(--info); text-decoration:none;">' +
                                fileName +
                                '</a><br>' +
                                '<small class="text-muted">Laissez vide pour conserver cette liste</small>' +
                                '</div>';
                        }
                    }
                    
                    const isAdmin = response.isAdmin || false;
                    
                    if (isAdmin) {
                        document.getElementById('modifier-input-entite').disabled = false;
                        document.getElementById('modifier-input-agence').disabled = false;
                        document.getElementById('modifier-input-periode').disabled = false;
                        document.getElementById('modifier-input-date-debut').disabled = false;
                        document.getElementById('modifier-input-date-fin').disabled = false;
                        document.getElementById('admin-note').style.display = 'block';
                        document.getElementById('non-admin-note').style.display = 'none';
                    } else {
                        document.getElementById('modifier-input-entite').disabled = true;
                        document.getElementById('modifier-input-agence').disabled = true;
                        document.getElementById('modifier-input-periode').disabled = true;
                        document.getElementById('modifier-input-date-debut').disabled = true;
                        document.getElementById('modifier-input-date-fin').disabled = true;
                        document.getElementById('admin-note').style.display = 'none';
                        document.getElementById('non-admin-note').style.display = 'block';
                    }
                    
                    if (typeof jQuery !== 'undefined' && jQuery.fn.modal) {
                        jQuery('#modal-modifier-ramassage').modal('show');
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
    
    xhr.send('ramassage_id=' + encodeURIComponent(ramassageId));
}

function validerFormulaireModification() {
    const observation = document.getElementById('modifier-input-observations');
    if (!observation || !observation.value.trim()) {
        observation.classList.add('is-invalid');
        observation.focus();
        notif('Les observations sont obligatoires', 'warning');
        return false;
    }
    
    const isAdmin = !document.getElementById('modifier-input-entite').disabled;
    
    if (isAdmin) {
        const champs = [
            { id: 'modifier-input-entite', nom: 'Entité' },
            { id: 'modifier-input-agence', nom: 'Agence' },
            { id: 'modifier-input-periode', nom: 'Période' },
            { id: 'modifier-input-date-debut', nom: 'Date de début' },
            { id: 'modifier-input-date-fin', nom: 'Date de fin' }
        ];
        
        for (let i = 0; i < champs.length; i++) {
            const champ = document.getElementById(champs[i].id);
            if (!champ || !champ.value.trim()) {
                champ.classList.add('is-invalid');
                champ.focus();
                notif('Le champ "' + champs[i].nom + '" est obligatoire', 'warning');
                return false;
            }
        }
        
        const dateDebut = document.getElementById('modifier-input-date-debut');
        const dateFin = document.getElementById('modifier-input-date-fin');
        
        const regex = /^(\d{2})\/(\d{2})\/(\d{4})$/;
        if (!regex.test(dateDebut.value)) {
            dateDebut.classList.add('is-invalid');
            dateDebut.focus();
            notif('Le format de la date de début doit être JJ/MM/AAAA', 'warning');
            return false;
        }
        
        if (!regex.test(dateFin.value)) {
            dateFin.classList.add('is-invalid');
            dateFin.focus();
            notif('Le format de la date de fin doit être JJ/MM/AAAA', 'warning');
            return false;
        }
        
        const partsDebut = dateDebut.value.split('/');
        const partsFin = dateFin.value.split('/');
        const dateObjDebut = new Date(partsDebut[2], partsDebut[1] - 1, partsDebut[0]);
        const dateObjFin = new Date(partsFin[2], partsFin[1] - 1, partsFin[0]);
        
        if (dateObjDebut >= dateObjFin) {
            notif('La date de début doit être antérieure à la date de fin', 'warning');
            dateDebut.classList.add('is-invalid');
            dateFin.classList.add('is-invalid');
            return false;
        }
        
        const diff = dateObjFin - dateObjDebut;
        const jours = Math.floor(diff / (1000 * 60 * 60 * 24));
        
        if (jours > 31) {
            notif('La période ne doit pas dépasser 1 mois (31 jours)', 'warning');
            dateDebut.classList.add('is-invalid');
            dateFin.classList.add('is-invalid');
            return false;
        }
    }
    
    const pdfInput = document.getElementById('modifier-input-liste');
    if (pdfInput && pdfInput.files && pdfInput.files.length > 0) {
        const file = pdfInput.files[0];
        if (file.size > 10 * 1024 * 1024) {
            pdfInput.classList.add('is-invalid');
            pdfInput.focus();
            notif('Le fichier est trop volumineux (max 10MB)', 'warning');
            return false;
        }
        
        if (file.type !== 'application/pdf' && !file.name.toLowerCase().endsWith('.pdf')) {
            pdfInput.classList.add('is-invalid');
            pdfInput.focus();
            notif('Seuls les fichiers PDF sont acceptés', 'warning');
            return false;
        }
    }
    
    return true;
}

function envoyerModification() {
    console.log('🔄 Envoi modification...');
    
    if (!validerFormulaireModification()) return;
    
    const form = document.getElementById('form-modifier-ramassage');
    if (!form) return;
    
    const formData = new FormData(form);
    
    const isAdmin = !document.getElementById('modifier-input-entite').disabled;
    
    if (isAdmin) {
        const dateDebut = document.getElementById('modifier-input-date-debut');
        const dateFin = document.getElementById('modifier-input-date-fin');
        
        if (dateDebut && dateDebut.value) {
            const parts = dateDebut.value.split('/');
            if (parts.length === 3) {
                const dateMySQL = parts[2] + '-' + parts[1] + '-' + parts[0];
                formData.set('date_debut', dateMySQL);
            }
        }
        
        if (dateFin && dateFin.value) {
            const parts = dateFin.value.split('/');
            if (parts.length === 3) {
                const dateMySQL = parts[2] + '-' + parts[1] + '-' + parts[0];
                formData.set('date_fin', dateMySQL);
            }
        }
    }
    
    const btnSubmit = document.getElementById('btn-modifier-ramassage');
    const texteOriginal = btnSubmit.innerHTML;
    btnSubmit.disabled = true;
    btnSubmit.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Modification...';
    
    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'index.php?p=ramassage.updateRamassage');
    
    xhr.onload = function() {
        btnSubmit.disabled = false;
        btnSubmit.innerHTML = texteOriginal;
        
        if (this.status === 200) {
            try {
                const response = JSON.parse(this.responseText);
                
                if (response.success) {
                    if (typeof jQuery !== 'undefined' && jQuery.fn.modal) {
                        jQuery('#modal-modifier-ramassage').modal('hide');
                    }
                    
                    notif(response.message, 'success');
                    
                    setTimeout(function() {
                        let url = '?p=ramassage&page=' + currentPage;
                        if (currentSearch !== '') {
                            url += '&search=' + encodeURIComponent(currentSearch);
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
// AJOUT DE RAMASSAGE
// ============================================
function ouvrirModalAjout() {
    console.log('📋 Ouverture modal ajout');
    
    const form = document.getElementById('form-ajout-ramassage');
    if (form) {
        const erreurs = form.querySelectorAll('.is-invalid');
        for (let i = 0; i < erreurs.length; i++) {
            erreurs[i].classList.remove('is-invalid');
        }
    }
    
    const aujourdhui = new Date();
    const dateInputDebut = document.getElementById('input-date-debut');
    const dateInputFin = document.getElementById('input-date-fin');
    
    if (dateInputDebut) {
        const jour = ('0' + aujourdhui.getDate()).slice(-2);
        const mois = ('0' + (aujourdhui.getMonth() + 1)).slice(-2);
        const annee = aujourdhui.getFullYear();
        dateInputDebut.value = jour + '/' + mois + '/' + annee;
    }
    
    if (dateInputFin) {
        const finDate = new Date();
        finDate.setDate(finDate.getDate() + 30);
        const jour = ('0' + finDate.getDate()).slice(-2);
        const mois = ('0' + (finDate.getMonth() + 1)).slice(-2);
        const annee = finDate.getFullYear();
        dateInputFin.value = jour + '/' + mois + '/' + annee;
    }
    
    const preview = document.getElementById('preview-pdf');
    if (preview) preview.innerHTML = '';
    
    if (typeof jQuery !== 'undefined' && jQuery.fn.modal) {
        jQuery('#modal-ajout-ramassage').modal('show');
    }
}

function validerFormulaireAjout() {
    const champs = [
        { id: 'input-entite', nom: 'Entité' },
        { id: 'input-agence', nom: 'Agence' },
        { id: 'input-periode', nom: 'Période' },
        { id: 'input-date-debut', nom: 'Date de début' },
        { id: 'input-date-fin', nom: 'Date de fin' }
    ];
    
    for (let i = 0; i < champs.length; i++) {
        const champ = document.getElementById(champs[i].id);
        if (!champ || !champ.value.trim()) {
            champ.classList.add('is-invalid');
            champ.focus();
            notif('Le champ "' + champs[i].nom + '" est obligatoire', 'warning');
            return false;
        }
    }
    
    const pdfInput = document.getElementById('input-liste');
    if (!pdfInput || !pdfInput.files || pdfInput.files.length === 0) {
        pdfInput.classList.add('is-invalid');
        pdfInput.focus();
        notif('La liste des ramasseurs (PDF) est obligatoire', 'warning');
        return false;
    }
    
    const dateDebut = document.getElementById('input-date-debut');
    const dateFin = document.getElementById('input-date-fin');
    
    const regex = /^(\d{2})\/(\d{2})\/(\d{4})$/;
    if (!regex.test(dateDebut.value)) {
        dateDebut.classList.add('is-invalid');
        dateDebut.focus();
        notif('Le format de la date de début doit être JJ/MM/AAAA', 'warning');
        return false;
    }
    
    if (!regex.test(dateFin.value)) {
        dateFin.classList.add('is-invalid');
        dateFin.focus();
        notif('Le format de la date de fin doit être JJ/MM/AAAA', 'warning');
        return false;
    }
    
    const partsDebut = dateDebut.value.split('/');
    const partsFin = dateFin.value.split('/');
    const dateObjDebut = new Date(partsDebut[2], partsDebut[1] - 1, partsDebut[0]);
    const dateObjFin = new Date(partsFin[2], partsFin[1] - 1, partsFin[0]);
    
    if (dateObjDebut >= dateObjFin) {
        notif('La date de début doit être antérieure à la date de fin', 'warning');
        dateDebut.classList.add('is-invalid');
        dateFin.classList.add('is-invalid');
        return false;
    }
    
    const diff = dateObjFin - dateObjDebut;
    const jours = Math.floor(diff / (1000 * 60 * 60 * 24));
    
    if (jours > 31) {
        notif('La période ne doit pas dépasser 1 mois (31 jours)', 'warning');
        dateDebut.classList.add('is-invalid');
        dateFin.classList.add('is-invalid');
        return false;
    }
    
    const file = pdfInput.files[0];
    if (file.size > 10 * 1024 * 1024) {
        pdfInput.classList.add('is-invalid');
        pdfInput.focus();
        notif('Le fichier est trop volumineux (max 10MB)', 'warning');
        return false;
    }
    
    if (file.type !== 'application/pdf' && !file.name.toLowerCase().endsWith('.pdf')) {
        pdfInput.classList.add('is-invalid');
        pdfInput.focus();
        notif('Seuls les fichiers PDF sont acceptés', 'warning');
        return false;
    }
    
    return true;
}

function envoyerAjout() {
    console.log('🔄 Envoi ajout...');
    
    if (!validerFormulaireAjout()) return;
    
    const form = document.getElementById('form-ajout-ramassage');
    if (!form) return;
    
    const formData = new FormData(form);
    
    const dateDebut = document.getElementById('input-date-debut');
    const dateFin = document.getElementById('input-date-fin');
    
    if (dateDebut && dateDebut.value) {
        const parts = dateDebut.value.split('/');
        if (parts.length === 3) {
            const dateMySQL = parts[2] + '-' + parts[1] + '-' + parts[0];
            formData.set('date_debut', dateMySQL);
        }
    }
    
    if (dateFin && dateFin.value) {
        const parts = dateFin.value.split('/');
        if (parts.length === 3) {
            const dateMySQL = parts[2] + '-' + parts[1] + '-' + parts[0];
            formData.set('date_fin', dateMySQL);
        }
    }
    
    const btnSubmit = document.getElementById('btn-enregistrer-ramassage');
    const texteOriginal = btnSubmit.innerHTML;
    btnSubmit.disabled = true;
    btnSubmit.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Envoi...';
    
    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'index.php?p=ramassage.ajoutRamassage');
    
    xhr.onload = function() {
        btnSubmit.disabled = false;
        btnSubmit.innerHTML = texteOriginal;
        
        if (this.status === 200) {
            try {
                const response = JSON.parse(this.responseText);
                
                if (response.success) {
                    if (typeof jQuery !== 'undefined' && jQuery.fn.modal) {
                        jQuery('#modal-ajout-ramassage').modal('hide');
                    }
                    
                    notif(response.message, 'success');
                    
                    setTimeout(function() {
                        let url = '?p=ramassage&page=1';
                        if (currentSearch !== '') {
                            url += '&search=' + encodeURIComponent(currentSearch);
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
// DÉTAILS
// ============================================
function ouvrirModalDetails(ramassageId) {
    console.log('📋 Ouverture détails pour ramassage:', ramassageId);
    
    const loader = document.getElementById('details-loader');
    const content = document.getElementById('details-content');
    
    loader.style.display = 'block';
    content.innerHTML = '';
    
    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'index.php?p=ramassage.getDetails');
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    
    xhr.onload = function() {
        loader.style.display = 'none';
        
        if (this.status === 200) {
            try {
                const response = JSON.parse(this.responseText);
                
                if (response.success) {
                    content.innerHTML = response.html;
                    
                    if (typeof jQuery !== 'undefined' && jQuery.fn.modal) {
                        jQuery('#modal-details-ramassage').modal('show');
                    }
                } else {
                    content.innerHTML = '<div class="alert alert-danger">' + 
                        (response.message || 'Erreur lors du chargement des détails') + 
                        '</div>';
                    notif(response.message || 'Erreur', 'error');
                }
            } catch (e) {
                console.error('❌ Erreur parsing JSON:', e);
                console.error('Réponse reçue:', this.responseText);
                content.innerHTML = '<div class="alert alert-danger">Erreur de chargement des données</div>';
                notif('Erreur de chargement des détails', 'error');
            }
        } else {
            content.innerHTML = '<div class="alert alert-danger">Erreur HTTP ' + this.status + '</div>';
            notif('Erreur serveur', 'error');
        }
    };
    
    xhr.onerror = function() {
        loader.style.display = 'none';
        content.innerHTML = '<div class="alert alert-danger">Erreur réseau</div>';
        notif('Erreur de connexion', 'error');
    };
    
    xhr.send('ramassage_id=' + encodeURIComponent(ramassageId));
}

// ============================================
// INITIALISATION
// ============================================
$(document).ready(function() {
    console.log('🚀 Page gestion ramassages initialisée');
    
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
    $('#btn-ajouter-ramassage').click(function(e) {
        e.preventDefault();
        ouvrirModalAjout();
    });
    
    $('#btn-enregistrer-ramassage').click(function(e) {
        e.preventDefault();
        envoyerAjout();
    });
    
    $('#btn-modifier-ramassage').click(function(e) {
        e.preventDefault();
        envoyerModification();
    });
    
    // Recherche
    $('#btn-search').click(function() {
        effectuerRecherche();
    });
    
    $('#search-ramassages').keypress(function(e) {
        if (e.which === 13) {
            effectuerRecherche();
        }
    });
    
    $('#btn-clear-search').click(function() {
        effacerRecherche();
    });
    
    // Gestion des événements sur les boutons d'action
    $(document).on('click', '.modifier-ramassage', function(e) {
        e.preventDefault();
        const ramassageId = $(this).data('id');
        ouvrirModalModifier(ramassageId);
    });
    
    $(document).on('click', '.details-ramassage', function(e) {
        e.preventDefault();
        const ramassageId = $(this).data('id');
        ouvrirModalDetails(ramassageId);
    });
    
    // Transformation en majuscules pour entite_nom
    const champsMajuscules = ['input-entite', 'modifier-input-entite'];
    champsMajuscules.forEach(function(id) {
        const champ = document.getElementById(id);
        if (champ) {
            champ.addEventListener('input', function() {
                mettreEnMajuscules(this);
            });
            champ.addEventListener('blur', function() {
                mettreEnMajuscules(this);
            });
            if (champ.value) {
                champ.value = champ.value.toUpperCase();
            }
        }
    });
    
    // Datepicker
    if (typeof jQuery.fn.datepicker !== 'undefined') {
        $('.datepicker-ramassage, .datepicker-ramassage-modifier').datepicker({
            format: 'dd/mm/yyyy',
            language: 'fr',
            autoclose: true,
            todayHighlight: true,
            startDate: new Date(2020, 0, 1),
            endDate: new Date(2030, 11, 31)
        });
        
        $('.btn-calendrier-debut, .btn-calendrier-debut-modifier').click(function(e) {
            e.preventDefault();
            const input = $(this).closest('.input-group').find('input[type="text"]');
            if (input.length) {
                input.datepicker('show');
            }
        });
        
        $('.btn-calendrier-fin, .btn-calendrier-fin-modifier').click(function(e) {
            e.preventDefault();
            const input = $(this).closest('.input-group').find('input[type="text"]');
            if (input.length) {
                input.datepicker('show');
            }
        });
    }
    
    // Prévisualisation des PDF
    $('#input-liste').change(function(e) {
        const file = e.target.files[0];
        const preview = $('#preview-pdf');
        preview.empty();
        
        if (!file) return;
        
        if (file.size > 10 * 1024 * 1024) {
            notif('Le fichier est trop volumineux (max 10MB)', 'warning');
            $(this).val('');
            return;
        }
        
        if (file.type !== 'application/pdf' && !file.name.toLowerCase().endsWith('.pdf')) {
            notif('Seuls les fichiers PDF sont acceptés', 'warning');
            $(this).val('');
            return;
        }
        
        preview.html(
            '<div style="background:var(--info-light); padding:10px; border-radius:8px;">' +
            '<i class="fa fa-file-pdf-o" style="color:var(--danger);"></i> ' + escapeHtml(file.name) + 
            ' (' + Math.round(file.size / 1024) + ' KB)' +
            '</div>'
        );
    });
    
    $('#modifier-input-liste').change(function(e) {
        const file = e.target.files[0];
        const preview = $('#modifier-preview-pdf');
        preview.empty();
        
        if (!file) return;
        
        if (file.size > 10 * 1024 * 1024) {
            notif('Le fichier est trop volumineux (max 10MB)', 'warning');
            $(this).val('');
            return;
        }
        
        if (file.type !== 'application/pdf' && !file.name.toLowerCase().endsWith('.pdf')) {
            notif('Seuls les fichiers PDF sont acceptés', 'warning');
            $(this).val('');
            return;
        }
        
        preview.html(
            '<div style="background:var(--info-light); padding:10px; border-radius:8px;">' +
            '<i class="fa fa-file-pdf-o" style="color:var(--danger);"></i> Nouveau fichier: ' + escapeHtml(file.name) + 
            ' (' + Math.round(file.size / 1024) + ' KB)' +
            '</div>'
        );
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
/**
 * GÉNÈRE UNE LIGNE DE TABLEAU POUR UN PLAN DE RAMASSAGE
 */
function genererLigneRamassageModerne($ramassage, $user) {
    if (!is_array($ramassage)) return '';
    
    // Déterminer l'état de validité
    $etatClass = 'status-valide';
    $etatText = 'Valide';
    
    if (isset($ramassage['date_fin']) && !empty($ramassage['date_fin'])) {
        try {
            $dateFinStr = is_string($ramassage['date_fin']) ? $ramassage['date_fin'] : '';
            
            if ($ramassage['date_fin'] instanceof \DateTime) {
                $dateFin = $ramassage['date_fin']->getTimestamp();
            } else {
                $dateFin = strtotime($dateFinStr);
            }
            
            if ($dateFin) {
                $aujourdhui = time();
                
                if ($dateFin < $aujourdhui) {
                    $etatClass = 'status-expire';
                    $etatText = 'Expiré';
                } else {
                    $joursRestants = floor(($dateFin - $aujourdhui) / (60 * 60 * 24));
                    
                    if ($joursRestants <= 7) {
                        $etatClass = 'status-expire-bientot';
                        $etatText = 'Expire bientôt (' . $joursRestants . 'j)';
                    }
                }
            }
        } catch (\Exception $e) {
            error_log("Erreur calcul état: " . $e->getMessage());
        }
    }
    
    $ramassage_id = isset($ramassage['id']) ? $ramassage['id'] : 0;
    
    $entite_nom = isset($ramassage['entite_nom']) && !empty($ramassage['entite_nom']) 
        ? htmlspecialchars($ramassage['entite_nom']) 
        : 'Non défini';
    
    $agence_nom = isset($ramassage['agence_nom']) && !empty($ramassage['agence_nom']) 
        ? htmlspecialchars($ramassage['agence_nom']) 
        : 'Non défini';
    
    $periode = isset($ramassage['periode']) ? htmlspecialchars($ramassage['periode']) : '';
    
    // Dates
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
    
    // Vérifier si l'utilisateur peut modifier
    $peutModifier = false;
    $privilege = isset($user['privilege']) ? $user['privilege'] : '';
    
    if (in_array($privilege, ['Administration', 'SuperAdministration'])) {
        $peutModifier = true;
    } else if (in_array($privilege, ['Agence', 'AgenceSage', 'Comptabilite', 'ControleInterne', 'Controleur'])) {
        $userId = isset($user['idUser']) ? $user['idUser'] : (isset($user['id']) ? $user['id'] : 0);
        $createdById = isset($ramassage['created_by']) ? $ramassage['created_by'] : 0;
        
        if ($userId == $createdById) {
            $peutModifier = true;
        }
        
        if (!$peutModifier && isset($ramassage['created_by_name']) && isset($user['username'])) {
            if ($ramassage['created_by_name'] == $user['username']) {
                $peutModifier = true;
            }
        }
    }
    
    $html = '<tr id="ramassage-' . $ramassage_id . '">';
    $html .= '<td class="text-center"><span style="font-size:11px;">' . htmlspecialchars($date_creation) . '</span></td>';
    $html .= '<td><span class="entite-badge"><i class="fa fa-building"></i> ' . $entite_nom . '</span></td>';
    $html .= '<td>' . $agence_nom . '</td>';
    $html .= '<td>' . $periode . '</td>';
    $html .= '<td class="text-center">' . htmlspecialchars($date_debut) . '</td>';
    $html .= '<td class="text-center">' . htmlspecialchars($date_fin) . '</td>';
    $html .= '<td class="text-center"><span class="status-badge ' . $etatClass . '">' . htmlspecialchars($etatText) . '</span></td>';
    $html .= '<td class="text-center"><div class="action-icons">';
    
    // Bouton Détails - toujours visible
    $html .= '<a href="#" class="details-ramassage action-link purple" data-id="' . $ramassage_id . '" title="Détails">';
    $html .= '<i class="fa fa-info-circle"></i></a>';
    
    // Bouton Modifier - si autorisé
    if ($peutModifier) {
        $html .= '<a href="#" class="modifier-ramassage action-link green" data-id="' . $ramassage_id . '" title="Modifier">';
        $html .= '<i class="fa fa-pencil"></i></a>';
    }
    
    $html .= '</div></td></tr>';
    
    return $html;
}
?>