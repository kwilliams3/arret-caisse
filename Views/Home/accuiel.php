<?php
/**
 * Dashboard Arrêts Caisses - Version simplifiée
 */

use Core\Model\App;
use Core\Model\Session;

$auth = App::getDBAuth();
$session = Session::getInstance();

$user = $_SESSION['user'];

// Récupération des données (à adapter selon votre base de données)
$nbreAgenceX3 = isset($nbreAgenceX3) ? $nbreAgenceX3 : 12;
$nbreAgenceSage = isset($nbreAgenceSage) ? $nbreAgenceSage : 8;
$nbreAgenceLD = isset($nbreAgenceLD) ? $nbreAgenceLD : 5;

$nbreArretsX3Hier = isset($nbreArretsX3Hier) ? $nbreArretsX3Hier : 8;
$nbreArretsX3Today = isset($nbreArretsX3Today) ? $nbreArretsX3Today : 10;
$nbreArrets100Hier = isset($nbreArrets100Hier) ? $nbreArrets100Hier : 5;
$nbreArrets100Today = isset($nbreArrets100Today) ? $nbreArrets100Today : 6;
$nbreArretsLDHier = isset($nbreArretsLDHier) ? $nbreArretsLDHier : 3;
$nbreArretsLDToday = isset($nbreArretsLDToday) ? $nbreArretsLDToday : 4;

// Calcul des totaux
$totalArretsHier = $nbreArretsX3Hier + $nbreArrets100Hier + $nbreArretsLDHier;
$totalArretsToday = $nbreArretsX3Today + $nbreArrets100Today + $nbreArretsLDToday;
$totalAgences = $nbreAgenceX3 + $nbreAgenceSage + $nbreAgenceLD;

// Campagnes (exemple)
$campagnes = [
    [
        'idCampagne' => 1,
        'principe' => 'Campagne arrêts caisses X3 - Juillet 2025',
        'dateFin' => '2025-07-31',
        'agencesConcernes' => 'Toutes agences X3'
    ],
    [
        'idCampagne' => 2,
        'principe' => 'Campagne arrêts caisses SAGE - Août 2025',
        'dateFin' => '2025-08-15',
        'agencesConcernes' => 'Agences SAGE'
    ],
    [
        'idCampagne' => 3,
        'principe' => 'Campagne arrêts caisses LD - Septembre 2025',
        'dateFin' => '2025-09-30',
        'agencesConcernes' => 'Agences LD'
    ]
];
?>

<!-- JQUERY LOCAL -->
<script src="Public/js/jquery-2.1.4.min.js"></script>

<!-- BOOTSTRAP LOCAL -->
<link rel="stylesheet" href="Public/css/bootstrap/css/bootstrap.min.css">
<script src="Public/js/bootstrap.min.js"></script>

<!-- FONT AWESOME 4.5.0 LOCAL -->
<link rel="stylesheet" href="Public/font-awesome/4.5.0/css/font-awesome.min.css">

<style>
/* Modern Dashboard Styles */
:root {
    --primary: #4361ee;
    --primary-light: #e8edff;
    --primary-dark: #3a56d4;
    --secondary: #3a0ca3;
    --success: #2ecc71;
    --success-light: #d5f5e3;
    --warning: #f39c12;
    --warning-light: #fef5e7;
    --danger: #e74c3c;
    --danger-light: #fdedec;
    --info: #3498db;
    --info-light: #e8f4fc;
    --purple: #8e44ad;
    --purple-light: #f3e8ff;
    --orange: #ed8936;
    --orange-light: #fef5e7;
    --teal: #38b2ac;
    --teal-light: #e6fffa;
    --pink: #ed64a6;
    --pink-light: #fff5f7;
    --cyan: #0bc5ea;
    --cyan-light: #e6fffa;
    --indigo: #667eea;
    --indigo-light: #e8edff;
    --dark: #2c3e50;
    --gray: #6c757d;
    --gray-light: #f8f9fa;
    --gray-border: #e9ecef;
    --border-radius: 12px;
    --border-radius-sm: 8px;
    --shadow: 0 4px 12px rgba(0,0,0,0.08);
    --shadow-hover: 0 8px 24px rgba(0,0,0,0.12);
}

