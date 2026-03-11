<?php
use Core\Model\App;
use Core\Database\ArretControle;
use Core\Database\ActionsArrets;
use Core\Model\Session;

$auth = App::getDBAuth();
$session = Session::getInstance();

$user = $_SESSION['user'];

// Récupération des paramètres
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$date_filter = isset($_GET['date_filter']) ? trim($_GET['date_filter']) : '';

// Si une date est en session, l'utiliser par défaut
if (isset($_SESSION['search0']) && empty($date_filter)) {
    $date_filter = $_SESSION['search0'];
}

// Filtrer les arrêts par date si spécifiée
$arretsDouaniersFiltres = array();
if (!empty($arretsDouaniers) && is_array($arretsDouaniers)) {
    foreach ($arretsDouaniers as $arret) {
        $include = true;
        
        // Filtre par date
        if (!empty($date_filter)) {
            $dateArret = '';
            if (isset($arret['dateEntree'])) {
                if (is_object($arret['dateEntree']) && method_exists($arret['dateEntree'], 'format')) {
                    $dateArret = $arret['dateEntree']->format('Y-m-d');
                } elseif (is_string($arret['dateEntree'])) {
                    $dateArret = date('Y-m-d', strtotime($arret['dateEntree']));
                }
            }
            if ($dateArret != $date_filter) {
                $include = false;
            }
        }
        
        // Filtre par recherche texte
        if ($include && !empty($search)) {
            $found = false;
            $searchFields = ['agence', 'arretInfo', 'arretDouanier', 'totalCaisse', 'diffDouanier', 'diffCaisse'];
            
            foreach ($searchFields as $field) {
                if (isset($arret[$field]) && stripos((string)$arret[$field], $search) !== false) {
                    $found = true;
                    break;
                }
            }
            
            if (!$found) {
                $include = false;
            }
        }
        
        if ($include) {
            $arretsDouaniersFiltres[] = $arret;
        }
    }
} else {
    $arretsDouaniersFiltres = array();
}

// PAGINATION MANUELLE
$itemsPerPage = 10;
$totalArrets = count($arretsDouaniersFiltres);
$totalPages = ceil($totalArrets / $itemsPerPage);

if ($page < 1) $page = 1;
if ($totalPages > 0 && $page > $totalPages) $page = $totalPages;

$startIndex = ($page - 1) * $itemsPerPage;
$endIndex = min($startIndex + $itemsPerPage, $totalArrets);
$arretsPagines = array_slice($arretsDouaniersFiltres, $startIndex, $itemsPerPage);
?>

<!-- FONT AWESOME 4.5.0 LOCAL -->
<link rel="stylesheet" href="Public/font-awesome/4.5.0/css/font-awesome.min.css">

<!-- JQUERY LOCAL -->
<script src="Public/js/jquery-2.1.4.min.js"></script>

<!-- BOOTSTRAP LOCAL -->
<link rel="stylesheet" href="Public/css/bootstrap/css/bootstrap.min.css">
<script src="Public/js/bootstrap.min.js"></script>

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

