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
$dateFilter = isset($_SESSION['search0']) ? $_SESSION['search0'] : '';

// Filtrer les arrêts par recherche si nécessaire
$arretsDouaniersFiltres = array();
if (!empty($arretsDouaniers) && is_array($arretsDouaniers)) {
    if (!empty($search)) {
        foreach ($arretsDouaniers as $arret) {
            $found = false;
            $searchFields = ['agence', 'arretInfo', 'arretDouanier'];
            
            foreach ($searchFields as $field) {
                if (isset($arret[$field]) && stripos($arret[$field], $search) !== false) {
                    $found = true;
                    break;
                }
            }
            
            // Recherche dans les montants
            $montantFields = ['totalCaisse', 'diffDouanier', 'diffCaisse'];
            foreach ($montantFields as $field) {
                if (isset($arret[$field]) && stripos((string)$arret[$field], $search) !== false) {
                    $found = true;
                    break;
                }
            }
            
            if ($found) {
                $arretsDouaniersFiltres[] = $arret;
            }
        }
    } else {
        $arretsDouaniersFiltres = $arretsDouaniers;
    }
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

.arrets-container { max-width:100%; margin:0 auto; font-family:'Inter',sans-serif; }
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
.legend-color.purple { background:#8e44ad; }

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

/* Filtre date */
.date-filter {
    min-width: 200px;
}
.date-filter-input {
    padding: 10px 15px;
    border: 1px solid #dce4ec;
    border-radius: 25px;
    background: #f8f9fa;
    font-size: 14px;
    outline: none;
    transition: all 0.3s ease;
}
.date-filter-input:focus {
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(67,97,238,0.1);
}
.search-date-btn {
    background: linear-gradient(135deg, #f39c12, #e67e22);
    color: white;
    border: none;
    border-radius: 25px;
    padding: 10px 20px;
    margin-left: 10px;
    cursor: pointer;
    transition: all 0.3s ease;
}
.search-date-btn:hover {
    opacity: 0.9;
}

#print-btn {
    background: linear-gradient(135deg, #2ecc71, #27ae60);
    color: white;
    border: none;
    border-radius: 25px;
    padding: 10px 20px;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    text-decoration: none;
    transition: all 0.3s ease;
}
#print-btn:hover {
    opacity: 0.9;
    text-decoration: none;
    color: white;
}

#dynamic-table { width:100% !important; font-size:13px; }
#dynamic-table thead th { background:var(--gray-light); color:var(--dark); font-weight:600; font-size:11px; padding:12px 8px; }
#dynamic-table tbody td { padding:12px 8px; border-bottom:1px solid var(--gray-border); vertical-align:middle; }
#dynamic-table tbody tr:hover { background:var(--gray-light); }

.diff-positive { color: var(--success); font-weight:600; }
.diff-negative { color: var(--danger); font-weight:600; }

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
.modern-form-label .required { color:var(--danger); margin-left:3px; }

.btn-modern { padding:10px 24px; border-radius:8px; font-weight:600; font-size:14px; border:none; cursor:pointer; transition:all 0.3s ease; display:inline-flex; align-items:center; gap:8px; }
.btn-modern-primary { background:linear-gradient(135deg,#4361ee,#3a56d4); color:white; }
.btn-modern-primary:hover { background:linear-gradient(135deg,#3a56d4,#2c3e50); color:white; }
.btn-modern-secondary { background:#f8f9fa; color:#6c757d; border:1px solid #dee2e6; }
.btn-modern-secondary:hover { background:#e9ecef; }
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

/* Grille pour les actions */
.actions-grid-form {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 15px;
    margin-bottom: 20px;
}

/* FontAwesome Fixes pour FA4 */
.fa {
    font-family: FontAwesome !important;
}

@media (max-width:768px) { 
    .table-header-modern { flex-direction:column; align-items:flex-start; } 
    .search-wrapper { width:100%; }
    .modal-dialog { margin:10px !important; max-width:calc(100% - 20px) !important; } 
    .stats-grid { justify-content:center; }
    .pagination-modern { justify-content:center; }
    .actions-grid-form { grid-template-columns: 1fr; }
}
@media (max-width:576px) { 
    .modern-header-banner,.modern-table-container { padding:15px; } 
    .modal-footer.modern-modal-footer { flex-direction:column; } 
    .btn-modern { width:100%; justify-content:center; } 
    .stats-grid { flex-direction:column; }
    .stat-item { width:100%; }
}
</style>

<div class="arrets-container">
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
            <?php if (!empty($search)): ?>
            <li style="display:inline-block; margin-left:8px;">
                <i class="fa fa-angle-right" style="color:var(--gray);"></i>
                <i class="fa fa-search" style="color:var(--warning);"></i>
                Recherche: "<?php echo htmlspecialchars($search); ?>"
            </li>
            <?php endif; ?>
            <?php if (!empty($dateFilter)): ?>
            <li style="display:inline-block; margin-left:8px;">
                <i class="fa fa-angle-right" style="color:var(--gray);"></i>
                <i class="fa fa-calendar" style="color:var(--info);"></i>
                Date: <?php echo htmlspecialchars($dateFilter); ?>
            </li>
            <?php endif; ?>
        </ul>
    </div>

    <!-- Header avec statistiques -->
    <div class="modern-header-banner">
        <h1 class="banner-title"><i class="fa fa-building"></i> ARRÊTS DES AGENCES</h1>
        <p class="banner-subtitle">Gestion et suivi des arrêts des agences • Contrôle interne</p>
        
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
    <div style="margin-bottom:20px;">
        <div style="display:flex; flex-wrap:wrap; gap:15px; align-items:flex-end; justify-content:space-between;">
            <div style="display:flex; flex-wrap:wrap; gap:15px; flex:1;">
                <!-- Filtre par date -->
                <div class="date-filter">
                    <label class="modern-form-label"><i class="fa fa-calendar"></i> Filtre par date</label>
                    <form action="<?php echo App::url('ArretsDouanier.interfaceGestion'); ?>" method="POST" style="display:flex;">
                        <input type="date" class="date-filter-input" id="search0" name="search0" value="<?php echo htmlspecialchars($dateFilter); ?>" placeholder="Sélectionner une date">
                        <button type="submit" class="search-date-btn">
                            <i class="fa fa-search"></i>
                        </button>
                    </form>
                </div>
                
                <!-- Recherche textuelle -->
                <div style="flex:1; min-width:300px;">
                    <label class="modern-form-label"><i class="fa fa-search"></i> Recherche</label>
                    <div class="search-wrapper">
                        <div class="search-box">
                            <span class="search-icon"><i class="fa fa-search" style="color:#95a5a6;"></i></span>
                            <input type="text" id="search-arrets" class="search-input" 
                                   placeholder="Rechercher par agence, montant, date..."
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
            
            <!-- Bouton Imprimer -->
            <div>
                <a href="<?php echo App::url('ArretsDouanier.ArretsCaissesPrint0'); ?>" class="btn-modern btn-modern-success" id="print-btn">
                    <i class="fa fa-print"></i> Imprimer
                </a>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="modern-table-container">
        <div class="table-header-modern">
            <div class="table-title">
                <i class="fa fa-list"></i> 
                Liste des arrêts des agences
                <?php if (!empty($search)): ?>
                    (<?php echo $totalArrets; ?> résultat<?php echo $totalArrets > 1 ? 's' : ''; ?>)
                <?php elseif (!empty($dateFilter)): ?>
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
                        <th class="text-center">Total Caisse</th>
                        <th class="text-center">Diff Douanier / Diff Caisse</th>
                        <th class="text-left">Agence</th>
                        <th class="text-center" width="120">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($arretsPagines)): ?>
                        <?php for($i = 0; $i < count($arretsPagines); $i++): 
                            $arret = $arretsPagines[$i];
                            
                            // Récupérer les données de contrôle et actions
                            $stmtArretControle = ArretControle::SearchByArret($arret['idArretsDouanier']);
                            $arretControle = 'Test 01';
                            $actions = 'Test 02';
                            while ($result3 = sqlsrv_fetch_array($stmtArretControle, SQLSRV_FETCH_ASSOC)) {
                                
                                $stmtActions = ActionsArrets::SearchByArret($result3['idArretControle']);
                                
                                while ($result4 = sqlsrv_fetch_array($stmtActions, SQLSRV_FETCH_ASSOC)) {
                                    $delai = date('d-m-Y', date_timestamp_get($result4['delai']));
                                    $actions .= '_' . $result4['idActionsArrets'] . '!' . $delai . '!' . $result4['designation'];
                                }
                                $arretControle .= '_' . $result3['idArretControle'] . '!' . $result3['controlePhysique'] . '!' . $result3['commentaires'];
                            }
                            
                            // Déterminer la classe pour la date
                            $dateEntree = isset($arret['dateEntree']) ? $arret['dateEntree'] : '';
                            $dateTimestamp = 0;
                            if (is_object($dateEntree) && method_exists($dateEntree, 'getTimestamp')) {
                                $dateTimestamp = $dateEntree->getTimestamp();
                            } elseif (is_numeric($dateEntree)) {
                                $dateTimestamp = $dateEntree;
                            } else {
                                $dateTimestamp = strtotime($dateEntree);
                            }
                            
                            $arretInfo = isset($arret['arretInfo']) ? $arret['arretInfo'] : '0';
                            $arretDouanier = isset($arret['arretDouanier']) ? $arret['arretDouanier'] : '0';
                            $totalCaisse = isset($arret['totalCaisse']) ? number_format($arret['totalCaisse'], 0, ',', ' ') : '0';
                            $diffDouanier = isset($arret['diffDouanier']) ? $arret['diffDouanier'] : '0';
                            $diffCaisse = isset($arret['diffCaisse']) ? $arret['diffCaisse'] : '0';
                            $agence = isset($arret['agence']) ? htmlspecialchars($arret['agence']) : '';
                            
                            // Déterminer la classe pour les différences
                            $diffDouanierClass = $diffDouanier > 0 ? 'diff-positive' : ($diffDouanier < 0 ? 'diff-negative' : '');
                            $diffCaisseClass = $diffCaisse > 0 ? 'diff-positive' : ($diffCaisse < 0 ? 'diff-negative' : '');
                        ?>
                        <tr>
                            <td class="text-center">
                                <div style="font-size:12px; font-weight:600;"><?php echo date('d/m/Y', $dateTimestamp); ?></div>
                                <div style="font-size:11px; color:var(--gray);"><?php echo date('H:i', $dateTimestamp); ?></div>
                            </td>
                            <td class="text-center montant-cell"><?php echo $arretInfo; ?></td>
                            <td class="text-center montant-cell"><?php echo $arretDouanier; ?></td>
                            <td class="text-center"><span class="total-cell"><?php echo $totalCaisse; ?></span></td>
                            <td class="text-center">
                                <span class="<?php echo $diffDouanierClass; ?>"><?php echo $diffDouanier; ?></span> / 
                                <span class="<?php echo $diffCaisseClass; ?>"><?php echo $diffCaisse; ?></span>
                            </td>
                            <td><?php echo $agence; ?></td>
                            <td class="text-center">
                                <div class="action-icons">
                                    <a class="detailsArrets action-link primary" href="#" 
                                       data-url="<?php echo App::url('ajax.arretsDouanier.detailsArretsDouanier'); ?>" 
                                       data-id="<?php echo $arret['idArretsDouanier']; ?>" 
                                       title="Détails de l'arrêt">
                                        <i class="fa fa-info-circle"></i>
                                    </a>
                                    
                                    <a class="miseJourControleTest action-link green" href="#" 
                                       data-url="<?php echo App::url('ajax.arretsDouanier.miseJourArretsControle'); ?>" 
                                       data-id="<?php echo $arret['idArretsDouanier']; ?>" 
                                       title="Observations contrôle">
                                        <i class="fa fa-pencil"></i>
                                    </a>
                                    
                                    <a class="detailsArretsControl action-link grey" href="#" 
                                       data-url="<?php echo App::url('ajax.arretControle.detailsArretsControl'); ?>" 
                                       data-id="<?php echo $arret['idArretsDouanier']; ?>" 
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
                                    <?php if (!empty($search)): ?>
                                        Aucun résultat pour "<?php echo htmlspecialchars($search); ?>"
                                    <?php elseif (!empty($dateFilter)): ?>
                                        Aucun résultat pour la date "<?php echo htmlspecialchars($dateFilter); ?>"
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
                    $prevUrl = '?p=ArretsDouanier.interfaceGestion&page=' . ($page - 1);
                    if (!empty($search)) $prevUrl .= '&search=' . urlencode($search);
                    if (!empty($dateFilter)) $prevUrl .= '&date=' . urlencode($dateFilter);
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
                    $firstUrl = '?p=ArretsDouanier.interfaceGestion&page=1';
                    if (!empty($search)) $firstUrl .= '&search=' . urlencode($search);
                    if (!empty($dateFilter)) $firstUrl .= '&date=' . urlencode($dateFilter);
                    echo '<a href="' . $firstUrl . '" class="pagination-item">1</a>';
                    if ($startPage > 2) {
                        echo '<span class="pagination-item disabled">...</span>';
                    }
                }
                
                for ($i = $startPage; $i <= $endPage; $i++) {
                    if ($i == $page) {
                        echo '<span class="pagination-item active">' . $i . '</span>';
                    } else {
                        $pageUrl = '?p=ArretsDouanier.interfaceGestion&page=' . $i;
                        if (!empty($search)) $pageUrl .= '&search=' . urlencode($search);
                        if (!empty($dateFilter)) $pageUrl .= '&date=' . urlencode($dateFilter);
                        echo '<a href="' . $pageUrl . '" class="pagination-item">' . $i . '</a>';
                    }
                }
                
                if ($endPage < $totalPages) {
                    if ($endPage < $totalPages - 1) {
                        echo '<span class="pagination-item disabled">...</span>';
                    }
                    $lastUrl = '?p=ArretsDouanier.interfaceGestion&page=' . $totalPages;
                    if (!empty($search)) $lastUrl .= '&search=' . urlencode($search);
                    if (!empty($dateFilter)) $lastUrl .= '&date=' . urlencode($dateFilter);
                    echo '<a href="' . $lastUrl . '" class="pagination-item">' . $totalPages . '</a>';
                }
                
                if ($page < $totalPages) {
                    $nextUrl = '?p=ArretsDouanier.interfaceGestion&page=' . ($page + 1);
                    if (!empty($search)) $nextUrl .= '&search=' . urlencode($search);
                    if (!empty($dateFilter)) $nextUrl .= '&date=' . urlencode($dateFilter);
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
<div id="modal-MiseJourArretsGestion" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header modern-modal-header">
                <h5 class="modal-title"><i class="fa fa-clipboard"></i> Observations Contrôle Interne</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body modern-modal-body">
                <form action="<?php echo App::url('ajax.arretControle.ajoutArretsControle'); ?>" method="POST" id="form-ajoutArretsControle">
                    <input type="hidden" id="idArretsCaisse"/>
                    
                    <div class="alert alert-info" style="background:var(--info-light); border-left:4px solid var(--info); padding:15px; border-radius:8px; margin-bottom:20px;">
                        <i class="fa fa-info-circle" style="color:var(--info);"></i> 
                        Informations de l'arrêt
                    </div>
                    
                    <div class="form-group">
                        <label class="modern-form-label"><i class="fa fa-calendar"></i> Date Entrée</label>
                        <input type="text" class="modern-form-control" id="dateArret" name="dateArret" value="" placeholder="Date Entrée" readonly>
                    </div>
                    
                    <div class="form-group">
                        <label class="modern-form-label"><i class="fa fa-building"></i> Agence</label>
                        <input type="text" class="modern-form-control" id="agenceArret" name="agenceArret" value="" placeholder="Agence Arret" readonly>
                    </div>
                    
                    <div class="form-group">
                        <label class="modern-form-label"><i class="fa fa-calculator"></i> Difference Caisse</label>
                        <input type="text" class="modern-form-control" id="diffArretCaisse" name="diffArretCaisse" value="" placeholder="Difference Arret Caisse" readonly>
                    </div>
                    
                    <div class="form-group">
                        <label class="modern-form-label"><i class="fa fa-calculator"></i> Difference Douanier</label>
                        <input type="text" class="modern-form-control" id="diffArretDouanier" name="diffArretDouanier" value="" placeholder="Difference Douanier" readonly>
                    </div>
                    
                    <div class="alert alert-warning" style="background:var(--warning-light); border-left:4px solid var(--warning); padding:15px; border-radius:8px; margin-bottom:20px;">
                        <i class="fa fa-exclamation-triangle" style="color:var(--warning);"></i> 
                        Informations de contrôle
                    </div>
                    
                    <div class="form-group">
                        <label class="modern-form-label"><i class="fa fa-check-circle"></i> Contrôle Physique et signature</label>
                        <select class="modern-form-control" id="controlePhys" name="controlePhys">
                            <option value="">Choisir une option</option>
                            <option value="Oui">Oui</option>
                            <option value="Non">Non</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="modern-form-label"><i class="fa fa-comment"></i> Commentaires</label>
                        <textarea class="modern-form-control" id="commentaireControle" name="commentaireControle" placeholder="Entrer les commentaires" rows="3"></textarea>
                    </div>
                    
                    <div class="alert alert-success" style="background:var(--success-light); border-left:4px solid var(--success); padding:15px; border-radius:8px; margin-bottom:20px;">
                        <i class="fa fa-tasks" style="color:var(--success);"></i> 
                        Actions à mener (3 maximum)
                    </div>
                    
                    <div class="actions-grid-form">
                        <div class="form-group">
                            <label class="modern-form-label"><i class="fa fa-tag"></i> Action 1</label>
                            <input type="text" class="modern-form-control" id="Action1" name="Action1" placeholder="Action à mener">
                        </div>
                        <div class="form-group">
                            <label class="modern-form-label"><i class="fa fa-calendar"></i> Délai 1</label>
                            <input type="date" class="modern-form-control" id="delai1" name="delai1">
                        </div>
                        
                        <div class="form-group">
                            <label class="modern-form-label"><i class="fa fa-tag"></i> Action 2</label>
                            <input type="text" class="modern-form-control" id="Action2" name="Action2" placeholder="Action à mener">
                        </div>
                        <div class="form-group">
                            <label class="modern-form-label"><i class="fa fa-calendar"></i> Délai 2</label>
                            <input type="date" class="modern-form-control" id="delai2" name="delai2">
                        </div>
                        
                        <div class="form-group">
                            <label class="modern-form-label"><i class="fa fa-tag"></i> Action 3</label>
                            <input type="text" class="modern-form-control" id="Action3" name="Action3" placeholder="Action à mener">
                        </div>
                        <div class="form-group">
                            <label class="modern-form-label"><i class="fa fa-calendar"></i> Délai 3</label>
                            <input type="date" class="modern-form-control" id="delai3" name="delai3">
                        </div>
                    </div>
                    
                    <div class="text-right" style="margin-top:20px;">
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

<!-- MODAL DÉTAILS CONTRÔLE -->
<div id="modal-detailsArretsControl" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header modern-modal-header">
                <h5 class="modal-title"><i class="fa fa-clipboard"></i> Détails du contrôle</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body modern-modal-body">
                <div id="loaderArretsControl" class="modern-loader" style="display:none;">
                    <i class="fa fa-spinner fa-spin"></i>
                    <p>Chargement des détails...</p>
                </div>
                <div id="chargerArretsControl"></div>
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
<div id="modal-MiseJourArretControle" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header modern-modal-header">
                <h5 class="modal-title"><i class="fa fa-edit"></i> Mise à jour contrôle interne</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body modern-modal-body">
                <div id="loaderArretsControle" class="modern-loader" style="display:none;">
                    <i class="fa fa-spinner fa-spin"></i>
                    <p>Chargement...</p>
                </div>
                <div id="chargerArretsControle"></div>
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
let currentDate = '<?php echo addslashes($dateFilter); ?>';
const itemsPerPage = 10;

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
// RECHERCHE
// ============================================
function effectuerRecherche() {
    const searchInput = document.getElementById('search-arrets');
    const searchTerm = searchInput.value.trim();
    
    let url = '?p=ArretsDouanier.interfaceGestion&page=1';
    if (searchTerm !== '') {
        url += '&search=' + encodeURIComponent(searchTerm);
    }
    if (currentDate !== '') {
        url += '&date=' + encodeURIComponent(currentDate);
    }
    
    window.location.href = url;
}

function effacerRecherche() {
    let url = '?p=ArretsDouanier.interfaceGestion&page=1';
    if (currentDate !== '') {
        url += '&date=' + encodeURIComponent(currentDate);
    }
    window.location.href = url;
}

// ============================================
// GESTION DES MODALES
// ============================================
function ouvrirModalDetails(selector, url, dataId) {
    const loader = $(selector + ' .modern-loader');
    const content = $(selector + ' #chargerArrets, ' + selector + ' #chargerArretsControl, ' + selector + ' #chargerArretsControle');
    
    loader.show();
    content.hide().empty();
    
    $.ajax({
        url: url,
        type: 'POST',
        data: {id: dataId},
        dataType: 'json',
        success: function(response) {
            var htmlContent = extractHtmlFromResponse(response);
            content.html(htmlContent).show();
            loader.hide();
        },
        error: function() {
            notif('Erreur chargement', 'error');
            loader.hide();
            $(selector).modal('hide');
        }
    });
}

// ============================================
// INITIALISATION
// ============================================
$(document).ready(function() {
    console.log('🚀 Page arrêts agences initialisée');
    
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
    $(document).on('click', '.detailsArrets', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        var url = $(this).data('url');
        
        $('#modal-detailsArrets').modal('show');
        ouvrirModalDetails('#modal-detailsArrets', url, id);
    });
    
    // Observations contrôle
    $(document).on('click', '.miseJourControleTest', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        var url = $(this).data('url');
        var loader = $('#loaderArretsControle');
        var content = $('#chargerArretsControle');
        
        loader.show();
        content.hide().empty();
        $('#modal-MiseJourArretControle').modal('show');
        
        $.ajax({
            url: url,
            type: 'POST',
            data: {id: id},
            dataType: 'json',
            success: function(response) {
                var htmlContent = extractHtmlFromResponse(response);
                content.html(htmlContent).show();
                loader.hide();
            },
            error: function() {
                notif('Erreur chargement', 'error');
                loader.hide();
                $('#modal-MiseJourArretControle').modal('hide');
            }
        });
    });
    
    // Détails contrôle
    $(document).on('click', '.detailsArretsControl', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        var url = $(this).data('url');
        
        $('#modal-detailsArretsControl').modal('show');
        ouvrirModalDetails('#modal-detailsArretsControl', url, id);
    });
    
    // Remplissage du formulaire d'observations (ancienne version)
    $(document).on('click', '.miseJourControle', function(e) {
        e.preventDefault();
        
        var id = $(this).data('id');
        var date = $(this).data('date');
        var agence = $(this).data('agence');
        var diff01 = $(this).data('diff01');
        var diff02 = $(this).data('diff02');
        var diff03 = $(this).data('diff03');
        var diff04 = $(this).data('diff04');
        
        $('#idArretsCaisse').val(id);
        $('#dateArret').val(date);
        $('#agenceArret').val(agence);
        $('#diffArretCaisse').val(diff01);
        $('#diffArretDouanier').val(diff02);
        
        // Traitement des données de contrôle
        var controles = diff03.split('_');
        if (controles.length > 1) {
            var controleData = controles[1].split('!');
            if (controleData.length > 1) {
                $('#controlePhys').val(controleData[1]);
                $('#commentaireControle').val(controleData[2]);
            }
        }
        
        // Traitement des actions
        var actionsList = diff04.split('_');
        var actionIndex = 1;
        for (var i = 1; i < actionsList.length; i++) {
            var actionData = actionsList[i].split('!');
            if (actionData.length > 2 && actionIndex <= 3) {
                $('#Action' + actionIndex).val(actionData[2]);
                $('#delai' + actionIndex).val(actionData[1].split('/').reverse().join('-'));
                actionIndex++;
            }
        }
        
        $('#modal-MiseJourArretsGestion').modal('show');
    });
    
    // Soumission du formulaire d'observations
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
                
                if (response.success || response.statuts === 1) {
                    $('#modal-MiseJourArretsGestion').modal('hide');
                    notif('Observations enregistrées avec succès', 'success');
                    
                    setTimeout(function() {
                        location.reload();
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
    
    // Recherche
    $('#btn-search').click(function() {
        effectuerRecherche();
    });
    
    $('#search-arrets').keypress(function(e) {
        if (e.which === 13) {
            effectuerRecherche();
        }
    });
    
    $('#btn-clear-search').click(function() {
        effacerRecherche();
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