/* Modern Dashboard Container */
.modern-dashboard {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    max-width: 100%;
    margin: 0 auto;
    padding: 0;
}

/* Modern Header Banner */
.modern-dashboard-header {
    background: linear-gradient(135deg, #0d1b3e 0%, #1a2b5c 100%);
    border-radius: var(--border-radius);
    padding: 25px 30px;
    margin-bottom: 25px;
    color: white;
    position: relative;
    overflow: hidden;
    box-shadow: var(--shadow);
}

.dashboard-header-content {
    position: relative;
    z-index: 2;
}

.dashboard-title {
    font-size: 20px;
    font-weight: 700;
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    gap: 12px;
}

.dashboard-title i {
    color: #4fc3f7;
    font-size: 22px;
    background: rgba(79, 195, 247, 0.1);
    width: 42px;
    height: 42px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.dashboard-subtitle {
    font-size: 14px;
    color: rgba(255, 255, 255, 0.8);
    line-height: 1.5;
}

.dashboard-stats {
    display: flex;
    gap: 15px;
    margin-top: 20px;
    flex-wrap: wrap;
}

.dashboard-stat {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 10px;
    padding: 10px 15px;
    backdrop-filter: blur(10px);
}

.dashboard-stat .stat-label {
    font-size: 11px;
    color: rgba(255, 255, 255, 0.7);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 4px;
}

.dashboard-stat .stat-value {
    font-size: 16px;
    font-weight: 700;
    color: white;
}

/* Modern Categories Grid */
.modern-categories-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
    gap: 20px;
    margin-bottom: 25px;
}

.modern-category-card {
    background: white;
    border-radius: var(--border-radius);
    padding: 20px;
    box-shadow: var(--shadow);
    border: 1px solid var(--gray-border);
    transition: all 0.3s ease;
    height: 100%;
}

.modern-category-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-hover);
}

.category-card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 1px solid var(--gray-border);
}

.category-title {
    font-size: 16px;
    font-weight: 600;
    color: var(--dark);
    display: flex;
    align-items: center;
    gap: 10px;
}

.category-title i {
    font-size: 16px;
    width: 32px;
    height: 32px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
}

/* Category Colors */
.category-title i.x3 { background: var(--primary); }
.category-title i.sage { background: var(--success); }
.category-title i.ld { background: var(--warning); }
.category-title i.global { background: var(--purple); }

.category-total {
    font-size: 12px;
    font-weight: 600;
    color: var(--primary);
    background: var(--primary-light);
    padding: 4px 10px;
    border-radius: 20px;
}

/* Modern Stats Grid - Version simplifiée avec 2 colonnes seulement */
.modern-stats-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
}

.modern-stat-item {
    background: var(--gray-light);
    border-radius: var(--border-radius-sm);
    padding: 15px;
    text-align: center;
    transition: all 0.2s ease;
    border: 1px solid transparent;
}

.modern-stat-item:hover {
    border-color: var(--primary);
    background: white;
    transform: translateY(-2px);
}

.stat-item-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 10px;
    font-size: 16px;
    color: white;
}

.stat-item-value {
    font-size: 20px;
    font-weight: 700;
    color: var(--dark);
    margin: 5px 0;
    line-height: 1;
}

.stat-item-label {
    font-size: 11px;
    color: var(--gray);
    text-transform: uppercase;
    letter-spacing: 0.3px;
    font-weight: 600;
    margin-top: 4px;
}

/* Section separator comme dans l'ancien code */
.section-separator {
    margin: 30px 0 20px 0;
    padding: 10px 0;
    border-bottom: 2px solid var(--gray-border);
}

.section-separator h3 {
    font-size: 18px;
    font-weight: 600;
    color: var(--dark);
    display: flex;
    align-items: center;
    gap: 10px;
}