.arrets-agences-container { max-width:100%; margin:0 auto; font-family:'Inter',sans-serif; }
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
.legend-color.blue { background:var(--info); }
.legend-color.green { background:var(--success); }
.legend-color.purple { background:#8e44ad; }

.actions-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(200px, 1fr)); gap:12px; margin-bottom:20px; }
.modern-action-btn { display:flex; flex-direction:column; align-items:center; padding:15px 10px; background:white; border:1px solid var(--gray-border); border-radius:var(--radius); text-decoration:none; color:var(--dark); transition:all 0.2s ease; box-shadow:var(--shadow); }
.modern-action-btn:hover { transform:translateY(-2px); box-shadow:var(--shadow-hover); border-color:var(--primary); text-decoration:none; color:var(--dark); }
.btn-icon { font-size:18px; margin-bottom:8px; }
.btn-text { font-weight:600; font-size:13px; }
.btn-subtext { font-size:10px; color:var(--gray); margin-top:3px; }
.print-btn { background:linear-gradient(135deg, #f39c12, #e67e22); border:none; color:white; }
.print-btn:hover { color:white; }

.modern-table-container { background:white; border-radius:var(--radius); padding:20px; box-shadow:var(--shadow); margin-bottom:30px; border:1px solid var(--gray-border); }
.table-header-modern { display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; padding-bottom:15px; border-bottom:1px solid var(--gray-border); flex-wrap:wrap; gap:15px; }
.table-title { font-size:16px; font-weight:600; color:var(--dark); display:flex; align-items:center; gap:10px; }

/* Barre de recherche et filtres */
.filter-section {
    display:flex;
    flex-wrap:wrap;
    gap:15px;
    margin-bottom:20px;
    align-items:flex-end;
}
.filter-item {
    min-width:200px;
}
.filter-item label {
    font-weight:600;
    font-size:12px;
    color:var(--gray);
    margin-bottom:5px;
    display:block;
}
.filter-item label i {
    margin-right:5px;
    color:var(--primary);
}
.modern-input {
    width:100%;
    padding:10px 15px;
    border:1px solid #dce4ec;
    border-radius:25px;
    background:#f8f9fa;
    font-size:14px;
    outline:none;
    transition:all 0.3s ease;
}
.modern-input:focus {
    border-color:var(--primary);
    box-shadow:0 0 0 3px rgba(67,97,238,0.1);
}
.modern-select {
    width:100%;
    padding:10px 15px;
    border:1px solid #dce4ec;
    border-radius:25px;
    background:#f8f9fa;
    font-size:14px;
    outline:none;
    transition:all 0.3s ease;
}
.modern-select:focus {
    border-color:var(--primary);
    box-shadow:0 0 0 3px rgba(67,97,238,0.1);
}
.modern-btn-search {
    background:linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
    color:white;
    border:none;
    border-radius:25px;
    padding:10px 25px;
    cursor:pointer;
    transition:all 0.3s ease;
    font-weight:600;
}
.modern-btn-search:hover {
    opacity:0.9;
    transform:translateY(-2px);
    box-shadow:var(--shadow-hover);
}

#dynamic-table { width:100% !important; font-size:13px; }
#dynamic-table thead th { background:var(--gray-light); color:var(--dark); font-weight:600; font-size:11px; padding:12px 8px; }
#dynamic-table tbody td { padding:12px 8px; border-bottom:1px solid var(--gray-border); vertical-align:middle; }
#dynamic-table tbody tr:hover { background:var(--gray-light); }

.diff-cell { font-weight:600; }
.diff-positif { color:var(--success); }
.diff-negatif { color:var(--danger); }

.action-icons { display:flex; gap:8px; flex-wrap: wrap; justify-content: center; }
.action-link { display:inline-flex; align-items:center; justify-content:center; text-decoration:none; width:32px; height:32px; border-radius:4px; transition:all 0.2s ease; }
.action-link:hover { transform:translateY(-2px); box-shadow:var(--shadow); text-decoration:none; }
.action-link.primary i { color:var(--info); }
.action-link.green i { color:var(--success); }
.action-link.grey i { color:var(--gray); }

.modal-header.modern-modal-header { background:linear-gradient(135deg,#0d1b3e,#1a2b5c); padding:20px 25px; border-radius:12px 12px 0 0; }
.modern-modal-header .modal-title { color:white; font-weight:600; font-size:16px; display:flex; align-items:center; gap:12px; }
.modern-modal-header .close { color:white; opacity:0.8; width:32px; height:32px; background:rgba(255,255,255,0.1); border-radius:50%; display:flex; align-items:center; justify-content:center; border:none; }
.modern-modal-header .close:hover { opacity:1; background:rgba(255,255,255,0.2); }
.modal-body.modern-modal-body { padding:25px; background:#f8fafc; max-height:70vh; overflow-y:auto; }
.modal-footer.modern-modal-footer { padding:20px 25px; background:white; border-top:1px solid #e9ecef; border-radius:0 0 12px 12px; display:flex; gap:10px; justify-content:flex-end; }

.modern-form-control { width:100%; padding:12px 15px; border:1px solid #dee2e6; border-radius:8px; font-size:14px; transition:all 0.3s ease; margin-bottom:15px; background:white; }
.modern-form-control:focus { border-color:#4361ee; box-shadow:0 0 0 3px rgba(67,97,238,0.1); outline:none; }
.modern-form-control[readonly] { background:var(--gray-light); cursor:not-allowed; }
.modern-form-label { font-weight:600; font-size:13px; color:var(--dark); margin-bottom:5px; display:block; }
.modern-form-label i { margin-right:5px; color:var(--primary); }

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

/* Grille pour les actions dans le modal */
.actions-grid-modal {
    display:grid;
    grid-template-columns:repeat(2, 1fr);
    gap:15px;
    margin-bottom:20px;
}
.actions-grid-modal .form-group {
    margin-bottom:0;
}

/* Pagination */
.pagination-modern { display:flex; justify-content:flex-end; gap:5px; margin-top:20px; }
.pagination-item { display:inline-flex; align-items:center; justify-content:center; min-width:36px; height:36px; padding:0 6px; border-radius:8px; background:white; border:1px solid #dee2e6; color:var(--dark); text-decoration:none; transition:all 0.2s ease; }
.pagination-item:hover { background:var(--primary-light); border-color:var(--primary); color:var(--primary); text-decoration:none; }
.pagination-item.active { background:var(--primary); border-color:var(--primary); color:white; }
.pagination-item.disabled { opacity:0.5; pointer-events:none; }
.pagination-info { padding:8px 0; color:var(--gray); }

/* FontAwesome Fixes pour FA4 */
.fa {
    font-family: FontAwesome !important;
}

@media (max-width:768px) { 
    .actions-grid { grid-template-columns:repeat(2,1fr); } 
    .table-header-modern { flex-direction:column; align-items:flex-start; } 
    .filter-section { flex-direction:column; align-items:stretch; }
    .filter-item { width:100%; }
    .modal-dialog { margin:10px !important; max-width:calc(100% - 20px) !important; } 
    .stats-grid { justify-content:center; }
    .pagination-modern { justify-content:center; }
    .actions-grid-modal { grid-template-columns:1fr; }
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

<div class="arrets-agences-container">
    <!-- Fil d'Ariane moderne -->
    <div class="modern-breadcrumbs" style="margin-bottom:15px;">
        <ul class="breadcrumb" style="background:none; padding:0;">
            <li style="display:inline-block;">
                <i class="fa fa-home" style="color:var(--primary);"></i>
                <a href="#" onclick="return naviguerVers('<?php echo App::url("dashboard"); ?>')" style="color:var(--dark);">Accueil</a>
            </li>
            <li style="display:inline-block; margin-left:8px;">
                <i class="fa fa-angle-right" style="color:var(--gray);"></i>
                Arrêts Agences
            </li>
            <?php if (!empty($search) || !empty($date_filter)): ?>
            <li style="display:inline-block; margin-left:8px;">
                <i class="fa fa-angle-right" style="color:var(--gray);"></i>
                <i class="fa fa-search" style="color:var(--warning);"></i>
                Recherche active
            </li>
            <?php endif; ?>
        </ul>
    </div>

    <!-- Header avec statistiques -->
    <div class="modern-header-banner">
        <h1 class="banner-title"><i class="fa fa-building"></i> ARRÊTS DES AGENCES</h1>
        <p class="banner-subtitle">Gestion et suivi des arrêts de caisse des agences</p>
        
        <div class="stats-grid">
            <div class="stat-item">
                <i class="fa fa-line-chart" style="color:var(--primary);"></i>
                <div>
                    <div class="stat-value"><?php echo $totalArrets; ?></div>
                    <div class="stat-label">Total arrêts</div>
                </div>
            </div>
        </div>
        
        <div class="legend-grid">
            <div class="legend-item"><i class="fa fa-info-circle" style="color:var(--info);"></i><span>Détails</span></div>
            <div class="legend-item"><i class="fa fa-pencil" style="color:var(--success);"></i><span>Observations</span></div>
            <div class="legend-item"><i class="fa fa-clipboard" style="color:var(--gray);"></i><span>Contrôle</span></div>
        </div>
    </div>

    <!-- Filtres et actions -->
    <div class="actions-section">
        <div class="actions-grid">
            <a href="<?php echo App::url('ArretsDouanier.ArretsCaissesPrint0'); ?>" class="modern-action-btn print-btn">
                <i class="fa fa-print btn-icon"></i>
                <span class="btn-text">Imprimer</span>
                <span class="btn-subtext">Liste des arrêts</span>
            </a>
        </div>
    </div>

    <!-- Barre de filtres -->
    <div class="filter-section">
        <div class="filter-item">
            <label><i class="fa fa-calendar"></i> Filtre par date</label>
            <input type="date" id="date-filter" class="modern-input" value="<?php echo htmlspecialchars($date_filter); ?>">
        </div>
        <div class="filter-item" style="flex:2;">
            <label><i class="fa fa-search"></i> Recherche</label>
            <input type="text" id="search-arrets" class="modern-input" placeholder="Agence, montant, différence..." value="<?php echo htmlspecialchars($search); ?>">
        </div>
        <div class="filter-item" style="min-width:auto;">
            <label>&nbsp;</label>
            <button class="modern-btn-search" id="btn-search">
                <i class="fa fa-search"></i> Rechercher
            </button>
        </div>
        <?php if (!empty($search) || !empty($date_filter)): ?>
        <div class="filter-item" style="min-width:auto;">
            <label>&nbsp;</label>
            <button class="modern-btn-search" id="btn-clear-search" style="background:var(--danger);">
                <i class="fa fa-times"></i> Effacer
            </button>
        </div>
        <?php endif; ?>
    </div>

    <!-- Table -->
    <div class="modern-table-container">
        <div class="table-header-modern">
            <div class="table-title">
                <i class="fa fa-list"></i> 
                Liste des arrêts des agences
                <?php if (!empty($search) || !empty($date_filter)): ?>
                    (<?php echo $totalArrets; ?> résultat<?php echo $totalArrets > 1 ? 's' : ''; ?>)
                <?php else: ?>
                    (<?php echo $totalArrets; ?>)
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
                        <th class="text-center" width="90">Date Entrée</th>
                        <th class="text-center">Arrêt Info</th>
                        <th class="text-center">Arrêt Douanier</th>
                        <th class="text-center" width="100">Total Caisse</th>
                        <th class="text-center">Diff Douanier / Diff Caisse</th>
                        <th class="text-left">Agence</th>
                        <th class="text-center" width="140">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($arretsPagines)): ?>
                        <?php for($i = 0; $i < count($arretsPagines); $i++): 
                            $arret = $arretsPagines[$i];
                            
                            // Date
                            $dateEntree = isset($arret['dateEntree']) ? $arret['dateEntree'] : '';
                            $dateTimestamp = 0;
                            if (is_object($dateEntree) && method_exists($dateEntree, 'getTimestamp')) {
                                $dateTimestamp = $dateEntree->getTimestamp();
                            } elseif (is_numeric($dateEntree)) {
                                $dateTimestamp = $dateEntree;
                            } else {
                                $dateTimestamp = strtotime($dateEntree);
                            }
                            
                            // Valeurs
                            $arretInfo = isset($arret['arretInfo']) ? number_format($arret['arretInfo'], 0, ',', ' ') : '0';
                            $arretDouanier = isset($arret['arretDouanier']) ? number_format($arret['arretDouanier'], 0, ',', ' ') : '0';
                            $totalCaisse = isset($arret['totalCaisse']) ? number_format($arret['totalCaisse'], 0, ',', ' ') : '0';
                            $diffDouanier = isset($arret['diffDouanier']) ? $arret['diffDouanier'] : '0';
                            $diffCaisse = isset($arret['diffCaisse']) ? $arret['diffCaisse'] : '0';
                            $agence = isset($arret['agence']) ? htmlspecialchars($arret['agence']) : '';
                            
                            // Classes pour les différences
                            $diffDouanierClass = $diffDouanier >= 0 ? 'diff-positif' : 'diff-negatif';
                            $diffCaisseClass = $diffCaisse >= 0 ? 'diff-positif' : 'diff-negatif';
                            
                            $idArretsDouanierSage = isset($arret['idArretsDouanierSage']) ? $arret['idArretsDouanierSage'] : 0;
                        ?>
                        <tr>
                            <td class="text-center">
                                <div style="font-size:12px; font-weight:600;"><?php echo date('d/m/Y', $dateTimestamp); ?></div>
                                <div style="font-size:11px; color:var(--gray);"><?php echo date('H:i', $dateTimestamp); ?></div>
                            </td>
                            <td class="text-right"><strong><?php echo $arretInfo; ?></strong></td>
                            <td class="text-right"><strong><?php echo $arretDouanier; ?></strong></td>
                            <td class="text-center"><span class="total-cell" style="background:var(--primary-light); padding:4px 8px; border-radius:12px;"><?php echo $totalCaisse; ?></span></td>
                            <td class="text-center">
                                <span class="diff-cell <?php echo $diffDouanierClass; ?>"><?php echo number_format($diffDouanier, 0, ',', ' '); ?></span> / 
                                <span class="diff-cell <?php echo $diffCaisseClass; ?>"><?php echo number_format($diffCaisse, 0, ',', ' '); ?></span>
                            </td>
                            <td><?php echo $agence; ?></td>
                            <td class="text-center">
                                <div class="action-icons">
                                    <!-- Détails arrêt -->
                                    <a class="detailsArretsSage action-link primary" href="#" 
                                       data-url="<?php echo App::url('ajax.arretsDouanierSage.detailsArretsDouanierSage'); ?>" 
                                       data-id="<?php echo $idArretsDouanierSage; ?>" 
                                       title="Détails de l'arrêt">
                                        <i class="fa fa-info-circle"></i>
                                    </a>
                                    
                                    <!-- Observations contrôle -->
                                    <a class="miseJourControleSage action-link green" href="#" 
                                       data-url="<?php echo App::url('ajax.arretsDouanierSage.miseJourArretsControleSage'); ?>" 
                                       data-id="<?php echo $idArretsDouanierSage; ?>" 
                                       title="Observations contrôle">
                                        <i class="fa fa-pencil"></i>
                                    </a>
                                    
                                    <!-- Détails contrôle -->
                                    <a class="detailsArretsControlSage action-link grey" href="#" 
                                       data-url="<?php echo App::url('ajax.arretControleSage.detailsArretsControlSage'); ?>" 
                                       data-id="<?php echo $idArretsDouanierSage; ?>" 
                                       title="Détails du contrôle">
                                        <i class="fa fa-clipboard"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endfor; ?>
                    <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center">
                            <div class="empty-state">
                                <i class="fa fa-building"></i>
                                <h4>Aucun arrêt trouvé</h4>
                                <p>
                                    <?php if (!empty($search) || !empty($date_filter)): ?>
                                        Aucun résultat pour les filtres sélectionnés
                                    <?php else: ?>
                                        Aucun arrêt enregistré
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
                Affichage de <?php echo ($totalArrets > 0 ? $startIndex + 1 : 0); ?> à <?php echo $endIndex; ?> sur <?php echo $totalArrets; ?> entrées
            </div>
            <div class="pagination-modern">
                <?php
                if ($page > 1) {
                    $prevUrl = '?p=ArretsDouanierSage.interfaceGestionSage&page=' . ($page - 1);
                    if (!empty($search)) $prevUrl .= '&search=' . urlencode($search);
                    if (!empty($date_filter)) $prevUrl .= '&date_filter=' . urlencode($date_filter);
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
                    $firstUrl = '?p=ArretsDouanierSage.interfaceGestionSage&page=1';
                    if (!empty($search)) $firstUrl .= '&search=' . urlencode($search);
                    if (!empty($date_filter)) $firstUrl .= '&date_filter=' . urlencode($date_filter);
                    echo '<a href="' . $firstUrl . '" class="pagination-item">1</a>';
                    if ($startPage > 2) {
                        echo '<span class="pagination-item disabled">...</span>';
                    }
                }
                
                for ($i = $startPage; $i <= $endPage; $i++) {
                    if ($i == $page) {
                        echo '<span class="pagination-item active">' . $i . '</span>';
                    } else {
                        $pageUrl = '?p=ArretsDouanierSage.interfaceGestionSage&page=' . $i;
                        if (!empty($search)) $pageUrl .= '&search=' . urlencode($search);
                        if (!empty($date_filter)) $pageUrl .= '&date_filter=' . urlencode($date_filter);
                        echo '<a href="' . $pageUrl . '" class="pagination-item">' . $i . '</a>';
                    }
                }
                
                if ($endPage < $totalPages) {
                    if ($endPage < $totalPages - 1) {
                        echo '<span class="pagination-item disabled">...</span>';
                    }
                    $lastUrl = '?p=ArretsDouanierSage.interfaceGestionSage&page=' . $totalPages;
                    if (!empty($search)) $lastUrl .= '&search=' . urlencode($search);
                    if (!empty($date_filter)) $lastUrl .= '&date_filter=' . urlencode($date_filter);
                    echo '<a href="' . $lastUrl . '" class="pagination-item">' . $totalPages . '</a>';
                }
                
                if ($page < $totalPages) {
                    $nextUrl = '?p=ArretsDouanierSage.interfaceGestionSage&page=' . ($page + 1);
                    if (!empty($search)) $nextUrl .= '&search=' . urlencode($search);
                    if (!empty($date_filter)) $nextUrl .= '&date_filter=' . urlencode($date_filter);
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

<!-- MODAL OBSERVATIONS CONTRÔLE -->
<div id="modal-MiseJourArretsGestionSage" class="modal fade">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header modern-modal-header">
                <h5 class="modal-title"><i class="fa fa-clipboard"></i> Observations Contrôle Interne Sage</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body modern-modal-body">
                <form action="<?php echo App::url('ajax.arretControleSage.ajoutArretsControleSage'); ?>" method="POST" id="form-ajoutArretsControle">
                    <input type="hidden" id="idArretsCaisse" name="idArretsCaisse"/>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="modern-form-label"><i class="fa fa-calendar"></i> Date Entrée</label>
                                <input type="text" class="modern-form-control" id="dateArretSage" name="dateArretSage" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="modern-form-label"><i class="fa fa-building"></i> Agence</label>
                                <input type="text" class="modern-form-control" id="agenceArretSage" name="agenceArretSage" readonly>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="modern-form-label"><i class="fa fa-money"></i> Différence Caisse</label>
                                <input type="text" class="modern-form-control" id="diffArretCaisseSage" name="diffArretCaisseSage" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="modern-form-label"><i class="fa fa-calculator"></i> Différence Douanier</label>
                                <input type="text" class="modern-form-control" id="diffArretDouanierSage" name="diffArretDouanierSage" readonly>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="modern-form-label"><i class="fa fa-check-circle"></i> Contrôle Physique</label>
                                <select class="modern-form-control" id="controlePhysSage" name="controlePhysSage">
                                    <option value="">Choisir une option</option>
                                    <option value="Oui">Oui</option>
                                    <option value="Non">Non</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="modern-form-label"><i class="fa fa-comment"></i> Commentaires</label>
                                <textarea class="modern-form-control" id="commentaireControleSage" name="commentaireControleSage" rows="1" placeholder="Entrer les commentaires"></textarea>
                            </div>
                        </div>
                    </div>
                    
                    <h5 style="margin:20px 0 15px; color:var(--primary); border-bottom:1px solid var(--gray-border); padding-bottom:8px;">
                        <i class="fa fa-tasks"></i> Actions à mettre en œuvre
                    </h5>
                    
                    <div class="actions-grid-modal">
                        <div class="form-group">
                            <label class="modern-form-label">Action 1</label>
                            <input type="text" class="modern-form-control" id="Action1Sage" name="Action1Sage" placeholder="Action à réaliser">
                        </div>
                        <div class="form-group">
                            <label class="modern-form-label">Délai 1</label>
                            <input type="date" class="modern-form-control" id="delai1Sage" name="delai1Sage">
                        </div>
                        
                        <div class="form-group">
                            <label class="modern-form-label">Action 2</label>
                            <input type="text" class="modern-form-control" id="Action2Sage" name="Action2Sage" placeholder="Action à réaliser">
                        </div>
                        <div class="form-group">
                            <label class="modern-form-label">Délai 2</label>
                            <input type="date" class="modern-form-control" id="delai2Sage" name="delai2Sage">
                        </div>
                        
                        <div class="form-group">
                            <label class="modern-form-label">Action 3</label>
                            <input type="text" class="modern-form-control" id="Action3Sage" name="Action3Sage" placeholder="Action à réaliser">
                        </div>
                        <div class="form-group">
                            <label class="modern-form-label">Délai 3</label>
                            <input type="date" class="modern-form-control" id="delai3Sage" name="delai3Sage">
                        </div>
                    </div>
                    
                    <div class="alert alert-info" style="background:var(--info-light); border-left:4px solid var(--info); padding:15px; border-radius:8px; margin:20px 0;">
                        <i class="fa fa-info-circle" style="color:var(--info);"></i> 
                        Les actions et délais sont facultatifs. Remplissez uniquement ceux qui sont nécessaires.
                    </div>
                    
                    <div class="text-right">
                        <button type="button" class="btn-modern btn-modern-secondary" data-dismiss="modal">
                            <i class="fa fa-times"></i> Annuler
                        </button>
                        <button type="submit" class="btn-modern btn-modern-primary">
                            <i class="fa fa-save"></i> Enregistrer
                        </button>
                    </div>
                    
                    <div class="modern-loader loaderReset" style="display:none;">
                        <i class="fa fa-spinner fa-spin"></i>
                        <p>Enregistrement en cours...</p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- MODAL DÉTAILS ARRÊT -->
<div id="modal-detailsArretsSage" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header modern-modal-header">
                <h5 class="modal-title"><i class="fa fa-info-circle"></i> Détails de l'arrêt</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body modern-modal-body">
                <div id="loaderArretsSage" class="modern-loader" style="display:none;">
                    <i class="fa fa-spinner fa-spin"></i>
                    <p>Chargement...</p>
                </div>
                <div id="chargerArretsSage"></div>
            </div>
            <div class="modal-footer modern-modal-footer">
                <button class="btn-modern btn-modern-secondary" data-dismiss="modal">
                    <i class="fa fa-times"></i> Fermer
                </button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL DÉTAILS CONTRÔLE -->
<div id="modal-detailsArretsControlSage" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header modern-modal-header">
                <h5 class="modal-title"><i class="fa fa-clipboard"></i> Détails du contrôle</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body modern-modal-body">
                <div id="loaderArretsControlSage" class="modern-loader" style="display:none;">
                    <i class="fa fa-spinner fa-spin"></i>
                    <p>Chargement...</p>
                </div>
                <div id="chargerArretsControlSage"></div>
            </div>
            <div class="modal-footer modern-modal-footer">
                <button class="btn-modern btn-modern-secondary" data-dismiss="modal">
                    <i class="fa fa-times"></i> Fermer
                </button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL MISE À JOUR CONTRÔLE -->
<div id="modal-MiseJourArretControleSage" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header modern-modal-header">
                <h5 class="modal-title"><i class="fa fa-pencil"></i> Mise à jour contrôle interne</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body modern-modal-body">
                <div id="loaderArretsControleSage" class="modern-loader" style="display:none;">
                    <i class="fa fa-spinner fa-spin"></i>
                    <p>Chargement...</p>
                </div>
                <div id="chargerArretsControleSage"></div>
            </div>
            <div class="modal-footer modern-modal-footer">
                <button class="btn-modern btn-modern-secondary" data-dismiss="modal">
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
let currentDateFilter = '<?php echo addslashes($date_filter); ?>';

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

// ============================================
// RECHERCHE ET FILTRES
// ============================================
function effectuerRecherche() {
    const searchInput = document.getElementById('search-arrets');
    const dateInput = document.getElementById('date-filter');
    const searchTerm = searchInput ? searchInput.value.trim() : '';
    const dateFilter = dateInput ? dateInput.value : '';
    
    let url = '?p=ArretsDouanierSage.interfaceGestionSage&page=1';
    if (searchTerm !== '') {
        url += '&search=' + encodeURIComponent(searchTerm);
    }
    if (dateFilter !== '') {
        url += '&date_filter=' + encodeURIComponent(dateFilter);
    }
    
    window.location.href = url;
}

function effacerRecherche() {
    window.location.href = '?p=ArretsDouanierSage.interfaceGestionSage&page=1';
}

// ============================================
// GESTION DES MODALES
// ============================================
function ouvrirModalDetails(selector, url, id, loaderId, contentId) {
    $(loaderId).show();
    $(contentId).hide().empty();
    
    $.ajax({
        url: url,
        type: 'POST',
        data: {id: id},
        dataType: 'json',
        success: function(response) {
            $(loaderId).hide();
            if (response.content) {
                $(contentId).html(response.content).show();
            } else {
                $(contentId).html('<div class="alert alert-danger">Erreur de chargement</div>').show();
            }
        },
        error: function() {
            $(loaderId).hide();
            notif('Erreur de chargement', 'error');
            $(selector).modal('hide');
        }
    });
}

// ============================================
// INITIALISATION
// ============================================
$(document).ready(function() {
    console.log('🚀 Page arrêts des agences initialisée');
    
    // Tooltips
    $('[title]').tooltip({placement:'top', trigger:'hover'});
    
    // Gestion des modales
    $('.modal').on('show.bs.modal', function() { 
        $(this).css('z-index','1050');
        if(!$(this).find('.modal-dialog').hasClass('modal-dialog-centered')) {
            $(this).find('.modal-dialog').css('margin-top','80px');
        }
    });
    
    // Boutons de recherche
    $('#btn-search').click(function(e) {
        e.preventDefault();
        effectuerRecherche();
    });
    
    $('#search-arrets, #date-filter').keypress(function(e) {
        if (e.which === 13) {
            effectuerRecherche();
        }
    });
    
    $('#btn-clear-search').click(function(e) {
        e.preventDefault();
        effacerRecherche();
    });
    
    // Détails arrêt
    $('.detailsArretsSage').click(function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        var url = $(this).data('url');
        ouvrirModalDetails('#modal-detailsArretsSage', url, id, '#loaderArretsSage', '#chargerArretsSage');
        $('#modal-detailsArretsSage').modal('show');
    });
    
    // Détails contrôle
    $('.detailsArretsControlSage').click(function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        var url = $(this).data('url');
        ouvrirModalDetails('#modal-detailsArretsControlSage', url, id, '#loaderArretsControlSage', '#chargerArretsControlSage');
        $('#modal-detailsArretsControlSage').modal('show');
    });
    
    // Observations contrôle - Remplir les champs avant ouverture
    $('.miseJourControleSage').click(function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        var url = $(this).data('url');
        
        $('#loaderArretsControleSage').show();
        $('#chargerArretsControleSage').hide().empty();
        
        $.ajax({
            url: url,
            type: 'POST',
            data: {id: id},
            dataType: 'json',
            success: function(response) {
                $('#loaderArretsControleSage').hide();
                if (response.content) {
                    $('#chargerArretsControleSage').html(response.content).show();
                } else {
                    $('#chargerArretsControleSage').html('<div class="alert alert-danger">Erreur de chargement</div>').show();
                }
            },
            error: function() {
                $('#loaderArretsControleSage').hide();
                notif('Erreur de chargement', 'error');
            }
        });
        
        $('#modal-MiseJourArretControleSage').modal('show');
    });
    
    // Formulaire d'observations (modal existant)
    $('#form-ajoutArretsControle').submit(function(e) {
        e.preventDefault();
        
        var formData = $(this).serialize();
        var loader = $('.loaderReset');
        
        loader.show();
        
        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                loader.hide();
                
                if (response.success) {
                    $('#modal-MiseJourArretsGestionSage').modal('hide');
                    notif('Observations enregistrées avec succès', 'success');
                    
                    setTimeout(function() {
                        let url = '?p=ArretsDouanierSage.interfaceGestionSage&page=' + currentPage;
                        if (currentSearch !== '') {
                            url += '&search=' + encodeURIComponent(currentSearch);
                        }
                        if (currentDateFilter !== '') {
                            url += '&date_filter=' + encodeURIComponent(currentDateFilter);
                        }
                        window.location.href = url;
                    }, 1500);
                    
                } else {
                    notif(response.message || 'Erreur lors de l\'enregistrement', 'error');
                }
            },
            error: function() {
                loader.hide();
                notif('Erreur de communication avec le serveur', 'error');
            }
        });
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