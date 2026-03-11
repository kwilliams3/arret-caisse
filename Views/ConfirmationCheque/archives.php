<?php
use Core\Model\App;
use Core\Database\Agence;
use Core\Database\Operation;

$user = $_SESSION['user'];

// CORRECTION RADICALE: Récupérer page depuis $_GET directement au lieu d'utiliser la variable du contrôleur
$page = 1;
if (isset($_GET['page']) && is_numeric($_GET['page'])) {
    $page = (int)$_GET['page'];
    if ($page < 1) $page = 1;
}

// Sécuriser les autres variables (qui viennent du contrôleur)
$totalPages = isset($totalPages) ? (int)$totalPages : 1;
$totalArchives = isset($totalArchives) ? (int)$totalArchives : 0;
$itemsPerPage = isset($itemsPerPage) ? (int)$itemsPerPage : 10;
$search = isset($search) ? $search : '';
$type = isset($type) ? $type : '';
$isAdminOrCompta = isset($isAdminOrCompta) ? $isAdminOrCompta : false;
$userAgenceId = isset($userAgenceId) ? $userAgenceId : null;
$userAgenceNom = isset($userAgenceNom) ? $userAgenceNom : '';
$archives = isset($archives) && is_array($archives) ? $archives : array();

// Recalculer startIndex et endIndex
$startIndex = ($page - 1) * $itemsPerPage;
$endIndex = min($startIndex + $itemsPerPage, $totalArchives);

// Récupérer toutes les archives pour les statistiques (une seule fois)
$allArchivesForStats = array();
try {
    if ($isAdminOrCompta) {
        $allArchivesForStats = Operation::getArchives(null, $type, 10000, 0);
    } else {
        $allArchivesForStats = Operation::getArchives($userAgenceId, $type, 10000, 0);
    }
} catch (Exception $e) {
    error_log("Erreur récupération archives pour statistiques: " . $e->getMessage());
}

// Compter les statistiques sur l'ensemble des données
$statsTermines = 0;
$statsAnnules = 0;
if (!empty($allArchivesForStats) && is_array($allArchivesForStats)) {
    foreach($allArchivesForStats as $op) {
        if (is_array($op) && isset($op['statut'])) {
            $statut = strtolower($op['statut']);
            if ($statut === 'confirmé') {
                $statsTermines++;
            } elseif ($statut === 'annulé') {
                $statsAnnules++;
            }
        }
    }
}
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

.operations-container { max-width:100%; margin:0 auto; font-family:'Inter',sans-serif; }
.modern-header-banner { background:white; border-radius:var(--radius); padding:20px 25px; margin-bottom:25px; color:var(--dark); box-shadow:var(--shadow); border:1px solid var(--gray-border); }
.banner-title { font-size:18px; font-weight:700; margin-bottom:10px; display:flex; align-items:center; gap:10px; color:var(--primary); }
.banner-subtitle { font-size:13px; color:var(--gray); margin-bottom:15px; }

/* Bouton retour */
.btn-retour {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 20px;
    background: var(--gray-light);
    border: 1px solid var(--gray-border);
    border-radius: var(--radius);
    color: var(--dark);
    text-decoration: none;
    transition: all 0.2s ease;
    margin-bottom: 20px;
}
.btn-retour:hover {
    background: var(--primary-light);
    border-color: var(--primary);
    color: var(--primary);
    text-decoration: none;
}

.stats-grid { display:flex; flex-wrap:wrap; gap:15px; margin-top:15px; }
.stat-item { background:var(--gray-light); padding:10px 15px; border-radius:var(--radius); display:flex; align-items:center; gap:10px; }
.stat-value { font-size:20px; font-weight:700; color:var(--primary); }
.stat-label { font-size:12px; color:var(--gray); }

.modern-table-container { background:white; border-radius:var(--radius); padding:20px; box-shadow:var(--shadow); margin-bottom:30px; border:1px solid var(--gray-border); }
.table-header-modern { display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; padding-bottom:15px; border-bottom:1px solid var(--gray-border); flex-wrap:wrap; gap:15px; }
.table-title { font-size:16px; font-weight:600; color:var(--dark); display:flex; align-items:center; gap:10px; }

