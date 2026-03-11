<?php
use Core\Model\App;
use Core\Model\Session;

$auth = App::getDBAuth();
$session = Session::getInstance();

$user = $_SESSION['user'];

// Récupération des paramètres
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$dateFilter = isset($_GET['date_filter']) ? trim($_GET['date_filter']) : '';

// Filtrer les arrêts douaniers par recherche si nécessaire
$arretsDouaniersFiltres = array();
if (!empty($arretsDouaniers) && is_array($arretsDouaniers)) {
    foreach ($arretsDouaniers as $arret) {
        $found = false;
        
        if (!empty($search)) {
            $searchFields = ['agence', 'arretInfo', 'arretDouanier', 'versements'];
            foreach ($searchFields as $field) {
                if (isset($arret[$field]) && stripos($arret[$field], $search) !== false) {
                    $found = true;
                    break;
                }
            }
            
            // Recherche dans les montants
            $montantFields = ['totalCaisse', 'diffDouanier', 'MontantVerse'];
            foreach ($montantFields as $field) {
                if (isset($arret[$field]) && stripos((string)$arret[$field], $search) !== false) {
                    $found = true;
                    break;
                }
            }
        }
        
        // Filtre par date
        if (!empty($dateFilter) && isset($arret['dateEntree'])) {
            $dateArret = '';
            if (is_object($arret['dateEntree']) && method_exists($arret['dateEntree'], 'format')) {
                $dateArret = $arret['dateEntree']->format('Y-m-d');
            } elseif (is_string($arret['dateEntree'])) {
                $dateArret = date('Y-m-d', strtotime($arret['dateEntree']));
            }
            
            if ($dateArret != $dateFilter) {
                if (!empty($search)) {
                    if (!$found) continue;
                } else {
                    continue;
                }
            }
        }
        
        if (empty($search) && empty($dateFilter)) {
            $arretsDouaniersFiltres[] = $arret;
        } elseif (!empty($search) && $found) {
            $arretsDouaniersFiltres[] = $arret;
        } elseif (!empty($dateFilter) && !empty($search) && $found) {
            $arretsDouaniersFiltres[] = $arret;
        }
    }
} else {
    $arretsDouaniersFiltres = $arretsDouaniers ?: array();
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

// Rôles autorisés pour les actions
$isCompta = in_array($user['privilege'], ['Comptabilite']);
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

.arrets-douane-container { max-width:100%; margin:0 auto; font-family:'Inter',sans-serif; }
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
.legend-color.info { background:var(--info); }
.legend-color.warning { background:var(--warning); }

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

/* Barre de recherche et filtre moderne */
.filter-wrapper {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    align-items: flex-end;
}
.search-wrapper {
    width: 300px;
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
.date-filter {
    width: 200px;
}
.date-input {
    padding: 10px 15px;
    border: 1px solid #dce4ec;
    border-radius: 25px;
    background: #f8f9fa;
    font-size: 14px;
    outline: none;
    transition: all 0.3s ease;
    width: 100%;
}
.date-input:focus {
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(67,97,238,0.1);
}
.filter-submit {
    background: linear-gradient(135deg, var(--success), #27ae60);
    color: white;
    border: none;
    border-radius: 25px;
    padding: 10px 20px;
    cursor: pointer;
    transition: all 0.3s ease;
}
.filter-submit:hover {
    opacity: 0.9;
}
.clear-filters {
    background: #f8d7da;
    border: none;
    color: #721c24;
    border-radius: 25px;
    padding: 10px 15px;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}
.clear-filters:hover {
    background: #f5c6cb;
    color: #721c24;
    text-decoration: none;
}

#dynamic-table { width:100% !important; font-size:13px; }
#dynamic-table thead th { background:var(--gray-light); color:var(--dark); font-weight:600; font-size:11px; padding:12px 8px; }
#dynamic-table tbody td { padding:12px 8px; border-bottom:1px solid var(--gray-border); vertical-align:middle; }
#dynamic-table tbody tr:hover { background:var(--gray-light); }

.diff-positive { color: var(--success); font-weight:600; }
.diff-negative { color: var(--danger); font-weight:600; }
.total-cell { font-weight:700; color:var(--primary); }

.action-icons { display:flex; gap:8px; flex-wrap: wrap; justify-content: center; }
.action-link { display:inline-flex; align-items:center; justify-content:center; text-decoration:none; width:32px; height:32px; border-radius:4px; transition:all 0.2s ease; }
.action-link:hover { transform:translateY(-2px); box-shadow:var(--shadow); text-decoration:none; }
.action-link.purple i { color:#8e44ad; }
.action-link.green i { color:var(--success); }
.action-link.blue i { color:var(--info); }
.action-link.orange i { color:var(--warning); }
.action-link.teal i { color:#008080; }

.modal-header.modern-modal-header { background:linear-gradient(135deg,#0d1b3e,#1a2b5c); padding:20px 25px; border-radius:12px 12px 0 0; }
.modern-modal-header .modal-title { color:white; font-weight:600; font-size:16px; display:flex; align-items:center; gap:12px; }
.modern-modal-header .close { color:white; opacity:0.8; width:32px; height:32px; background:rgba(255,255,255,0.1); border-radius:50%; display:flex; align-items:center; justify-content:center; border:none; }
.modern-modal-header .close:hover { opacity:1; background:rgba(255,255,255,0.2); }
.modal-body.modern-modal-body { padding:25px; background:#f8fafc; max-height:70vh; overflow-y:auto; }
.modal-footer.modern-modal-footer { padding:20px 25px; background:white; border-top:1px solid #e9ecef; border-radius:0 0 12px 12px; display:flex; gap:10px; justify-content:flex-end; }

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
    .filter-wrapper { flex-direction:column; align-items:stretch; }
    .search-wrapper, .date-filter { width:100%; }
    .modal-dialog { margin:10px !important; max-width:calc(100% - 20px) !important; } 
    .stats-grid { justify-content:center; }
    .pagination-modern { justify-content:center; }
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

<div class="arrets-douane-container">
    <!-- Fil d'Ariane moderne -->
    <div class="modern-breadcrumbs" style="margin-bottom:15px;">
        <ul class="breadcrumb" style="background:none; padding:0;">
            <li style="display:inline-block;">
                <i class="fa fa-home" style="color:var(--primary);"></i>
                <a href="#" onclick="return naviguerVers('<?php echo App::url("dashboard"); ?>')" style="color:var(--dark);">Arrêts Agences</a>
            </li>
            <li style="display:inline-block; margin-left:8px;">
                <i class="fa fa-angle-right" style="color:var(--gray);"></i>
                Liste des arrêts douaniers
            </li>
        </ul>
    </div>

    <!-- Header avec statistiques -->
    <div class="modern-header-banner">
        <h1 class="banner-title"><i class="fa fa-calculator"></i> ARRÊTS DOUANIERS</h1>
        <p class="banner-subtitle">Gestion des arrêts douaniers • Suivi des différences</p>
        
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
            <div class="legend-item"><i class="fa fa-info-circle" style="color:var(--info);"></i><span>Détails arrêt</span></div>
            <div class="legend-item"><i class="fa fa-file-signature" style="color:var(--success);"></i><span>Observations</span></div>
            <div class="legend-item"><i class="fa fa-upload" style="color:var(--warning);"></i><span>Bordereau</span></div>
        </div>
    </div>

    <!-- Filtres et recherche -->
    <div style="margin-bottom:20px;">
        <form action="<?php echo App::url('ArretsDouanier.interfaceCpta'); ?>" method="POST" class="filter-wrapper">
            <div class="search-wrapper">
                <label class="modern-form-label"><i class="fa fa-search"></i> Recherche</label>
                <div class="search-box">
                    <span class="search-icon"><i class="fa fa-search" style="color:#95a5a6;"></i></span>
                    <input type="text" name="search" class="search-input" id="search-arrets" 
                           placeholder="Agence, montant, versement..."
                           value="<?php echo htmlspecialchars($search); ?>">
                </div>
            </div>
            
            <div class="date-filter">
                <label class="modern-form-label"><i class="fa fa-calendar"></i> Date</label>
                <input type="date" name="date_filter" id="date-filter" class="date-input" 
                       value="<?php echo htmlspecialchars($dateFilter); ?>">
            </div>
            
            <div style="display:flex; gap:10px; align-items:center; margin-bottom:5px;">
                <button type="submit" class="filter-submit">
                    <i class="fa fa-filter"></i> Filtrer
                </button>
                
                <?php if (!empty($search) || !empty($dateFilter)): ?>
                <a href="<?php echo App::url('ArretsDouanier.interfaceCpta'); ?>" class="clear-filters">
                    <i class="fa fa-times-circle"></i> Effacer
                </a>
                <?php endif; ?>
            </div>
        </form>
        <small class="text-muted">Recherche par agence, montant, versement • Filtrage par date</small>
    </div>

    <!-- Actions -->
    <div class="actions-section">
        <div class="actions-grid">
            <a href="<?php echo App::url('ArretsDouanier.ArretsCaissesPrint'); ?>" class="modern-action-btn print-btn">
                <i class="fa fa-print btn-icon"></i>
                <span class="btn-text">Imprimer</span>
                <span class="btn-subtext">Liste des arrêts</span>
            </a>
        </div>
    </div>

    <!-- Table -->
    <div class="modern-table-container">
        <div class="table-header-modern">
            <div class="table-title">
                <i class="fa fa-list"></i> 
                Liste des arrêts des agences
                <?php if (!empty($search) || !empty($dateFilter)): ?>
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
                        <th class="text-left">Arrêt Info / Arrêt Douanier</th>
                        <th class="text-center" width="100">Total Caisse</th>
                        <th class="text-center" width="100">Diff Douanier</th>
                        <th class="text-left">Versement / Montant</th>
                        <th class="text-left">Agence</th>
                        <th class="text-center" width="140">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($arretsPagines)): ?>
                        <?php for($i = 0; $i < count($arretsPagines); $i++): 
                            $arret = $arretsPagines[$i];
                            
                            $dateEntree = isset($arret['dateEntree']) ? $arret['dateEntree'] : '';
                            $dateTimestamp = 0;
                            if (is_object($dateEntree) && method_exists($dateEntree, 'getTimestamp')) {
                                $dateTimestamp = $dateEntree->getTimestamp();
                            } elseif (is_numeric($dateEntree)) {
                                $dateTimestamp = $dateEntree;
                            } else {
                                $dateTimestamp = strtotime($dateEntree);
                            }
                            
                            $arretInfo = isset($arret['arretInfo']) ? number_format($arret['arretInfo'], 0, ',', ' ') : '0';
                            $arretDouanier = isset($arret['arretDouanier']) ? number_format($arret['arretDouanier'], 0, ',', ' ') : '0';
                            $totalCaisse = isset($arret['totalCaisse']) ? number_format($arret['totalCaisse'], 0, ',', ' ') : '0';
                            $diffDouanier = isset($arret['diffDouanier']) ? $arret['diffDouanier'] : 0;
                            $versements = isset($arret['versements']) ? htmlspecialchars($arret['versements']) : '';
                            $montantVerse = isset($arret['MontantVerse']) ? number_format($arret['MontantVerse'], 0, ',', ' ') : '0';
                            $agence = isset($arret['agence']) ? htmlspecialchars($arret['agence']) : '';
                            $idArretsDouanier = isset($arret['idArretsDouanier']) ? $arret['idArretsDouanier'] : 0;
                            
                            $bordereau1 = isset($arret['bordereauVersement']) ? explode("!", $arret['bordereauVersement']) : ['Aucun'];
                            $bordereau = "http://192.168.0.13:8088/ArretsCaisses/DocumentsBordereau/" . $bordereau1[0];
                            
                            $diffClass = $diffDouanier >= 0 ? 'diff-positive' : 'diff-negative';
                            $diffDisplay = number_format($diffDouanier, 0, ',', ' ');
                        ?>
                        <tr>
                            <td class="text-center">
                                <div style="font-size:12px; font-weight:600;"><?php echo date('d/m/Y', $dateTimestamp); ?></div>
                                <div style="font-size:11px; color:var(--gray);"><?php echo date('H:i', $dateTimestamp); ?></div>
                            </td>
                            <td>
                                <div><span class="montant-cell"><?php echo $arretInfo; ?></span></div>
                                <div style="font-size:11px; color:var(--gray);">Douanier: <?php echo $arretDouanier; ?></div>
                            </td>
                            <td class="text-center">
                                <span class="total-cell"><?php echo $totalCaisse; ?></span>
                            </td>
                            <td class="text-center">
                                <span class="<?php echo $diffClass; ?>"><?php echo $diffDisplay; ?></span>
                            </td>
                            <td>
                                <div><?php echo $versements; ?></div>
                                <div style="font-size:11px; color:var(--gray);">Montant: <?php echo $montantVerse; ?></div>
                            </td>
                            <td><?php echo $agence; ?></td>
                            <td class="text-center">
                                <div class="action-icons">
                                    <!-- Détails arrêt -->
                                    <a href="#" class="detailsArrets action-link purple" data-url="<?php echo App::url('ajax.arretsDouanier.detailsArretsDouanier'); ?>" data-id="<?php echo $idArretsDouanier; ?>" title="Détails de l'arrêt">
                                        <i class="fa fa-info-circle"></i>
                                    </a>
                                    
                                    <!-- Détails versement -->
                                    <a href="#" class="detailsArretsVerse action-link blue" data-url="<?php echo App::url('ajax.arretsDouanier.detailsArretsDouanierVerse'); ?>" data-id="<?php echo $idArretsDouanier; ?>" title="Détails du versement">
                                        <i class="fa fa-money"></i>
                                    </a>
                                    
                                    <?php if($isCompta): ?>
                                    <!-- Observations comptabilité -->
                                    <a href="#" class="miseJourCpta action-link green" data-url="<?php echo App::url('ajax.arretsDouanier.miseJourArretsCpta'); ?>" data-id="<?php echo $idArretsDouanier; ?>" title="Observations comptabilité">
                                        <i class="fa fa-file-signature"></i>
                                    </a>
                                    <?php endif; ?>
                                    
                                    <!-- Téléchargement bordereau -->
                                    <?php if(strcmp($bordereau1[0], 'Aucun') !== 0): ?>
                                    <a href="<?php echo $bordereau; ?>" download="<?php echo $bordereau1[0]; ?>" target="_blank" class="action-link orange" title="Télécharger le bordereau">
                                        <i class="fa fa-download"></i>
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endfor; ?>
                    <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center">
                            <div class="empty-state">
                                <i class="fa fa-calculator"></i>
                                <h4>Aucun arrêt douanier trouvé</h4>
                                <p>
                                    <?php if (!empty($search) || !empty($dateFilter)): ?>
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
                $baseUrl = '?p=ArretsDouanier.interfaceCpta&page=';
                if (!empty($search)) $baseUrl .= '&search=' . urlencode($search);
                if (!empty($dateFilter)) $baseUrl .= '&date_filter=' . urlencode($dateFilter);
                
                if ($page > 1) {
                    $prevUrl = $baseUrl . ($page - 1);
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
                    $firstUrl = $baseUrl . '1';
                    echo '<a href="' . $firstUrl . '" class="pagination-item">1</a>';
                    if ($startPage > 2) {
                        echo '<span class="pagination-item disabled">...</span>';
                    }
                }
                
                for ($i = $startPage; $i <= $endPage; $i++) {
                    if ($i == $page) {
                        echo '<span class="pagination-item active">' . $i . '</span>';
                    } else {
                        $pageUrl = $baseUrl . $i;
                        echo '<a href="' . $pageUrl . '" class="pagination-item">' . $i . '</a>';
                    }
                }
                
                if ($endPage < $totalPages) {
                    if ($endPage < $totalPages - 1) {
                        echo '<span class="pagination-item disabled">...</span>';
                    }
                    $lastUrl = $baseUrl . $totalPages;
                    echo '<a href="' . $lastUrl . '" class="pagination-item">' . $totalPages . '</a>';
                }
                
                if ($page < $totalPages) {
                    $nextUrl = $baseUrl . ($page + 1);
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

<!-- MODAL DÉTAILS ARRÊT -->
<div id="modal-detailsArrets" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header modern-modal-header">
                <h5 class="modal-title"><i class="fa fa-info-circle"></i> Détails de l'arrêt</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body modern-modal-body">
                <div id="loaderArrets" class="modern-loader" style="display:none;">
                    <i class="fa fa-spinner fa-spin"></i>
                    <p>Chargement des détails...</p>
                </div>
                <div id="chargerArrets"></div>
            </div>
            <div class="modal-footer modern-modal-footer">
                <button class="btn-modern btn-modern-secondary" data-dismiss="modal">
                    <i class="fa fa-times"></i> Fermer
                </button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL DÉTAILS VERSEMENT -->
<div id="modal-detailsArretsVerse" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header modern-modal-header">
                <h5 class="modal-title"><i class="fa fa-money"></i> Détails du versement</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body modern-modal-body">
                <div id="loaderArretsVerse" class="modern-loader" style="display:none;">
                    <i class="fa fa-spinner fa-spin"></i>
                    <p>Chargement des détails...</p>
                </div>
                <div id="chargerArretsVerse"></div>
            </div>
            <div class="modal-footer modern-modal-footer">
                <button class="btn-modern btn-modern-secondary" data-dismiss="modal">
                    <i class="fa fa-times"></i> Fermer
                </button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL OBSERVATIONS COMPTABILITÉ -->
<div id="modal-MiseJourArretsCpta" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header modern-modal-header">
                <h5 class="modal-title"><i class="fa fa-file-signature"></i> Observations Comptabilité</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body modern-modal-body">
                <div id="loaderObsArretCpta" class="modern-loader" style="display:none;">
                    <i class="fa fa-spinner fa-spin"></i>
                    <p>Chargement...</p>
                </div>
                <div id="chargerObsArretCpta"></div>
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
let currentDateFilter = '<?php echo addslashes($dateFilter); ?>';

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

// Fonction pour extraire le HTML des réponses AJAX
function extractHtmlFromResponse(response) {
    if (typeof response === 'string') {
        try {
            var parsed = JSON.parse(response);
            if (parsed && parsed.content) {
                return parsed.content;
            }
        } catch(e) {
            return response;
        }
    }
    
    if (typeof response === 'object' && response !== null) {
        if (response.content) {
            return response.content;
        }
    }
    
    return response;
}

// ============================================
// GESTION DES MODALES
// ============================================
$(document).ready(function() {
    console.log('🚀 Page arrêts douaniers initialisée');
    
    // Tooltips
    $('[title]').tooltip({placement:'top', trigger:'hover'});
    
    // Gestion des modales
    $('.modal').on('show.bs.modal', function() { 
        $(this).css('z-index','1050');
        if(!$(this).find('.modal-dialog').hasClass('modal-dialog-centered')) {
            $(this).find('.modal-dialog').css('margin-top','80px');
        }
    });
    
    // Détails arrêt
    $('.detailsArrets').click(function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        var url = $(this).data('url');
        
        $('#loaderArrets').show();
        $('#chargerArrets').hide().empty();
        $('#modal-detailsArrets').modal('show');
        
        $.ajax({
            url: url,
            type: 'POST',
            data: {id: id},
            dataType: 'json',
            success: function(response) {
                var htmlContent = extractHtmlFromResponse(response);
                $('#chargerArrets').html(htmlContent).show();
                $('#loaderArrets').hide();
            },
            error: function() {
                notif('Erreur chargement des détails', 'error');
                $('#loaderArrets').hide();
                $('#modal-detailsArrets').modal('hide');
            }
        });
    });
    
    // Détails versement
    $('.detailsArretsVerse').click(function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        var url = $(this).data('url');
        
        $('#loaderArretsVerse').show();
        $('#chargerArretsVerse').hide().empty();
        $('#modal-detailsArretsVerse').modal('show');
        
        $.ajax({
            url: url,
            type: 'POST',
            data: {id: id},
            dataType: 'json',
            success: function(response) {
                var htmlContent = extractHtmlFromResponse(response);
                $('#chargerArretsVerse').html(htmlContent).show();
                $('#loaderArretsVerse').hide();
            },
            error: function() {
                notif('Erreur chargement des détails', 'error');
                $('#loaderArretsVerse').hide();
                $('#modal-detailsArretsVerse').modal('hide');
            }
        });
    });
    
    // Observations comptabilité
    $('.miseJourCpta').click(function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        var url = $(this).data('url');
        
        $('#loaderObsArretCpta').show();
        $('#chargerObsArretCpta').hide().empty();
        $('#modal-MiseJourArretsCpta').modal('show');
        
        $.ajax({
            url: url,
            type: 'POST',
            data: {id: id},
            dataType: 'json',
            success: function(response) {
                var htmlContent = extractHtmlFromResponse(response);
                $('#chargerObsArretCpta').html(htmlContent).show();
                $('#loaderObsArretCpta').hide();
            },
            error: function() {
                notif('Erreur chargement', 'error');
                $('#loaderObsArretCpta').hide();
                $('#modal-MiseJourArretsCpta').modal('hide');
            }
        });
    });
    
    // Soumission du formulaire de filtre
    $('#filter-form').submit(function(e) {
        // Le formulaire est soumis normalement
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