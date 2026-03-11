<?php
use Core\Model\App;
use Core\Model\Session;
use Core\Database\Agence;

$auth = App::getDBAuth();
$session = Session::getInstance();

$user = $_SESSION['user'];

// Récupération des paramètres
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Filtrer les arrêts douaniers par recherche si nécessaire
$arretsDouaniersFiltres = array();
if (!empty($arretsDouaniers) && is_array($arretsDouaniers)) {
    if (!empty($search)) {
        foreach ($arretsDouaniers as $arret) {
            $found = false;
            $searchFields = ['agence', 'arretInfo', 'arretDouanier'];
            
            foreach ($searchFields as $field) {
                if (isset($arret[$field]) && stripos((string)$arret[$field], $search) !== false) {
                    $found = true;
                    break;
                }
            }
            
            // Recherche dans les montants
            $montantFields = ['totalCaisse', 'diffDouanier'];
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

// Récupérer l'agence de l'utilisateur si c'est une agence
$userAgence = '';
if (in_array($user['privilege'], ['Agence'])) {
    try {
        $stmtAgence = Agence::searchById($user['agence']);
        if ($stmtAgence) {
            while ($result2 = sqlsrv_fetch_array($stmtAgence, SQLSRV_FETCH_ASSOC)) {
                $userAgence = isset($result2['designation']) ? $result2['designation'] : '';
                break;
            }
        }
    } catch (Exception $e) {
        error_log("Erreur récupération agence: " . $e->getMessage());
    }
}

// Rôles autorisés à ajouter
$privilegesAjout = ['Administration', 'Agence', 'SuperAdministration'];
$allowedToAdd = in_array($user['privilege'], $privilegesAjout);
?>

<!-- FONT AWESOME LOCAL -->
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
.legend-color.purple { background:#8e44ad; }
.legend-color.green { background:var(--success); }

.actions-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(200px, 1fr)); gap:12px; margin-bottom:20px; }
.modern-action-btn { display:flex; flex-direction:column; align-items:center; padding:15px 10px; background:white; border:1px solid var(--gray-border); border-radius:var(--radius); text-decoration:none; color:var(--dark); transition:all 0.2s ease; box-shadow:var(--shadow); }
.modern-action-btn:hover { transform:translateY(-2px); box-shadow:var(--shadow-hover); border-color:var(--primary); text-decoration:none; color:var(--dark); }
.btn-icon { font-size:18px; margin-bottom:8px; }
.btn-text { font-weight:600; font-size:13px; }
.btn-subtext { font-size:10px; color:var(--gray); margin-top:3px; }
.add-arret-btn { background:linear-gradient(135deg, var(--primary), var(--primary-dark)); border:none; color:white; }
.add-arret-btn:hover { color:white; }

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

#dynamic-table { width:100% !important; font-size:13px; }
#dynamic-table thead th { background:var(--gray-light); color:var(--dark); font-weight:600; font-size:11px; padding:12px 8px; }
#dynamic-table tbody td { padding:12px 8px; border-bottom:1px solid var(--gray-border); vertical-align:middle; }
#dynamic-table tbody tr:hover { background:var(--gray-light); }

.montant-cell { font-weight:600; }
.montant-positif { color:var(--success); }
.montant-negatif { color:var(--danger); }
.total-cell { font-weight:700; color:var(--primary); background:var(--primary-light); border-radius:12px; padding:4px 8px; display:inline-block; }

.action-icons { display:flex; gap:8px; flex-wrap: wrap; justify-content: center; }
.action-link { display:inline-flex; align-items:center; justify-content:center; text-decoration:none; width:32px; height:32px; border-radius:4px; transition:all 0.2s ease; }
.action-link:hover { transform:translateY(-2px); box-shadow:var(--shadow); text-decoration:none; }
.action-link.purple i { color:#8e44ad; }
.action-link.green i { color:var(--success); }
.action-link.blue i { color:var(--info); }
.action-link.download i { color:var(--warning); }

.modal-header.modern-modal-header { background:linear-gradient(135deg,#0d1b3e,#1a2b5c); padding:20px 25px; border-radius:12px 12px 0 0; }
.modern-modal-header .modal-title { color:white; font-weight:600; font-size:16px; display:flex; align-items:center; gap:12px; }
.modern-modal-header .close { color:white; opacity:0.8; width:32px; height:32px; background:rgba(255,255,255,0.1); border-radius:50%; display:flex; align-items:center; justify-content:center; border:none; }
.modern-modal-header .close:hover { opacity:1; background:rgba(255,255,255,0.2); }
.modal-body.modern-modal-body { padding:25px; background:#f8fafc; max-height:70vh; overflow-y:auto; }
.modal-footer.modern-modal-footer { padding:20px 25px; background:white; border-top:1px solid #e9ecef; border-radius:0 0 12px 12px; display:flex; gap:10px; justify-content:flex-end; }

.modern-form-control { width:100%; padding:12px 15px; border:1px solid #dee2e6; border-radius:8px; font-size:14px; transition:all 0.3s ease; margin-bottom:15px; background:white; }
.modern-form-control:focus { border-color:#4361ee; box-shadow:0 0 0 3px rgba(67,97,238,0.1); outline:none; }
.modern-form-control[readonly] { background:var(--gray-light); }
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

/* Pagination */
.pagination-modern { display:flex; justify-content:flex-end; gap:5px; margin-top:20px; }
.pagination-item { display:inline-flex; align-items:center; justify-content:center; min-width:36px; height:36px; padding:0 6px; border-radius:8px; background:white; border:1px solid #dee2e6; color:var(--dark); text-decoration:none; transition:all 0.2s ease; }
.pagination-item:hover { background:var(--primary-light); border-color:var(--primary); color:var(--primary); text-decoration:none; }
.pagination-item.active { background:var(--primary); border-color:var(--primary); color:white; }
.pagination-item.disabled { opacity:0.5; pointer-events:none; }
.pagination-info { padding:8px 0; color:var(--gray); }

/* Styles pour les différences */
.diff-positive { color: var(--success); font-weight:600; }
.diff-negative { color: var(--danger); font-weight:600; }

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

<div class="arrets-container">
    <!-- Fil d'Ariane moderne -->
    <div class="modern-breadcrumbs" style="margin-bottom:15px;">
        <ul class="breadcrumb" style="background:none; padding:0;">
            <li style="display:inline-block;">
                <i class="fa fa-home" style="color:var(--primary);"></i>
                <a href="#" onclick="return naviguerVers('<?php echo App::url("dashboard"); ?>')" style="color:var(--dark);">Arrêts Chef</a>
            </li>
            <li style="display:inline-block; margin-left:8px;">
                <i class="fa fa-angle-right" style="color:var(--gray);"></i>
                Liste des arrêts Chef
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
        <h1 class="banner-title"><i class="fa fa-user"></i> ARRÊTS CHEF</h1>
        <p class="banner-subtitle">
            Gestion des arrêts Chef • Suivi des différences
            <?php if (!empty($userAgence)): ?>
                - <strong><?php echo htmlspecialchars($userAgence); ?></strong>
            <?php endif; ?>
        </p>
        
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
            <div class="legend-item"><div class="legend-color purple"></div><span>Détails arrêt</span></div>
            <div class="legend-item"><div class="legend-color green"></div><span>Détails versement</span></div>
            <div class="legend-item"><i class="fa fa-download" style="color:var(--warning);"></i><span>Télécharger bordereau</span></div>
        </div>
    </div>

    <!-- Actions -->
    <div class="actions-section">
        <div class="actions-grid">
            <?php if($allowedToAdd) : ?>
            <a href="#" class="modern-action-btn add-arret-btn AddArretDouanier">
                <i class="fa fa-plus btn-icon"></i>
                <span class="btn-text">Ajouter</span>
                <span class="btn-subtext">Nouvel arrêt</span>
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
    </div>

    <!-- Table -->
    <div class="modern-table-container">
        <div class="table-header-modern">
            <div class="table-title">
                <i class="fa fa-list"></i> 
                Liste des arrêts Chef
                <?php if (!empty($search)): ?>
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
                        <th class="text-right">Arrêt Info</th>
                        <th class="text-right">Arrêt Douanier</th>
                        <th class="text-right">Total Caisse</th>
                        <th class="text-right">Diff Douanier</th>
                        <th class="text-left">Agence</th>
                        <th class="text-center" width="140">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($arretsPagines)): ?>
                        <?php for($i = 0; $i < count($arretsPagines); $i++): 
                            $arret = $arretsPagines[$i];
                            
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
                            
                            $arretInfo = isset($arret['arretInfo']) ? number_format($arret['arretInfo'], 0, ',', ' ') : '0';
                            $arretDouanier = isset($arret['arretDouanier']) ? number_format($arret['arretDouanier'], 0, ',', ' ') : '0';
                            $totalCaisse = isset($arret['totalCaisse']) ? number_format($arret['totalCaisse'], 0, ',', ' ') : '0';
                            $diffDouanier = isset($arret['diffDouanier']) ? $arret['diffDouanier'] : 0;
                            $diffDouanierFormatted = number_format($diffDouanier, 0, ',', ' ');
                            $agence = isset($arret['agence']) ? htmlspecialchars($arret['agence']) : '';
                            $idArret = isset($arret['idArretsDouanier']) ? $arret['idArretsDouanier'] : 0;
                            
                            // Gestion du bordereau
                            $bordereau1 = explode("!", $arret['bordereauVersement']);
                            $bordereauUrl = "http://192.168.0.13:8088/ArretsCaisses/DocumentsBordereau/" . $bordereau1[0];
                            $hasBordereau = ($bordereau1[0] !== 'Aucun');
                            
                            // Classe pour la différence
                            $diffClass = $diffDouanier >= 0 ? 'diff-positive' : 'diff-negative';
                        ?>
                        <tr>
                            <td class="text-center">
                                <div style="font-size:12px; font-weight:600;"><?php echo date('d/m/Y', $dateTimestamp); ?></div>
                                <div style="font-size:11px; color:var(--gray);"><?php echo date('H:i', $dateTimestamp); ?></div>
                            </td>
                            <td class="text-right montant-cell"><?php echo $arretInfo; ?></td>
                            <td class="text-right montant-cell"><?php echo $arretDouanier; ?></td>
                            <td class="text-right"><span class="total-cell"><?php echo $totalCaisse; ?></span></td>
                            <td class="text-right <?php echo $diffClass; ?>"><?php echo $diffDouanierFormatted; ?></td>
                            <td><?php echo $agence; ?></td>
                            <td class="text-center">
                                <div class="action-icons">
                                    <a href="#" class="detailsArrets action-link purple" 
                                       data-url="<?php echo App::url('ajax.arretsDouanier.detailsArretsDouanier'); ?>" 
                                       data-id="<?php echo $idArret; ?>" 
                                       title="Détails de l'arrêt">
                                        <i class="fa fa-info-circle"></i>
                                    </a>
                                    
                                    <a href="#" class="detailsArretsVerse action-link green" 
                                       data-url="<?php echo App::url('ajax.arretsDouanier.detailsArretsDouanierVerse'); ?>" 
                                       data-id="<?php echo $idArret; ?>" 
                                       title="Détails du versement">
                                        <i class="fa fa-money"></i>
                                    </a>
                                    
                                    <?php if ($hasBordereau): ?>
                                        <a href="<?php echo $bordereauUrl; ?>" download="<?php echo $bordereau1[0]; ?>" target="_blank" class="action-link download" title="Télécharger le bordereau">
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
                                <i class="fa fa-user"></i>
                                <h4>Aucun arrêt Chef trouvé</h4>
                                <p>
                                    <?php if (!empty($search)): ?>
                                        Aucun résultat pour "<?php echo htmlspecialchars($search); ?>"
                                    <?php else: ?>
                                        Commencez par ajouter un nouvel arrêt
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
                    $prevUrl = '?p=arretsDouanier&page=' . ($page - 1);
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
                    $firstUrl = '?p=arretsDouanier&page=1';
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
                        $pageUrl = '?p=arretsDouanier&page=' . $i;
                        if (!empty($search)) $pageUrl .= '&search=' . urlencode($search);
                        echo '<a href="' . $pageUrl . '" class="pagination-item">' . $i . '</a>';
                    }
                }
                
                if ($endPage < $totalPages) {
                    if ($endPage < $totalPages - 1) {
                        echo '<span class="pagination-item disabled">...</span>';
                    }
                    $lastUrl = '?p=arretsDouanier&page=' . $totalPages;
                    if (!empty($search)) $lastUrl .= '&search=' . urlencode($search);
                    echo '<a href="' . $lastUrl . '" class="pagination-item">' . $totalPages . '</a>';
                }
                
                if ($page < $totalPages) {
                    $nextUrl = '?p=arretsDouanier&page=' . ($page + 1);
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

<!-- MODAL AJOUT ARRÊT CHEF -->
<div id="modalAjoutArretDouanier" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header modern-modal-header">
                <h5 class="modal-title"><i class="fa fa-plus-circle"></i> Ajouter un Arrêt Chef</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body modern-modal-body">
                <form action="<?php echo App::url('ajax.arretsDouanier.ajoutArretsDouanier'); ?>" method="POST" id="form-ajoutArretsDouanier" enctype="multipart/form-data">
                    
                    <div class="alert alert-info" style="background:var(--info-light); border-left:4px solid var(--info); padding:15px; border-radius:8px; margin-bottom:20px;">
                        <i class="fa fa-info-circle" style="color:var(--info);"></i> 
                        Les champs avec <span class="required">*</span> sont obligatoires
                    </div>
                    
                    <div class="form-group">
                        <label class="modern-form-label"><i class="fa fa-calculator"></i> Total Caisse</label>
                        <input type="number" class="modern-form-control" id="totalCaisseD" name="totalCaisseD1" value='<?php echo isset($arretsOld['totalCaisse']) ? $arretsOld['totalCaisse'] : 0; ?>' placeholder="Total Caisse" readonly>
                    </div>
                    
                    <div class="form-group">
                        <label class="modern-form-label"><i class="fa fa-money"></i> Arrêt Douanier <span class="required">*</span></label>
                        <input type="number" class="modern-form-control" id="arretDouanier" name="arretDouanier" placeholder="Montant arrêt douanier" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="modern-form-label"><i class="fa fa-laptop"></i> Arrêt Info <span class="required">*</span></label>
                        <input type="number" class="modern-form-control" id="arretInfo" name="arretInfo" placeholder="Montant arrêt informatique" required>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="modern-form-label"><i class="fa fa-calculator"></i> Différence Caisse</label>
                                <input type="number" class="modern-form-control" id="diffCaisse" name="diffCaisse" placeholder="Différence caisse" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="modern-form-label"><i class="fa fa-calculator"></i> Différence Douanier</label>
                                <input type="number" class="modern-form-control" id="diffDouanier" name="diffDouanier" placeholder="Différence douanier" readonly>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="modern-form-label"><i class="fa fa-comment"></i> Observation Caisse <span class="required">*</span></label>
                        <textarea class="modern-form-control" id="obsArretChef" name="obsArretChef" placeholder="Entrer les observations" rows="3" required></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label class="modern-form-label"><i class="fa fa-money"></i> Versement Banque / Ramassage <span class="required">*</span></label>
                        <select class="modern-form-control" id="versement" name="versement" required>
                            <option value="">Choisir une option</option>
                            <option value="Oui">Oui</option>
                            <option value="Non">Non</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="modern-form-label"><i class="fa fa-comment"></i> Observation Versement <span class="required">*</span></label>
                        <textarea class="modern-form-control" id="observationVers" name="observationVers" placeholder="Entrer les observations versement" rows="3" required></textarea>
                    </div>
                    
                    <div class="form-group" id="montantVGroup" style="display:none;">
                        <label class="modern-form-label"><i class="fa fa-money"></i> Montant versement</label>
                        <input type="number" class="modern-form-control" id="montantVerse" name="montantVerse" placeholder="Montant versé">
                    </div>
                    
                    <div class="form-group" id="bordereauGroup" style="display:none;">
                        <label class="modern-form-label"><i class="fa fa-file-pdf-o"></i> Bordereau Versement / Ramassage</label>
                        <input type="file" class="modern-form-control" id="bordereauVers" name="bordereauVers" accept=".pdf,.jpg,.jpeg,.png">
                        <small class="text-muted">Formats acceptés: PDF, JPG, PNG (max 5MB)</small>
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

<script>
// ============================================
// VARIABLES GLOBALES
// ============================================
let currentPage = <?php echo $page; ?>;
let currentSearch = '<?php echo addslashes($search); ?>';
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

// ============================================
// CALCUL DES DIFFÉRENCES
// ============================================
function calculerDifferences() {
    let totalCaisse = parseFloat($('#totalCaisseD').val()) || 0;
    let arretDouanier = parseFloat($('#arretDouanier').val()) || 0;
    let arretInfo = parseFloat($('#arretInfo').val()) || 0;
    
    let diffCaisse = arretInfo - totalCaisse;
    let diffDouanier = arretDouanier - totalCaisse;
    
    $('#diffCaisse').val(diffCaisse);
    $('#diffDouanier').val(diffDouanier);
}

// ============================================
// GESTION DU VERSEMENT
// ============================================
function toggleVersementFields() {
    let versement = $('#versement').val();
    
    if (versement === 'Oui') {
        $('#montantVGroup').show();
        $('#bordereauGroup').show();
        $('#montantVerse').prop('required', true);
        $('#bordereauVers').prop('required', true);
    } else {
        $('#montantVGroup').hide();
        $('#bordereauGroup').hide();
        $('#montantVerse').prop('required', false);
        $('#bordereauVers').prop('required', false);
        $('#montantVerse').val('');
        $('#bordereauVers').val('');
    }
}

// ============================================
// RECHERCHE
// ============================================
function effectuerRecherche() {
    const searchInput = document.getElementById('search-arrets');
    const searchTerm = searchInput.value.trim();
    
    let url = '?p=arretsDouanier&page=1';
    if (searchTerm !== '') {
        url += '&search=' + encodeURIComponent(searchTerm);
    }
    
    window.location.href = url;
}

function effacerRecherche() {
    window.location.href = '?p=arretsDouanier&page=1';
}

// ============================================
// AJOUT D'ARRÊT CHEF
// ============================================
function ouvrirModalAjout() {
    console.log('📋 Ouverture modal ajout');
    
    const form = document.getElementById('form-ajoutArretsDouanier');
    if (form) {
        form.reset();
    }
    
    $('#montantVGroup').hide();
    $('#bordereauGroup').hide();
    
    if (typeof jQuery !== 'undefined' && jQuery.fn.modal) {
        jQuery('#modalAjoutArretDouanier').modal('show');
    }
}

function validerFormulaire() {
    let valide = true;
    
    const inputs = [
        { id: 'arretDouanier', nom: 'Arrêt Douanier' },
        { id: 'arretInfo', nom: 'Arrêt Info' },
        { id: 'obsArretChef', nom: 'Observation Caisse' },
        { id: 'observationVers', nom: 'Observation Versement' }
    ];
    
    for (let i = 0; i < inputs.length; i++) {
        const input = $('#' + inputs[i].id);
        if (!input.val() || input.val() === '') {
            input.addClass('is-invalid');
            notif('Le champ "' + inputs[i].nom + '" est obligatoire', 'warning');
            valide = false;
            break;
        }
    }
    
    if ($('#versement').val() === '') {
        $('#versement').addClass('is-invalid');
        notif('Veuillez choisir une option de versement', 'warning');
        valide = false;
    }
    
    if ($('#versement').val() === 'Oui') {
        if (!$('#montantVerse').val()) {
            $('#montantVerse').addClass('is-invalid');
            notif('Le montant versé est obligatoire', 'warning');
            valide = false;
        }
        
        if (!$('#bordereauVers').val()) {
            $('#bordereauVers').addClass('is-invalid');
            notif('Le bordereau est obligatoire', 'warning');
            valide = false;
        }
    }
    
    return valide;
}

// ============================================
// DÉTAILS
// ============================================
function ouvrirModalDetails(url, id, loaderId, contentId, modalId) {
    console.log('📋 Ouverture détails');
    
    $('#' + loaderId).show();
    $('#' + contentId).hide().empty();
    
    $.ajax({
        url: url,
        type: 'POST',
        data: {id: id},
        dataType: 'json',
        success: function(response) {
            $('#' + loaderId).hide();
            
            if (response.content) {
                $('#' + contentId).html(response.content).show();
            } else if (response.html) {
                $('#' + contentId).html(response.html).show();
            } else if (typeof response === 'string') {
                $('#' + contentId).html(response).show();
            } else {
                $('#' + contentId).html('<div class="alert alert-danger">Format de réponse invalide</div>').show();
            }
            
            if (typeof jQuery !== 'undefined' && jQuery.fn.modal) {
                jQuery('#' + modalId).modal('show');
            }
        },
        error: function() {
            $('#' + loaderId).hide();
            notif('Erreur lors du chargement', 'error');
        }
    });
}

// ============================================
// INITIALISATION
// ============================================
$(document).ready(function() {
    console.log('🚀 Page arrêts Chef initialisée');
    
    // Tooltips
    $('[title]').tooltip({placement:'top', trigger:'hover'});
    
    // Gestion des modales
    $('.modal').on('show.bs.modal', function() { 
        $(this).css('z-index','1050');
        if(!$(this).find('.modal-dialog').hasClass('modal-dialog-centered')) {
            $(this).find('.modal-dialog').css('margin-top','80px');
        }
    });
    
    // Bouton Ajouter
    $('.AddArretDouanier').click(function(e) {
        e.preventDefault();
        ouvrirModalAjout();
    });
    
    // Calcul automatique des différences
    $('#arretDouanier, #arretInfo').on('input', function() {
        calculerDifferences();
    });
    
    // Gestion du versement
    $('#versement').change(function() {
        toggleVersementFields();
    });
    
    // Soumission du formulaire
    $('#form-ajoutArretsDouanier').submit(function(e) {
        e.preventDefault();
        
        if (!validerFormulaire()) return;
        
        var formData = new FormData(this);
        var loader = $('.loaderReset');
        
        loader.show();
        
        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                loader.hide();
                
                if (response.success) {
                    $('#modalAjoutArretDouanier').modal('hide');
                    notif('Arrêt Chef ajouté avec succès', 'success');
                    
                    setTimeout(function() {
                        let url = '?p=arretsDouanier&page=1';
                        if (currentSearch !== '') {
                            url += '&search=' + encodeURIComponent(currentSearch);
                        }
                        window.location.href = url;
                    }, 1500);
                    
                } else {
                    notif(response.message || 'Erreur lors de l\'ajout', 'error');
                }
            },
            error: function() {
                loader.hide();
                notif('Erreur de communication avec le serveur', 'error');
            }
        });
    });
    
    // Détails arrêt
    $(document).on('click', '.detailsArrets', function(e) {
        e.preventDefault();
        var url = $(this).data('url');
        var id = $(this).data('id');
        ouvrirModalDetails(url, id, 'loaderArrets', 'chargerArrets', 'modal-detailsArrets');
    });
    
    // Détails versement
    $(document).on('click', '.detailsArretsVerse', function(e) {
        e.preventDefault();
        var url = $(this).data('url');
        var id = $(this).data('id');
        ouvrirModalDetails(url, id, 'loaderArretsVerse', 'chargerArretsVerse', 'modal-detailsArretsVerse');
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