/* Barre de recherche */
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

#archives-table { width:100% !important; font-size:13px; }
#archives-table thead th { background:var(--gray-light); color:var(--dark); font-weight:600; font-size:11px; padding:12px 8px; }
#archives-table tbody td { padding:12px 8px; border-bottom:1px solid var(--gray-border); vertical-align:middle; }
#archives-table tbody tr:hover { background:var(--gray-light); }

.status-badge { display:inline-flex; align-items:center; gap:5px; padding:4px 10px; border-radius:12px; font-size:11px; font-weight:600; }
.status-termine { background:var(--success-light); color:#13863c; }
.status-annule { background:var(--danger-light); color:#c0392b; }

.type-cheque { color: var(--success); font-weight:600; font-size:16px; }
.type-virement { color: var(--info); font-weight:600; font-size:16px; }

.action-icons { display:flex; gap:8px; flex-wrap: wrap; justify-content: center; }
.action-link { display:inline-flex; align-items:center; justify-content:center; text-decoration:none; width:32px; height:32px; border-radius:4px; transition:all 0.2s ease; }
.action-link:hover { transform:translateY(-2px); box-shadow:var(--shadow); text-decoration:none; }
.action-link.purple i { color:#8e44ad; }

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

/* FontAwesome Fixes */
.fa {
    font-family: FontAwesome !important;
}

@media (max-width:768px) { 
    .table-header-modern { flex-direction:column; align-items:flex-start; } 
    .search-wrapper { width:100%; }
    .pagination-modern { justify-content:center; }
}
</style>

<div class="operations-container">
    <!-- Bouton retour -->
    <a href="?p=confirmationCheque" class="btn-retour">
        <i class="fa fa-arrow-left"></i> Retour à la gestion des opérations
    </a>

    <!-- Header avec statistiques -->
    <div class="modern-header-banner">
        <h1 class="banner-title"><i class="fa fa-archive"></i> ARCHIVES DES OPÉRATIONS</h1>
        <p class="banner-subtitle">
            Consultation des opérations terminées et annulées
            <?php if (!$isAdminOrCompta && !empty($userAgenceNom)): ?>
                - <strong><?php echo htmlspecialchars($userAgenceNom); ?></strong>
            <?php elseif ($isAdminOrCompta): ?>
                - <strong>Toutes les agences</strong>
            <?php endif; ?>
        </p>
        
        <div class="stats-grid">
            <div class="stat-item">
                <i class="fa fa-archive" style="color:var(--primary);"></i>
                <div>
                    <div class="stat-value"><?php echo $totalArchives; ?></div>
                    <div class="stat-label">Opérations archivées</div>
                </div>
            </div>
            <div class="stat-item">
                <i class="fa fa-check-circle" style="color:var(--success);"></i>
                <div>
                    <div class="stat-value"><?php echo $statsTermines; ?></div>
                    <div class="stat-label">Terminées</div>
                </div>
            </div>
            <div class="stat-item">
                <i class="fa fa-times-circle" style="color:var(--danger);"></i>
                <div>
                    <div class="stat-value"><?php echo $statsAnnules; ?></div>
                    <div class="stat-label">Annulées</div>
                </div>
            </div>
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
                        <input type="text" id="search-archives" class="search-input" 
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

    <!-- Table des archives -->
    <div class="modern-table-container">
        <div class="table-header-modern">
            <div class="table-title">
                <i class="fa fa-list"></i> 
                Liste des opérations archivées
                (<?php echo $totalArchives; ?> opération<?php if ($totalArchives > 1) echo 's'; ?>)
            </div>
            <div class="table-tools">
                <span class="badge"><i class="fa fa-refresh fa-sm"></i> Actualisé à <?php echo date('H:i'); ?></span>
            </div>
        </div>
        <div class="table-responsive">
            <table id="archives-table" class="table table-hover">
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
                        <th class="text-center" width="100">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($archives)) : ?>
                        <?php 
                        $agencesCache = array();
                        
                        foreach($archives as $operation) : 
                            if (!is_array($operation)) continue;
                            
                            $agenceNom = '';
                            $agenceId = isset($operation['agence_id']) ? $operation['agence_id'] : '';
                            
                            if (!empty($agenceId)) {
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
                                        if (empty($agenceNom)) {
                                            $agencesCache[$agenceId] = "Agence " . $agenceId;
                                            $agenceNom = "Agence " . $agenceId;
                                        }
                                    } catch (Exception $e) {
                                        $agencesCache[$agenceId] = "Agence " . $agenceId;
                                        $agenceNom = "Agence " . $agenceId;
                                    }
                                } else {
                                    $agenceNom = $agencesCache[$agenceId];
                                }
                            }
                            
                            // Déterminer le statut
                            $statusClass = 'status-termine';
                            $statut = isset($operation['statut']) ? strtolower($operation['statut']) : '';
                            if ($statut === 'confirmé' || $statut === 'confirme') {
                                $statusClass = 'status-termine';
                                $statut = 'Confirmé';
                            } elseif ($statut === 'annulé' || $statut === 'annule') {
                                $statusClass = 'status-annule';
                                $statut = 'Annulé';
                            } else {
                                $statusClass = 'status-annule';
                                $statut = 'Inconnu';
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
                            if (isset($operation['date_entree']) && !empty($operation['date_entree'])) {
                                if ($operation['date_entree'] instanceof DateTime) {
                                    $date_entree = $operation['date_entree']->format('d/m/Y H:i');
                                } elseif (is_string($operation['date_entree'])) {
                                    $timestamp = strtotime($operation['date_entree']);
                                    if ($timestamp && $timestamp > 0) {
                                        $date_entree = date('d/m/Y H:i', $timestamp);
                                    }
                                }
                            }
                            
                            // Icône du type
                            $typeIcon = '';
                            if ($type_operation === 'virement') {
                                $typeIcon = '<i class="fa fa-exchange" style="color:var(--info); font-size:16px;" title="Virement"></i>';
                            } else {
                                $typeIcon = '<i class="fa fa-money" style="color:var(--success); font-size:16px;" title="Chèque"></i>';
                            }
                            
                            // Construction de la ligne
                            echo '<tr id="archive-' . $operation_id . '">';
                            echo '<td class="text-center">' . $typeIcon . '</td>';
                            echo '<td class="text-center"><span style="font-size:11px;">' . $date_entree . '</span></td>';
                            echo '<td><strong>' . $nom_client . '</strong></td>';
                            echo '<td><span style="font-size:12px;">' . $numero_cheque . '</span></td>';
                            echo '<td class="text-right"><strong>' . $montant . '</strong> FCFA</td>';
                            echo '<td>' . $banque . '</td>';
                            echo '<td class="text-center"><span class="status-badge ' . $statusClass . '">' . $statut . '</span></td>';
                            echo '<td class="text-center"><span class="status-badge ' . ($etatConfText === 'Oui' ? 'status-termine' : 'status-annule') . '">' . $etatConfText . '</span></td>';
                            echo '<td class="text-center"><span class="status-badge ' . ($etatValidText === 'Oui' ? 'status-termine' : 'status-annule') . '">' . $etatValidText . '</span></td>';
                            echo '<td>' . htmlspecialchars($agenceNom) . '</td>';
                            echo '<td class="text-center"><div class="action-icons">';
                            echo '<a href="#" class="details-archive action-link purple" data-id="' . $operation_id . '" title="Voir les détails">';
                            echo '<i class="fa fa-info-circle"></i></a>';
                            echo '</div></td></tr>';
                            
                        endforeach; 
                        ?>
                    <?php else : ?>
                    <tr>
                        <td colspan="11" class="text-center">
                            <div class="empty-state">
                                <i class="fa fa-archive"></i>
                                <h4>Aucune opération archivée</h4>
                                <p>
                                    <?php if (!empty($search)): ?>
                                        Aucun résultat pour "<?php echo htmlspecialchars($search); ?>"
                                    <?php elseif (!empty($type)): ?>
                                        Aucun <?php echo $type === 'cheque' ? 'chèque' : 'virement'; ?> archivé
                                    <?php else: ?>
                                        Aucune opération terminée ou annulée
                                    <?php endif; ?>
                                </p>
                            </div>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination simplifiée -->
        <?php if ($totalPages > 1) : ?>
        <div style="display:flex; flex-wrap:wrap; justify-content:space-between; align-items:center; margin-top:20px;">
            <div class="pagination-info">
                Affichage de <?php echo ($totalArchives > 0 ? $startIndex + 1 : 0); ?> à <?php echo $endIndex; ?> sur <?php echo $totalArchives; ?> entrées
            </div>
            <div class="pagination-modern">
                <?php
                // URL de base
                $baseUrl = '?p=confirmationCheque.archives';
                
                // Paramètres supplémentaires
                $params = '';
                if (!empty($search)) {
                    $params .= '&search=' . urlencode($search);
                }
                if (!empty($type)) {
                    $params .= '&type=' . urlencode($type);
                }
                
                // Bouton Précédent
                if ($page > 1) {
                    $prevUrl = $baseUrl . '&page=' . ($page - 1) . $params;
                    echo '<a href="' . $prevUrl . '" class="pagination-item"><i class="fa fa-chevron-left"></i></a>';
                } else {
                    echo '<span class="pagination-item disabled"><i class="fa fa-chevron-left"></i></span>';
                }
                
                // Numéros de page
                for ($i = 1; $i <= $totalPages; $i++) {
                    if ($i == $page) {
                        echo '<span class="pagination-item active">' . $i . '</span>';
                    } else {
                        $pageUrl = $baseUrl . '&page=' . $i . $params;
                        echo '<a href="' . $pageUrl . '" class="pagination-item">' . $i . '</a>';
                    }
                }
                
                // Bouton Suivant
                if ($page < $totalPages) {
                    $nextUrl = $baseUrl . '&page=' . ($page + 1) . $params;
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

<!-- MODAL DÉTAILS -->
<div id="modal-details-archive" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header modern-modal-header">
                <h5 class="modal-title"><i class="fa fa-info-circle"></i> Détails de l'opération archivée</h5>
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

<script>
// ============================================
// FONCTIONS UTILITAIRES
// ============================================
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
// FILTRES ET RECHERCHE
// ============================================
function filterByType() {
    var typeSelect = document.getElementById('filter-type');
    var selectedType = typeSelect.value;
    
    var url = '?p=confirmationCheque.archives&page=1';
    var searchInput = document.getElementById('search-archives');
    if (searchInput && searchInput.value.trim()) {
        url += '&search=' + encodeURIComponent(searchInput.value.trim());
    }
    if (selectedType) {
        url += '&type=' + selectedType;
    }
    
    window.location.href = url;
}

function effectuerRecherche() {
    var searchInput = document.getElementById('search-archives');
    var searchTerm = searchInput.value.trim();
    var typeSelect = document.getElementById('filter-type');
    var selectedType = typeSelect.value;
    
    var url = '?p=confirmationCheque.archives&page=1';
    if (searchTerm !== '') {
        url += '&search=' + encodeURIComponent(searchTerm);
    }
    if (selectedType !== '') {
        url += '&type=' + selectedType;
    }
    
    window.location.href = url;
}

function effacerRecherche() {
    window.location.href = '?p=confirmationCheque.archives&page=1';
}

// ============================================
// DÉTAILS
// ============================================
function ouvrirDetailsArchive(operationId) {
    console.log('📋 Ouverture détails archive:', operationId);
    
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
        jQuery('#modal-details-archive').modal('show');
    }
}

// ============================================
// INITIALISATION
// ============================================
$(document).ready(function() {
    console.log('🚀 Page archives initialisée');
    console.log('📍 Page actuelle:', <?php echo $page; ?>);
    
    // Filtres
    $('#filter-type').change(function() {
        filterByType();
    });
    
    $('#btn-search').click(function() {
        effectuerRecherche();
    });
    
    $('#search-archives').keypress(function(e) {
        if (e.which === 13) {
            effectuerRecherche();
        }
    });
    
    $('#btn-clear-search').click(function() {
        effacerRecherche();
    });
    
    // Gestion des événements sur les boutons d'action
    $(document).on('click', '.details-archive', function(e) {
        e.preventDefault();
        var operationId = $(this).data('id');
        ouvrirDetailsArchive(operationId);
    });
});
</script>