.section-separator h3 i {
    color: var(--primary);
    font-size: 20px;
}

/* Quick Actions */
.quick-actions {
    background: white;
    border-radius: var(--border-radius);
    padding: 20px;
    margin-top: 25px;
    box-shadow: var(--shadow);
    border: 1px solid var(--gray-border);
}

.quick-actions-title {
    font-size: 16px;
    font-weight: 600;
    color: var(--dark);
    margin-bottom: 15px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.quick-actions-title i {
    color: var(--primary);
}

.quick-actions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
    gap: 12px;
}

.quick-action-btn {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 15px;
    background: var(--gray-light);
    border-radius: var(--border-radius-sm);
    color: var(--dark);
    text-decoration: none;
    transition: all 0.2s ease;
    border: 1px solid transparent;
    font-size: 13px;
    font-weight: 500;
}

.quick-action-btn:hover {
    background: white;
    border-color: var(--primary);
    color: var(--primary);
    transform: translateY(-2px);
    text-decoration: none;
    box-shadow: var(--shadow);
}

.quick-action-btn i {
    font-size: 16px;
    color: var(--primary);
}

/* Modern Modal */
.modern-dashboard-modal .modal-header {
    background: linear-gradient(135deg, #0d1b3e 0%, #1a2b5c 100%);
    color: white;
    border-radius: 12px 12px 0 0;
    padding: 20px 25px;
    border: none;
}

.modern-dashboard-modal .modal-title {
    font-size: 16px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 12px;
}

.modern-dashboard-modal .modal-body {
    padding: 25px;
    max-height: 60vh;
    overflow-y: auto;
}

.campaign-card {
    background: white;
    border-radius: 10px;
    padding: 15px;
    margin-bottom: 12px;
    border: 1px solid var(--gray-border);
    transition: all 0.2s ease;
}

.campaign-card:hover {
    border-color: var(--primary);
    box-shadow: var(--shadow);
    transform: translateY(-2px);
}

.campaign-id {
    font-size: 14px;
    font-weight: 600;
    color: var(--primary);
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.campaign-principle {
    font-size: 13px;
    color: var(--dark);
    margin-bottom: 10px;
    line-height: 1.4;
}

.campaign-details {
    display: flex;
    gap: 15px;
    font-size: 11px;
    color: var(--gray);
}

.campaign-details i {
    margin-right: 4px;
}

/* Empty State */
.modern-empty-state {
    padding: 40px 20px;
    text-align: center;
    color: var(--gray);
}

.modern-empty-state i {
    font-size: 48px;
    color: var(--gray-border);
    margin-bottom: 15px;
}

.modern-empty-state h4 {
    font-size: 16px;
    color: var(--dark);
    margin-bottom: 8px;
}

/* Animations */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.modern-category-card {
    animation: fadeInUp 0.4s ease forwards;
    animation-delay: calc(var(--index, 0) * 0.05s);
    opacity: 0;
}

/* Responsive Design */
@media (max-width: 1200px) {
    .modern-categories-grid {
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    }
}

@media (max-width: 768px) {
    .modern-categories-grid {
        grid-template-columns: 1fr;
    }
    
    .modern-dashboard-header {
        padding: 20px;
    }
    
    .dashboard-stats {
        flex-direction: column;
        gap: 10px;
    }
    
    .modern-stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .quick-actions-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 576px) {
    .modern-stats-grid {
        grid-template-columns: 1fr;
    }
    
    .modern-category-card {
        padding: 15px;
    }
    
    .category-card-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }
    
    .category-total {
        align-self: flex-start;
    }
}

/* FontAwesome Fixes pour FA4 */
.fa {
    font-family: FontAwesome !important;
}
</style>

<div class="modern-dashboard">
    <!-- Modern Header Banner -->
    <div class="modern-dashboard-header">
        <div class="dashboard-header-content">
            <h1 class="dashboard-title">
                <i class="fa fa-tachometer"></i>
                DASHBOARD ARRÊTS CAISSES
            </h1>
            <p class="dashboard-subtitle">
                Vue d'ensemble des arrêts caisses par type d'agence
            </p>
            
            <div class="dashboard-stats">
                <div class="dashboard-stat">
                    <div class="stat-label">Total Agences</div>
                    <div class="stat-value"><?= number_format($totalAgences, 0, ',', ' ') ?></div>
                </div>
                <div class="dashboard-stat">
                    <div class="stat-label">Arrêts Hier</div>
                    <div class="stat-value"><?= number_format($totalArretsHier, 0, ',', ' ') ?></div>
                </div>
                <div class="dashboard-stat">
                    <div class="stat-label">Arrêts Aujourd'hui</div>
                    <div class="stat-value"><?= number_format($totalArretsToday, 0, ',', ' ') ?></div>
                </div>
                <div class="dashboard-stat">
                    <div class="stat-label">Dernière mise à jour</div>
                    <div class="stat-value"><?= date('H:i') ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Section Agences X3 -->
    <div class="section-separator">
        <h3>
            <i class="fa fa-building"></i>
            Agences X3
        </h3>
    </div>
    
    <div class="modern-categories-grid">
        <!-- Agences X3 - Hier -->
        <div class="modern-category-card" style="--index: 0">
            <div class="category-card-header">
                <div class="category-title">
                    <i class="fa fa-calendar x3"></i>
                    Arrêts de la veille
                </div>
                <div class="category-total">
                    <?= number_format($nbreAgenceX3, 0, ',', ' ') ?> agences
                </div>
            </div>
            
            <div class="modern-stats-grid">
                <!-- OK -->
                <div class="modern-stat-item">
                    <div class="stat-item-icon" style="background: var(--success)">
                        <i class="fa fa-check-circle"></i>
                    </div>
                    <div class="stat-item-value"><?= number_format($nbreArretsX3Hier, 0, ',', ' ') ?></div>
                    <div class="stat-item-label">Nbre d'arrêts OK</div>
                </div>
                
                <!-- KO -->
                <div class="modern-stat-item">
                    <div class="stat-item-icon" style="background: var(--danger)">
                        <i class="fa fa-times-circle"></i>
                    </div>
                    <div class="stat-item-value"><?= number_format($nbreAgenceX3 - $nbreArretsX3Hier, 0, ',', ' ') ?></div>
                    <div class="stat-item-label">Nbre d'arrêts KO</div>
                </div>
            </div>
        </div>

        <!-- Agences X3 - Aujourd'hui -->
        <div class="modern-category-card" style="--index: 1">
            <div class="category-card-header">
                <div class="category-title">
                    <i class="fa fa-check-square-o x3"></i>
                    Arrêts aujourd'hui
                </div>
                <div class="category-total">
                    <?= number_format($nbreAgenceX3, 0, ',', ' ') ?> agences
                </div>
            </div>
            
            <div class="modern-stats-grid">
                <!-- OK -->
                <div class="modern-stat-item">
                    <div class="stat-item-icon" style="background: var(--success)">
                        <i class="fa fa-check-circle"></i>
                    </div>
                    <div class="stat-item-value"><?= number_format($nbreArretsX3Today, 0, ',', ' ') ?></div>
                    <div class="stat-item-label">Nbre d'arrêts OK</div>
                </div>
                
                <!-- KO -->
                <div class="modern-stat-item">
                    <div class="stat-item-icon" style="background: var(--danger)">
                        <i class="fa fa-times-circle"></i>
                    </div>
                    <div class="stat-item-value"><?= number_format($nbreAgenceX3 - $nbreArretsX3Today, 0, ',', ' ') ?></div>
                    <div class="stat-item-label">Nbre d'arrêts KO</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Section Agences SAGE 100 -->
    <div class="section-separator">
        <h3>
            <i class="fa fa-database"></i>
            Agences SAGE 100
        </h3>
    </div>
    
    <div class="modern-categories-grid">
        <!-- Agences SAGE - Hier -->
        <div class="modern-category-card" style="--index: 2">
            <div class="category-card-header">
                <div class="category-title">
                    <i class="fa fa-calendar sage"></i>
                    Arrêts de la veille
                </div>
                <div class="category-total">
                    <?= number_format($nbreAgenceSage, 0, ',', ' ') ?> agences
                </div>
            </div>
            
            <div class="modern-stats-grid">
                <!-- OK -->
                <div class="modern-stat-item">
                    <div class="stat-item-icon" style="background: var(--success)">
                        <i class="fa fa-check-circle"></i>
                    </div>
                    <div class="stat-item-value"><?= number_format($nbreArrets100Hier, 0, ',', ' ') ?></div>
                    <div class="stat-item-label">Nbre d'arrêts OK</div>
                </div>
                
                <!-- KO -->
                <div class="modern-stat-item">
                    <div class="stat-item-icon" style="background: var(--danger)">
                        <i class="fa fa-times-circle"></i>
                    </div>
                    <div class="stat-item-value"><?= number_format($nbreAgenceSage - $nbreArrets100Hier, 0, ',', ' ') ?></div>
                    <div class="stat-item-label">Nbre d'arrêts KO</div>
                </div>
            </div>
        </div>

        <!-- Agences SAGE - Aujourd'hui -->
        <div class="modern-category-card" style="--index: 3">
            <div class="category-card-header">
                <div class="category-title">
                    <i class="fa fa-check-square-o sage"></i>
                    Arrêts aujourd'hui
                </div>
                <div class="category-total">
                    <?= number_format($nbreAgenceSage, 0, ',', ' ') ?> agences
                </div>
            </div>
            
            <div class="modern-stats-grid">
                <!-- OK -->
                <div class="modern-stat-item">
                    <div class="stat-item-icon" style="background: var(--success)">
                        <i class="fa fa-check-circle"></i>
                    </div>
                    <div class="stat-item-value"><?= number_format($nbreArrets100Today, 0, ',', ' ') ?></div>
                    <div class="stat-item-label">Nbre d'arrêts OK</div>
                </div>
                
                <!-- KO -->
                <div class="modern-stat-item">
                    <div class="stat-item-icon" style="background: var(--danger)">
                        <i class="fa fa-times-circle"></i>
                    </div>
                    <div class="stat-item-value"><?= number_format($nbreAgenceSage - $nbreArrets100Today, 0, ',', ' ') ?></div>
                    <div class="stat-item-label">Nbre d'arrêts KO</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Section Agences LD -->
    <div class="section-separator">
        <h3>
            <i class="fa fa-code-fork"></i>
            Agences LD
        </h3>
    </div>
    
    <div class="modern-categories-grid">
        <!-- Agences LD - Hier -->
        <div class="modern-category-card" style="--index: 4">
            <div class="category-card-header">
                <div class="category-title">
                    <i class="fa fa-calendar ld"></i>
                    Arrêts de la veille
                </div>
                <div class="category-total">
                    <?= number_format($nbreAgenceLD, 0, ',', ' ') ?> agences
                </div>
            </div>
            
            <div class="modern-stats-grid">
                <!-- OK -->
                <div class="modern-stat-item">
                    <div class="stat-item-icon" style="background: var(--success)">
                        <i class="fa fa-check-circle"></i>
                    </div>
                    <div class="stat-item-value"><?= number_format($nbreArretsLDHier, 0, ',', ' ') ?></div>
                    <div class="stat-item-label">Nbre d'arrêts OK</div>
                </div>
                
                <!-- KO -->
                <div class="modern-stat-item">
                    <div class="stat-item-icon" style="background: var(--danger)">
                        <i class="fa fa-times-circle"></i>
                    </div>
                    <div class="stat-item-value"><?= number_format($nbreAgenceLD - $nbreArretsLDHier, 0, ',', ' ') ?></div>
                    <div class="stat-item-label">Nbre d'arrêts KO</div>
                </div>
            </div>
        </div>

        <!-- Agences LD - Aujourd'hui -->
        <div class="modern-category-card" style="--index: 5">
            <div class="category-card-header">
                <div class="category-title">
                    <i class="fa fa-check-square-o ld"></i>
                    Arrêts aujourd'hui
                </div>
                <div class="category-total">
                    <?= number_format($nbreAgenceLD, 0, ',', ' ') ?> agences
                </div>
            </div>
            
            <div class="modern-stats-grid">
                <!-- OK -->
                <div class="modern-stat-item">
                    <div class="stat-item-icon" style="background: var(--success)">
                        <i class="fa fa-check-circle"></i>
                    </div>
                    <div class="stat-item-value"><?= number_format($nbreArretsLDToday, 0, ',', ' ') ?></div>
                    <div class="stat-item-label">Nbre d'arrêts OK</div>
                </div>
                
                <!-- KO -->
                <div class="modern-stat-item">
                    <div class="stat-item-icon" style="background: var(--danger)">
                        <i class="fa fa-times-circle"></i>
                    </div>
                    <div class="stat-item-value"><?= number_format($nbreAgenceLD - $nbreArretsLDToday, 0, ',', ' ') ?></div>
                    <div class="stat-item-label">Nbre d'arrêts KO</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Vue Globale -->
    <div class="section-separator">
        <h3>
            <i class="fa fa-pie-chart"></i>
            Vue Globale
        </h3>
    </div>
    
    <div class="modern-categories-grid">
        <!-- Global Hier -->
        <div class="modern-category-card" style="--index: 6">
            <div class="category-card-header">
                <div class="category-title">
                    <i class="fa fa-calendar global"></i>
                    Total arrêts de la veille
                </div>
                <div class="category-total">
                    <?= number_format($totalAgences, 0, ',', ' ') ?> agences
                </div>
            </div>
            
            <div class="modern-stats-grid">
                <!-- OK -->
                <div class="modern-stat-item">
                    <div class="stat-item-icon" style="background: var(--success)">
                        <i class="fa fa-check-circle"></i>
                    </div>
                    <div class="stat-item-value"><?= number_format($totalArretsHier, 0, ',', ' ') ?></div>
                    <div class="stat-item-label">Nbre d'arrêts OK</div>
                </div>
                
                <!-- KO -->
                <div class="modern-stat-item">
                    <div class="stat-item-icon" style="background: var(--danger)">
                        <i class="fa fa-times-circle"></i>
                    </div>
                    <div class="stat-item-value"><?= number_format($totalAgences - $totalArretsHier, 0, ',', ' ') ?></div>
                    <div class="stat-item-label">Nbre d'arrêts KO</div>
                </div>
            </div>
        </div>

        <!-- Global Today -->
        <div class="modern-category-card" style="--index: 7">
            <div class="category-card-header">
                <div class="category-title">
                    <i class="fa fa-check-square-o global"></i>
                    Total arrêts aujourd'hui
                </div>
                <div class="category-total">
                    <?= number_format($totalAgences, 0, ',', ' ') ?> agences
                </div>
            </div>
            
            <div class="modern-stats-grid">
                <!-- OK -->
                <div class="modern-stat-item">
                    <div class="stat-item-icon" style="background: var(--success)">
                        <i class="fa fa-check-circle"></i>
                    </div>
                    <div class="stat-item-value"><?= number_format($totalArretsToday, 0, ',', ' ') ?></div>
                    <div class="stat-item-label">Nbre d'arrêts OK</div>
                </div>
                
                <!-- KO -->
                <div class="modern-stat-item">
                    <div class="stat-item-icon" style="background: var(--danger)">
                        <i class="fa fa-times-circle"></i>
                    </div>
                    <div class="stat-item-value"><?= number_format($totalAgences - $totalArretsToday, 0, ',', ' ') ?></div>
                    <div class="stat-item-label">Nbre d'arrêts KO</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="quick-actions">
        <h3 class="quick-actions-title">
            <i class="fa fa-bolt"></i>
            Actions Rapides
        </h3>
        <div class="quick-actions-grid">
            <a href="<?= App::url('arretsCaisses.index') ?>" class="quick-action-btn">
                <i class="fa fa-archive"></i>
                Arrêts Caissière
            </a>
            <a href="<?= App::url('arretsCaissesLD.index') ?>" class="quick-action-btn">
                <i class="fa fa-archive"></i>
                Arrêts LD
            </a>
            <a href="<?= App::url('arretsDouanier.index') ?>" class="quick-action-btn">
                <i class="fa fa-archive"></i>
                Arrêts Chef
            </a>
            <a href="<?= App::url('arretsCaisses.arretSage') ?>" class="quick-action-btn">
                <i class="fa fa-archive"></i>
                Arrêts SAGE
            </a>
            <a href="#" class="quick-action-btn" id="viewCampagnes">
                <i class="fa fa-calendar"></i>
                Voir Campagnes
            </a>
            <a href="<?= App::url('home.accuiel') ?>" class="quick-action-btn">
                <i class="fa fa-refresh"></i>
                Actualiser
            </a>
        </div>
    </div>
</div>

<!-- Modern Modal pour les campagnes -->
<div id="modal-ChargesCampagnes1" class="modal fade modern-dashboard-modal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fa fa-tags"></i>
                    Campagnes en cours
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <?php if (!empty($campagnes)): ?>
                    <?php foreach ($campagnes as $campagne): ?>
                    <div class="campaign-card">
                        <div class="campaign-id">
                            <i class="fa fa-tag"></i>
                            Campagne N° <?= isset($campagne['idCampagne']) ? $campagne['idCampagne'] : '' ?>
                        </div>
                        <div class="campaign-principle">
                            <?= isset($campagne['principe']) ? htmlspecialchars($campagne['principe']) : '' ?>
                        </div>
                        <div class="campaign-details">
                            <div>
                                <i class="fa fa-calendar"></i>
                                Fin: <?= isset($campagne['dateFin']) ? date('d/m/Y', strtotime($campagne['dateFin'])) : '' ?>
                            </div>
                            <div>
                                <i class="fa fa-building"></i>
                                <?= isset($campagne['agencesConcernes']) ? htmlspecialchars($campagne['agencesConcernes']) : '' ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="modern-empty-state">
                        <i class="fa fa-inbox"></i>
                        <h4>Aucune campagne en cours</h4>
                        <p>Toutes les campagnes sont terminées</p>
                    </div>
                <?php endif; ?>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fa fa-times"></i>
                    Fermer
                </button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Initialiser les tooltips
    $('[title]').tooltip({
        placement: 'top',
        trigger: 'hover'
    });
    
    // Gestion du modal des campagnes
    $('#viewCampagnes').click(function(e) {
        e.preventDefault();
        $('#modal-ChargesCampagnes1').modal('show');
    });
    
    $('#modal-ChargesCampagnes1').on('show.bs.modal', function() {
        $(this).css('z-index', '9999');
    });
    
    // Animation des cartes au chargement
    setTimeout(function() {
        $('.modern-category-card').each(function(index) {
            $(this).css('--index', index);
            $(this).css('animation-delay', (index * 0.05) + 's');
        });
    }, 100);
    
    // Filtrage des catégories
    var searchInput = $('<input type="text" class="form-control" placeholder="Rechercher un type d\'agence..." style="margin-bottom: 15px; font-size: 14px;">');
    searchInput.insertAfter('.modern-dashboard-header');
    
    searchInput.on('keyup', function() {
        var searchTerm = $(this).val().toLowerCase();
        $('.modern-category-card').each(function() {
            var title = $(this).find('.category-title').text().toLowerCase();
            if (title.includes(searchTerm) || searchTerm === '') {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });
    
    // Mettre à jour l'heure en temps réel
    function updateTime() {
        var now = new Date();
        var timeString = now.getHours().toString().padStart(2, '0') + ':' + 
                        now.getMinutes().toString().padStart(2, '0');
        $('.dashboard-stat:last-child .stat-value').text(timeString);
    }
    
    // Mettre à jour l'heure toutes les minutes
    setInterval(updateTime, 60000);
});
</script>