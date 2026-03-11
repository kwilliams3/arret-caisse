<?php
use Core\Model\App;
use Core\Model\Session;

$auth = App::getDBAuth();
$session = Session::getInstance();

$user = $_SESSION['user'];

if(!isset($_SESSION['user']) || empty($_SESSION['user'])) {
    App::redirect(App::url('home.index'));
}

// Récupération des paramètres
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Filtrer les utilisateurs par recherche si nécessaire
$usersFiltres = array();
if (!empty($users) && is_array($users)) {
    if (!empty($search)) {
        foreach ($users as $utilisateur) {
            $found = false;
            $searchFields = ['login', 'NomUser', 'agence', 'privilege'];
            
            foreach ($searchFields as $field) {
                if (isset($utilisateur[$field]) && stripos((string)$utilisateur[$field], $search) !== false) {
                    $found = true;
                    break;
                }
            }
            
            if ($found) {
                $usersFiltres[] = $utilisateur;
            }
        }
    } else {
        $usersFiltres = $users;
    }
} else {
    $usersFiltres = array();
}

// PAGINATION MANUELLE
$itemsPerPage = 10;
$totalUsers = count($usersFiltres);
$totalPages = ceil($totalUsers / $itemsPerPage);

if ($page < 1) $page = 1;
if ($totalPages > 0 && $page > $totalPages) $page = $totalPages;

$startIndex = ($page - 1) * $itemsPerPage;
$endIndex = min($startIndex + $itemsPerPage, $totalUsers);
$usersPagines = array_slice($usersFiltres, $startIndex, $itemsPerPage);
?>

<!-- FONT AWESOME 4.5.0 LOCAL -->
<link rel="stylesheet" href="Public/font-awesome/4.5.0/css/font-awesome.min.css">

<!-- jQuery et Bootstrap LOCAL -->
<script src="Public/js/jquery-2.1.4.min.js"></script>
<script src="Public/js/bootstrap.min.js"></script>

<!-- Bootstrap CSS LOCAL -->
<link rel="stylesheet" href="Public/css/bootstrap/css/bootstrap.min.css">

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

/* FontAwesome Fixes pour FA4 */
.fa {
    font-family: FontAwesome !important;
}

.users-container { max-width:100%; margin:0 auto; font-family:'Inter',sans-serif; }
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
.legend-color.purple { background:#8e44ad; }
.legend-color.red { background:var(--danger); }

.actions-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(200px, 1fr)); gap:12px; margin-bottom:20px; }
.modern-action-btn { display:flex; flex-direction:column; align-items:center; padding:15px 10px; background:white; border:1px solid var(--gray-border); border-radius:var(--radius); text-decoration:none; color:var(--dark); transition:all 0.2s ease; box-shadow:var(--shadow); }
.modern-action-btn:hover { transform:translateY(-2px); box-shadow:var(--shadow-hover); border-color:var(--primary); text-decoration:none; color:var(--dark); }
.btn-icon { font-size:18px; margin-bottom:8px; }
.btn-text { font-weight:600; font-size:13px; }
.btn-subtext { font-size:10px; color:var(--gray); margin-top:3px; }
.add-user-btn { background:linear-gradient(135deg, var(--primary), var(--primary-dark)); border:none; color:white; }
.add-user-btn:hover { color:white; }

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

.privilege-badge { 
    display:inline-block; 
    padding:4px 8px; 
    border-radius:12px; 
    font-size:11px; 
    font-weight:600; 
}
.privilege-admin { background:var(--danger-light); color:#c0392b; }
.privilege-agence { background:var(--info-light); color:var(--info); }
.privilege-caissiere { background:var(--success-light); color:var(--success); }
.privilege-controle { background:var(--warning-light); color:var(--warning); }

.action-icons { display:flex; gap:8px; flex-wrap: wrap; justify-content: center; }
.action-link { display:inline-flex; align-items:center; justify-content:center; text-decoration:none; width:32px; height:32px; border-radius:4px; transition:all 0.2s ease; }
.action-link:hover { transform:translateY(-2px); box-shadow:var(--shadow); text-decoration:none; }
.action-link.green i { color:var(--success); }
.action-link.purple i { color:#8e44ad; }
.action-link.red i { color:var(--danger); }

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
.btn-modern-success { background:linear-gradient(135deg,#2ecc71,#27ae60); color:white; }
.btn-modern-success:hover { background:linear-gradient(135deg,#27ae60,#1e8449); color:white; }
.btn-modern-danger { background:linear-gradient(135deg,#e74c3c,#c0392b); color:white; }
.btn-modern-danger:hover { background:linear-gradient(135deg,#c0392b,#a93226); color:white; }

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

<div class="users-container">
    <!-- Fil d'Ariane moderne -->
    <div class="modern-breadcrumbs" style="margin-bottom:15px;">
        <ul class="breadcrumb" style="background:none; padding:0;">
            <li style="display:inline-block;">
                <i class="fa fa-home" style="color:var(--primary);"></i>
                <a href="#" onclick="return naviguerVers('<?php echo App::url("dashboard"); ?>')" style="color:var(--dark);">Accueil</a>
            </li>
            <li style="display:inline-block; margin-left:8px;">
                <i class="fa fa-angle-right" style="color:var(--gray);"></i>
                Administration
            </li>
            <li style="display:inline-block; margin-left:8px;">
                <i class="fa fa-angle-right" style="color:var(--gray);"></i>
                Utilisateurs
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
        <h1 class="banner-title"><i class="fa fa-users"></i> GESTION DES UTILISATEURS</h1>
        <p class="banner-subtitle">Administration des comptes utilisateurs • Gestion des privilèges</p>
        
        <div class="stats-grid">
            <div class="stat-item">
                <i class="fa fa-users" style="color:var(--primary);"></i>
                <div>
                    <div class="stat-value"><?php echo $totalUsers; ?></div>
                    <div class="stat-label">Total utilisateurs</div>
                </div>
            </div>
        </div>
        
        <div class="legend-grid">
            <div class="legend-item"><i class="fa fa-pencil" style="color:var(--success);"></i><span>Modifier</span></div>
            <div class="legend-item"><i class="fa fa-undo" style="color:#8e44ad;"></i><span>Réinitialiser mot de passe</span></div>
            <div class="legend-item"><i class="fa fa-trash" style="color:var(--danger);"></i><span>Supprimer</span></div>
        </div>
    </div>

    <!-- Actions -->
    <div class="actions-section">
        <div class="actions-grid">
            <a href="#" class="modern-action-btn add-user-btn addUserCaisse">
                <i class="fa fa-plus btn-icon"></i>
                <span class="btn-text">Ajouter</span>
                <span class="btn-subtext">Nouvel utilisateur</span>
            </a>
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
                        <input type="text" id="search-users" class="search-input" 
                               placeholder="Rechercher par login, nom, agence, privilège..."
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
                <small class="text-muted">Recherche par login, nom, agence, privilège</small>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="modern-table-container">
        <div class="table-header-modern">
            <div class="table-title">
                <i class="fa fa-list"></i> 
                Liste des utilisateurs
                <?php if (!empty($search)): ?>
                    (<?php echo $totalUsers; ?> résultat<?php echo $totalUsers > 1 ? 's' : ''; ?>)
                <?php else: ?>
                    (<?php echo $totalUsers; ?>)
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
                        <th class="text-left">Login</th>
                        <th class="text-left">Nom</th>
                        <th class="text-left">Agence</th>
                        <th class="text-center">Privilège</th>
                        <th class="text-center" width="120">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($usersPagines)): ?>
                        <?php for($i = 0; $i < count($usersPagines); $i++): 
                            $utilisateur = $usersPagines[$i];
                            
                            $login = isset($utilisateur['login']) ? htmlspecialchars($utilisateur['login']) : '';
                            $nomUser = isset($utilisateur['NomUser']) ? htmlspecialchars($utilisateur['NomUser']) : '';
                            $agence = isset($utilisateur['agence']) ? htmlspecialchars($utilisateur['agence']) : '';
                            $privilege = isset($utilisateur['privilege']) ? htmlspecialchars($utilisateur['privilege']) : '';
                            
                            // Déterminer la classe du badge de privilège
                            $privilegeClass = 'privilege-agence';
                            if (strpos($privilege, 'Admin') !== false || $privilege == 'SuperAdministration') {
                                $privilegeClass = 'privilege-admin';
                            } elseif (strpos($privilege, 'Caisse') !== false) {
                                $privilegeClass = 'privilege-caissiere';
                            } elseif (strpos($privilege, 'Controle') !== false) {
                                $privilegeClass = 'privilege-controle';
                            }
                            
                            $idUser = isset($utilisateur['idUser']) ? $utilisateur['idUser'] : 0;
                        ?>
                        <tr>
                            <td><strong><?php echo $login; ?></strong></td>
                            <td><?php echo $nomUser; ?></td>
                            <td><?php echo $agence; ?></td>
                            <td class="text-center">
                                <span class="privilege-badge <?php echo $privilegeClass; ?>"><?php echo $privilege; ?></span>
                            </td>
                            <td class="text-center">
                                <div class="action-icons">
                                    <!-- Modifier -->
                                    <a class="modifierUserCaisse action-link green" href="#" 
                                       data-url="<?php echo App::url('ajax.user.updateUserCaisse'); ?>" 
                                       data-id="<?php echo $idUser; ?>" 
                                       title="Modifier l'utilisateur">
                                        <i class="fa fa-pencil"></i>
                                    </a>
                                    
                                    <!-- Réinitialiser mot de passe -->
                                    <a href="#" class="ReinitialiserPassCaisse action-link purple" 
                                       title="Réinitialiser le mot de passe" 
                                       data-id="<?php echo $idUser; ?>">
                                        <i class="fa fa-undo"></i>
                                    </a>
                                    
                                    <!-- Supprimer -->
                                    <a class="deleteUser action-link red" href="#" 
                                       data-id="<?php echo $idUser; ?>" 
                                       title="Supprimer l'utilisateur">
                                        <i class="fa fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endfor; ?>
                    <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center">
                            <div class="empty-state">
                                <i class="fa fa-users"></i>
                                <h4>Aucun utilisateur trouvé</h4>
                                <p>
                                    <?php if (!empty($search)): ?>
                                        Aucun résultat pour "<?php echo htmlspecialchars($search); ?>"
                                    <?php else: ?>
                                        Commencez par ajouter un nouvel utilisateur
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
                Affichage de <?php echo ($totalUsers > 0 ? $startIndex + 1 : 0); ?> à <?php echo $endIndex; ?> sur <?php echo $totalUsers; ?> entrées
            </div>
            <div class="pagination-modern">
                <?php
                if ($page > 1) {
                    $prevUrl = '?p=user.index&page=' . ($page - 1);
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
                    $firstUrl = '?p=user.index&page=1';
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
                        $pageUrl = '?p=user.index&page=' . $i;
                        if (!empty($search)) $pageUrl .= '&search=' . urlencode($search);
                        echo '<a href="' . $pageUrl . '" class="pagination-item">' . $i . '</a>';
                    }
                }
                
                if ($endPage < $totalPages) {
                    if ($endPage < $totalPages - 1) {
                        echo '<span class="pagination-item disabled">...</span>';
                    }
                    $lastUrl = '?p=user.index&page=' . $totalPages;
                    if (!empty($search)) $lastUrl .= '&search=' . urlencode($search);
                    echo '<a href="' . $lastUrl . '" class="pagination-item">' . $totalPages . '</a>';
                }
                
                if ($page < $totalPages) {
                    $nextUrl = '?p=user.index&page=' . ($page + 1);
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

<!-- MODAL AJOUT UTILISATEUR -->
<div id="modalAddUserCaisse" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header modern-modal-header">
                <h5 class="modal-title"><i class="fa fa-user-plus"></i> Nouvel utilisateur</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body modern-modal-body">
                <form action="<?php echo App::url('ajax.user.addUserCaisse'); ?>" method="POST" id="form-AddUserCaisse">
                    
                    <div class="alert alert-info" style="background:var(--info-light); border-left:4px solid var(--info); padding:15px; border-radius:8px; margin-bottom:20px;">
                        <i class="fa fa-info-circle" style="color:var(--info);"></i> 
                        Tous les champs sont obligatoires
                    </div>
                    
                    <div class="form-group">
                        <label class="modern-form-label"><i class="fa fa-user"></i> Nom Utilisateur</label>
                        <input type="text" class="modern-form-control" id="nomUser1" name="nomUser" placeholder="Nom complet" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="modern-form-label"><i class="fa fa-id-card"></i> Login</label>
                        <input type="text" class="modern-form-control" id="login1" name="login" placeholder="Identifiant de connexion" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="modern-form-label"><i class="fa fa-lock"></i> Mot de passe</label>
                        <input type="password" class="modern-form-control" id="password1" name="password" placeholder="Mot de passe" required>
                    </div>

                    <div class="form-group">
                        <label class="modern-form-label"><i class="fa fa-repeat"></i> Confirmation mot de passe</label>
                        <input type="password" class="modern-form-control" id="confirmPassword1" name="confirmPassword" placeholder="Répéter le mot de passe" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="modern-form-label"><i class="fa fa-shield"></i> Privilège</label>
                        <select class="modern-form-control" id="privilege1" name="privilege" required>
                            <option value="">Choisir le niveau</option>
                            <option value="Agence">Agence</option>
                            <option value="Caissiere">Caissiere</option>
                            <option value="CaissiereLD">Caissiere LD</option>
                            <option value="CaissiereSage">Caissiere Sage</option>
                            <option value="AgenceSage">Agence Sage</option>
                            <option value="Administration">Administration</option>
                            <option value="SuperAdministration">Super Administration</option>
                            <option value="ControleInterne">Controle Interne</option>
                            <option value="Comptabilite">Comptabilite</option>
                            <option value="Controleur">Controleur</option>
                            <option value="OPAgence">OPAgence</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="modern-form-label"><i class="fa fa-building"></i> Agence</label>
                        <select class="modern-form-control" id="agence1" name="agence" required>
                            <option value="">Choisir l'agence</option>
                            <?php
                                if (!empty($agences)){
                                    foreach ($agences as $agence) {
                                        $id = isset($agence['id']) ? $agence['id'] : '';
                                        $designation = isset($agence['designation']) ? htmlspecialchars($agence['designation']) : '';
                                        echo '<option value="' . $id . '">' . $designation . '</option>';
                                    }
                                }
                            ?>
                        </select>
                    </div>
                    
                    <div class="text-right" style="margin-top:20px;">
                        <button type="button" class="btn-modern btn-modern-secondary" data-dismiss="modal">
                            <i class="fa fa-times"></i> Annuler
                        </button>
                        <button type="submit" class="btn-modern btn-modern-success">
                            <i class="fa fa-save"></i> Créer
                        </button>
                    </div>
                    
                    <div class="modern-loader loaderRegister" style="display:none;">
                        <i class="fa fa-spinner fa-spin"></i>
                        <p>Création en cours...</p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- MODAL MODIFICATION UTILISATEUR -->
<div id="modal-UpdateUserCaisse" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header modern-modal-header">
                <h5 class="modal-title"><i class="fa fa-user-edit"></i> Modifier l'utilisateur</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body modern-modal-body">
                <div id="loaderUserCaisse" class="modern-loader" style="display:none;">
                    <i class="fa fa-spinner fa-spin"></i>
                    <p>Chargement...</p>
                </div>
                <div id="chargerUserCaisse"></div>
            </div>
            <div class="modal-footer modern-modal-footer">
                <button class="btn-modern btn-modern-secondary" data-dismiss="modal">
                    <i class="fa fa-times"></i> Fermer
                </button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL SUPPRESSION UTILISATEUR -->
<div id="modal-DeleteUser" class="modal fade">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header modern-modal-header">
                <h5 class="modal-title"><i class="fa fa-user-times"></i> Suppression d'utilisateur</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body modern-modal-body">
                <form action="<?php echo App::url('ajax.user.deleteUser'); ?>" id="form-DeleteUser">
                    <input type="hidden" class="idUser" id="deleteUserId">
                    
                    <div style="text-align:center; padding:20px;">
                        <i class="fa fa-exclamation-triangle text-warning" style="font-size:48px; margin-bottom:20px;"></i>
                        <h4>Confirmer la suppression</h4>
                        <p style="color:var(--gray);">Voulez-vous vraiment supprimer cet utilisateur ? Cette action est irréversible.</p>
                    </div>
                    
                    <div class="text-center" style="margin-top:20px;">
                        <button type="button" class="btn-modern btn-modern-secondary" data-dismiss="modal">
                            <i class="fa fa-times"></i> Annuler
                        </button>
                        <button type="submit" class="btn-modern btn-modern-danger">
                            <i class="fa fa-trash"></i> Supprimer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- MODAL RÉINITIALISATION MOT DE PASSE -->
<div id="modalResetUserCaisse" class="modal fade">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header modern-modal-header">
                <h5 class="modal-title"><i class="fa fa-key"></i> Réinitialiser le mot de passe</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body modern-modal-body">
                <form action="<?php echo App::url('ajax.user.resetUserCaisse'); ?>" method="POST" id="form-ResetUser">
                    <input type="hidden" class="idUserCaisse" id="resetUserId">
                    
                    <div style="text-align:center; padding:10px; margin-bottom:20px;">
                        <i class="fa fa-lock" style="font-size:48px; color:var(--primary); margin-bottom:15px;"></i>
                        <h4>Réinitialisation du mot de passe</h4>
                        <p style="color:var(--gray);">Saisissez le nouveau mot de passe</p>
                    </div>
                    
                    <div class="form-group">
                        <label class="modern-form-label"><i class="fa fa-lock"></i> Nouveau mot de passe</label>
                        <input type="password" class="modern-form-control" id="newPasswordCaisse1" name="newPassword1" placeholder="Nouveau mot de passe" required>
                    </div>

                    <div class="form-group">
                        <label class="modern-form-label"><i class="fa fa-repeat"></i> Confirmation</label>
                        <input type="password" class="modern-form-control" id="confirm_PasswordCaisse1" name="confirmPassword1" placeholder="Confirmer le mot de passe" required>
                    </div>
                    
                    <div class="text-right" style="margin-top:20px;">
                        <button type="button" class="btn-modern btn-modern-secondary" data-dismiss="modal">
                            <i class="fa fa-times"></i> Annuler
                        </button>
                        <button type="submit" class="btn-modern btn-modern-primary">
                            <i class="fa fa-save"></i> Réinitialiser
                        </button>
                    </div>
                    
                    <div class="modern-loader loaderReset" style="display:none;">
                        <i class="fa fa-spinner fa-spin"></i>
                        <p>Réinitialisation en cours...</p>
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

// ============================================
// RECHERCHE
// ============================================
function effectuerRecherche() {
    const searchInput = document.getElementById('search-users');
    const searchTerm = searchInput ? searchInput.value.trim() : '';
    
    let url = '?p=user.index&page=1';
    if (searchTerm !== '') {
        url += '&search=' + encodeURIComponent(searchTerm);
    }
    
    window.location.href = url;
}

function effacerRecherche() {
    window.location.href = '?p=user.index&page=1';
}

// ============================================
// GESTION DES MODALES
// ============================================
function ouvrirModalModifier(url, id) {
    $('#loaderUserCaisse').show();
    $('#chargerUserCaisse').hide().empty();
    
    $.ajax({
        url: url,
        type: 'POST',
        data: {id: id},
        dataType: 'json',
        success: function(response) {
            $('#loaderUserCaisse').hide();
            if (response.content) {
                $('#chargerUserCaisse').html(response.content).show();
            } else {
                notif('Erreur de chargement', 'error');
            }
        },
        error: function() {
            $('#loaderUserCaisse').hide();
            notif('Erreur de chargement', 'error');
        }
    });
    
    $('#modal-UpdateUserCaisse').modal('show');
}

// ============================================
// VALIDATION DES FORMULAIRES
// ============================================
function validerFormulaireAjout() {
    const nom = $('#nomUser1').val();
    const login = $('#login1').val();
    const password = $('#password1').val();
    const confirmPassword = $('#confirmPassword1').val();
    const privilege = $('#privilege1').val();
    const agence = $('#agence1').val();
    
    if (!nom || !login || !password || !confirmPassword || !privilege || !agence) {
        notif('Tous les champs sont obligatoires', 'warning');
        return false;
    }
    
    if (password !== confirmPassword) {
        notif('Les mots de passe ne correspondent pas', 'warning');
        return false;
    }
    
    if (password.length < 6) {
        notif('Le mot de passe doit contenir au moins 6 caractères', 'warning');
        return false;
    }
    
    return true;
}

function validerFormulaireReset() {
    const password = $('#newPasswordCaisse1').val();
    const confirmPassword = $('#confirm_PasswordCaisse1').val();
    
    if (!password || !confirmPassword) {
        notif('Tous les champs sont obligatoires', 'warning');
        return false;
    }
    
    if (password !== confirmPassword) {
        notif('Les mots de passe ne correspondent pas', 'warning');
        return false;
    }
    
    if (password.length < 6) {
        notif('Le mot de passe doit contenir au moins 6 caractères', 'warning');
        return false;
    }
    
    return true;
}

// ============================================
// INITIALISATION
// ============================================
$(document).ready(function() {
    console.log('🚀 Page gestion des utilisateurs initialisée');
    
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
    $('.addUserCaisse').click(function(e) {
        e.preventDefault();
        $('#form-AddUserCaisse')[0].reset();
        $('#modalAddUserCaisse').modal('show');
    });
    
    // Formulaire ajout
    $('#form-AddUserCaisse').submit(function(e) {
        e.preventDefault();
        
        if (!validerFormulaireAjout()) return;
        
        var formData = $(this).serialize();
        var loader = $('.loaderRegister');
        
        loader.show();
        
        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                loader.hide();
                
                if (response.success) {
                    $('#modalAddUserCaisse').modal('hide');
                    notif('Utilisateur créé avec succès', 'success');
                    
                    setTimeout(function() {
                        let url = '?p=user.index&page=1';
                        if (currentSearch !== '') {
                            url += '&search=' + encodeURIComponent(currentSearch);
                        }
                        window.location.href = url;
                    }, 1500);
                    
                } else {
                    notif(response.message || 'Erreur lors de la création', 'error');
                }
            },
            error: function() {
                loader.hide();
                notif('Erreur de communication avec le serveur', 'error');
            }
        });
    });
    
    // Modification
    $('.modifierUserCaisse').click(function(e) {
        e.preventDefault();
        var url = $(this).data('url');
        var id = $(this).data('id');
        ouvrirModalModifier(url, id);
    });
    
    // Suppression
    $('.deleteUser').click(function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        $('#deleteUserId').val(id);
        $('#modal-DeleteUser').modal('show');
    });
    
    $('#form-DeleteUser').submit(function(e) {
        e.preventDefault();
        
        var formData = $(this).serialize();
        
        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#modal-DeleteUser').modal('hide');
                    notif('Utilisateur supprimé avec succès', 'success');
                    
                    setTimeout(function() {
                        let url = '?p=user.index&page=1';
                        if (currentSearch !== '') {
                            url += '&search=' + encodeURIComponent(currentSearch);
                        }
                        window.location.href = url;
                    }, 1500);
                    
                } else {
                    notif(response.message || 'Erreur lors de la suppression', 'error');
                }
            },
            error: function() {
                notif('Erreur de communication avec le serveur', 'error');
            }
        });
    });
    
    // Réinitialisation mot de passe
    $('.ReinitialiserPassCaisse').click(function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        $('#resetUserId').val(id);
        $('#form-ResetUser')[0].reset();
        $('#modalResetUserCaisse').modal('show');
    });
    
    $('#form-ResetUser').submit(function(e) {
        e.preventDefault();
        
        if (!validerFormulaireReset()) return;
        
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
                    $('#modalResetUserCaisse').modal('hide');
                    notif('Mot de passe réinitialisé avec succès', 'success');
                } else {
                    notif(response.message || 'Erreur lors de la réinitialisation', 'error');
                }
            },
            error: function() {
                loader.hide();
                notif('Erreur de communication avec le serveur', 'error');
            }
        });
    });
    
    // RECHERCHE - CORRIGÉ
    $('#btn-search').click(function(e) {
        e.preventDefault();
        effectuerRecherche();
    });
    
    $('#search-users').keypress(function(e) {
        if (e.which === 13) {
            effectuerRecherche();
        }
    });
    
    $('#btn-clear-search').click(function(e) {
        e.preventDefault();
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