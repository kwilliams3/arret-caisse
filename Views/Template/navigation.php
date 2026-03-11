<?php

use Core\Model\App;
use Core\Model\Session;
use Core\Database\Client;

$params = $_GET;
$param = $_GET;

$auth = App::getDBAuth();
$session = Session::getInstance();

if(isset($_SESSION['user'])){

    $user = $_SESSION['user'];
    
}else{
    App::redirect(App::url('home.index'));
}

// Récupération des variables de session si elles existent (optionnel)
$montantVentes = isset($_SESSION['montantVentes']) ? $_SESSION['montantVentes'] : 0;
$montantVentesAll = isset($_SESSION['montantVentesAll']) ? $_SESSION['montantVentesAll'] : 0;
$objectifAgences = isset($_SESSION['objectifAgences']) ? $_SESSION['objectifAgences'] : 1000000;
$objectifUser = isset($user['objectif']) ? $user['objectif'] : 100000;

// Calculs similaires à la page d'origine
$CAPGlobal = round((100 * $montantVentesAll) / $objectifAgences, 2);
$nbreJrs = (int)date("d");
$avance = ($objectifUser * $nbreJrs) / 30;
$CAP = round((100 * $montantVentes) / $objectifUser, 2);

// Préparation des données pour typeahead
$articles = isset($articles) ? $articles : [];
$arts = isset($arts) ? $arts : [];

// Liste des modules pour la recherche
$modulesSearchList = [];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <meta charset="utf-8" />
    <title>ARRÊTS CAISSES - Système de Gestion</title>
    <meta name="description" content="Système de gestion des arrêts de caisses" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0" />

    <!-- bootstrap -->
    <link href="Public/css/bootstrap/css/bootstrap.min.css" rel="stylesheet" /> 
    
    <!-- FONT AWESOME 4.5.0 -->
    <link rel="stylesheet" href="Public/font-awesome/4.5.0/css/font-awesome.min.css">
    
    <!-- page specific plugin styles -->
    <link rel="stylesheet" href="Public/css/jquery-ui.min.css" />
    <link rel="stylesheet" href="Public/css/ui.jqgrid.min.css" />
    <link rel="stylesheet" href="Public/css/chosen.min.css" />
    
    <!-- text fonts-->
    <link rel="stylesheet" href="Public/css/fonts.googleapis.com.css" /> 

    <!-- ace styles -->
    <link rel="stylesheet" href="Public/css/ace.min.css" class="ace-main-stylesheet" id="main-ace-style" />

    <!--[if lte IE 9]>
    <link rel="stylesheet" href="Public/css/ace-part2.min.css" class="ace-main-stylesheet" />
    <![endif]-->
    <link rel="stylesheet" href="Public/css/ace-skins.min.css" />
    <link rel="stylesheet" href="Public/css/ace-rtl.min.css" />

    <!--[if lte IE 9]>
    <link rel="stylesheet" href="Public/css/ace-ie.min.css" />
    <![endif]-->

    <!-- Modern UI Styles -->
    <style>
        /* Modern UI Variables */
        :root {
            --primary: #4361ee;
            --primary-light: #e8edff;
            --secondary: #3a0ca3;
            --success: #2ecc71;
            --success-light: #d5f5e3;
            --warning: #f39c12;
            --warning-light: #fef5e7;
            --danger: #e74c3c;
            --danger-light: #fdedec;
            --info: #3498db;
            --info-light: #e8f4fc;
            --light: #f8f9fa;
            --dark: #2c3e50;
            --gray: #6c757d;
            --gray-light: #f5f7fa;
            --sidebar-width: 240px;
            --navbar-height: 70px;
            --border-radius: 12px;
            --border-radius-sm: 8px;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            --shadow: 0 4px 20px rgba(0,0,0,0.08);
            --shadow-lg: 0 10px 40px rgba(0,0,0,0.12);
            --sidebar-bg: linear-gradient(180deg, #0d1b3e 0%, #1a2b5c 100%);
            --sidebar-header: rgba(13, 27, 62, 0.95);
            --sidebar-hover: rgba(255,255,255,0.08);
            --sidebar-active: rgba(79, 195, 247, 0.1);
            --sidebar-border: rgba(255,255,255,0.1);
        }

        /* Modern Body & Layout */
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #f8fafc 0%, #eef2f7 100%);
            min-height: 100vh;
            overflow-x: hidden;
            color: #333;
            font-size: 14px;
            margin: 0;
            padding: 0;
            line-height: 1.6;
        }

        /* Modern Header */
        .modern-header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: var(--navbar-height);
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            border-bottom: 1px solid rgba(0,0,0,0.05);
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            z-index: 1100;
            display: flex;
            align-items: center;
            padding: 0 30px;
            backdrop-filter: blur(20px);
        }

        .header-container {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
        }

        .logo-container {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .logo {
            width: 44px;
            height: 44px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 22px;
            box-shadow: 0 6px 16px rgba(67, 97, 238, 0.3);
        }

        .logo i {
            color: white;
        }

        .brand-text {
            display: flex;
            flex-direction: column;
        }

        .brand-main {
            font-size: 18px;
            font-weight: 700;
            color: var(--dark);
            line-height: 1.2;
            letter-spacing: -0.3px;
        }

        .brand-sub {
            font-size: 12px;
            color: var(--gray);
            font-weight: 400;
        }

        /* Header Actions */
        .header-actions {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        /* KPI Badges */
        .kpi-badge {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 18px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 13px;
            transition: var(--transition);
            cursor: pointer;
            border: 1px solid transparent;
            box-shadow: 0 3px 8px rgba(0,0,0,0.08);
        }

        .kpi-badge:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.15);
        }

        .kpi-badge.global {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .kpi-badge.agence {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
            color: white;
        }

        .kpi-badge i {
            color: white;
        }

        .kpi-badge .badge {
            background: rgba(255,255,255,0.25);
            padding: 4px 12px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 12px;
            backdrop-filter: blur(10px);
            color: white;
        }

        /* User Profile */
        .user-profile-container {
            position: relative;
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 8px 16px;
            border-radius: 20px;
            background: white;
            cursor: pointer;
            transition: var(--transition);
            border: 1px solid rgba(0,0,0,0.08);
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
        }

        .user-profile:hover {
            background: #f8f9fa;
            border-color: var(--primary);
            box-shadow: 0 6px 20px rgba(67, 97, 238, 0.2);
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 16px;
            box-shadow: 0 4px 10px rgba(67, 97, 238, 0.3);
        }

        .user-info {
            display: flex;
            flex-direction: column;
        }

        .user-name {
            font-weight: 600;
            color: var(--dark);
            font-size: 14px;
        }

        .user-role {
            font-size: 11px;
            color: var(--gray);
            font-weight: 500;
        }

        .user-profile .dropdown-arrow {
            font-size: 12px;
            color: var(--gray);
            transition: var(--transition);
        }

        /* User Dropdown */
        .user-dropdown {
            position: absolute;
            top: calc(100% + 10px);
            right: 0;
            background: white;
            border-radius: 12px;
            box-shadow: var(--shadow-lg);
            min-width: 240px;
            z-index: 1101;
            border: 1px solid rgba(0,0,0,0.08);
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.3s ease;
        }

        .user-dropdown.show {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .dropdown-header {
            padding: 20px;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            background: linear-gradient(135deg, var(--primary-light) 0%, #f8f9fa 100%);
            border-radius: 12px 12px 0 0;
        }

        .dropdown-header .user-info {
            align-items: center;
            text-align: center;
        }

        .dropdown-header .user-avatar {
            margin: 0 auto 12px;
            width: 56px;
            height: 56px;
            font-size: 22px;
        }

        .dropdown-body {
            padding: 10px 0;
        }

        .dropdown-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 20px;
            color: var(--dark);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: var(--transition);
        }

        .dropdown-item:hover {
            background: var(--primary-light);
            color: var(--primary);
            padding-left: 25px;
        }

        .dropdown-item i {
            width: 20px;
            font-size: 16px;
            color: var(--gray);
        }

        .dropdown-item:hover i {
            color: var(--primary);
        }

        .dropdown-divider {
            height: 1px;
            background: rgba(0,0,0,0.05);
            margin: 10px 0;
        }

        /* Modern Sidebar */
        .modern-sidebar {
            position: fixed;
            left: 0;
            top: var(--navbar-height);
            bottom: 0;
            width: var(--sidebar-width);
            background: var(--sidebar-bg);
            z-index: 1099;
            overflow-y: auto;
            padding: 0;
            box-shadow: 5px 0 25px rgba(0,0,0,0.2);
            border-right: 1px solid var(--sidebar-border);
        }

        .sidebar-header {
            padding: 20px 15px;
            border-bottom: 1px solid var(--sidebar-border);
            background: var(--sidebar-header);
            backdrop-filter: blur(10px);
        }

        .sidebar-title {
            font-size: 13px;
            font-weight: 600;
            color: rgba(255,255,255,0.9);
            text-transform: uppercase;
            letter-spacing: 1.2px;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .sidebar-title i {
            color: #4fc3f7;
            font-size: 16px;
        }

        /* Search Module */
        .sidebar-search {
            padding: 15px 20px;
            border-bottom: 1px solid var(--sidebar-border);
            position: relative;
        }

        .search-container {
            position: relative;
            display: flex;
            align-items: center;
        }

        .search-input {
            width: 100%;
            padding: 10px 40px 10px 15px;
            border-radius: 25px;
            border: none;
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
            font-family: 'Poppins', sans-serif;
            font-size: 0.95em;
            transition: all 0.3s ease;
            outline: none;
        }

        .search-input::placeholder {
            color: rgba(255, 255, 255, 0.5);
        }

        .search-input:focus {
            background: rgba(255, 255, 255, 0.15);
            border-color: #4fc3f7;
            box-shadow: 0 0 0 2px rgba(79, 195, 247, 0.2);
        }

        .search-icon {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 1em;
            pointer-events: none;
        }

        .search-icon i {
            color: #1cc88a;
        }

        .search-results {
            position: absolute;
            top: 110%;
            left: 0;
            width: 100%;
            background: #2c2c2c;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.3);
            max-height: 200px;
            overflow-y: auto;
            display: none;
            z-index: 10;
        }

        .search-results.show {
            display: block;
        }

        .search-result-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            color: rgba(255, 255, 255, 0.85);
            text-decoration: none;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            transition: var(--transition);
            cursor: pointer;
            font-size: 13px;
        }

        .search-result-item:hover {
            background: var(--sidebar-hover);
            color: white;
            padding-left: 15px;
        }

        .search-result-item:last-child {
            border-bottom: none;
        }

        .search-result-icon {
            width: 18px;
            text-align: center;
            font-size: 13px;
        }

        .search-result-icon i {
            color: #4fc3f7;
        }

        .search-result-text {
            flex: 1;
            font-weight: 500;
        }

        .search-category {
            padding: 6px 12px;
            font-size: 10px;
            color: rgba(255, 255, 255, 0.5);
            text-transform: uppercase;
            letter-spacing: 0.8px;
            background: rgba(0, 0, 0, 0.2);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .no-results {
            padding: 15px 12px;
            text-align: center;
            color: rgba(255, 255, 255, 0.5);
            font-size: 12px;
        }

        .search-highlight {
            color: #4fc3f7;
            font-weight: 600;
        }

        .search-notification {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #ff4757;
            color: #fff;
            font-size: 0.7em;
            font-weight: bold;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            display: none;
        }

        /* Menu Items */
        .sidebar-menu-container {
            padding: 15px 0;
        }

        .sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .menu-item {
            margin-bottom: 2px;
        }

        .menu-link {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 15px;
            color: rgba(255, 255, 255, 0.85);
            text-decoration: none;
            font-weight: 500;
            font-size: 13px;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
            margin: 0 8px;
            border-radius: 8px;
            position: relative;
            overflow: hidden;
            background: transparent !important;
        }

        .menu-link:hover {
            background: var(--sidebar-hover) !important;
            color: white;
            border-left-color: rgba(79, 195, 247, 0.5);
        }

        .menu-link.active {
            background: var(--sidebar-active) !important;
            color: white;
            font-weight: 600;
            border-left-color: #4fc3f7;
        }

        .menu-icon {
            width: 20px;
            text-align: center;
            font-size: 15px;
        }

        .menu-icon i {
            color: rgba(255, 255, 255, 0.7);
            transition: all 0.3s ease;
        }

        .menu-link:hover .menu-icon i,
        .menu-link.active .menu-icon i {
            color: #4fc3f7;
        }

        .menu-text {
            flex: 1;
            font-weight: 500;
        }

        .menu-arrow {
            font-size: 11px;
        }

        .menu-arrow i {
            color: rgba(255, 255, 255, 0.5);
            transition: var(--transition);
        }

        .menu-item.open .menu-arrow i {
            transform: rotate(180deg);
            color: #4fc3f7;
        }

        /* Submenu */
        .submenu {
            list-style: none;
            padding: 0;
            margin: 0;
            background: rgba(0, 0, 0, 0.2);
            border-left: 3px solid rgba(79, 195, 247, 0.3);
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
            margin-left: 25px;
            border-radius: 0 0 8px 8px;
        }

        .menu-item.open .submenu {
            max-height: 1000px;
        }

        .submenu-item {
            border-top: 1px solid rgba(255, 255, 255, 0.05);
        }

        .submenu-link {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 15px 10px 35px;
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            font-size: 12px;
            font-weight: 500;
            transition: all 0.3s ease;
            background: transparent !important;
        }

        .submenu-link:hover {
            background: rgba(255, 255, 255, 0.05) !important;
            color: white;
            padding-left: 38px;
        }

        .submenu-icon {
            width: 14px;
            text-align: center;
            font-size: 11px;
        }

        .submenu-icon i {
            color: rgba(255, 255, 255, 0.5);
            transition: var(--transition);
        }

        .submenu-link:hover .submenu-icon i {
            color: #4fc3f7;
        }

        /* Main Content Area */
        .main-content-area {
            margin-left: var(--sidebar-width);
            margin-top: var(--navbar-height);
            padding: 30px;
            min-height: calc(100vh - var(--navbar-height));
            background: transparent;
        }

        .content-container {
            max-width: 100%;
        }

        /* Content Area */
        .content-area {
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            border-radius: var(--border-radius);
            padding: 30px;
            border: 1px solid rgba(0,0,0,0.05);
            box-shadow: var(--shadow);
            margin-bottom: 30px;
            min-height: 400px;
        }

        .content-header {
            margin-bottom: 25px;
            padding-bottom: 20px;
            border-bottom: 1px solid rgba(0,0,0,0.05);
        }

        .content-title {
            font-size: 20px;
            font-weight: 700;
            color: var(--dark);
            margin: 0;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .content-title i {
            color: var(--primary);
        }

        /* Footer */
        .modern-footer {
            padding: 25px 0;
            text-align: center;
            color: var(--gray);
            font-size: 13px;
            border-top: 1px solid rgba(0,0,0,0.05);
            margin-top: 30px;
            background: rgba(255,255,255,0.5);
            border-radius: var(--border-radius);
            backdrop-filter: blur(20px);
        }

        .footer-brand {
            color: var(--primary);
            font-weight: 700;
        }

        /* Scroll to Top */
        .scroll-top-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 18px;
            box-shadow: 0 6px 20px rgba(67, 97, 238, 0.4);
            cursor: pointer;
            transition: var(--transition);
            z-index: 100;
            border: none;
            opacity: 0;
            visibility: hidden;
        }

        .scroll-top-btn.show {
            opacity: 1;
            visibility: visible;
        }

        .scroll-top-btn:hover {
            transform: translateY(-5px) scale(1.1);
            box-shadow: 0 8px 25px rgba(67, 97, 238, 0.5);
        }

        .scroll-top-btn i {
            color: white;
        }

        /* Loader de déconnexion */
        .logout-loader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255,255,255,0.95);
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(5px);
        }

        .logout-loader .spinner {
            width: 50px;
            height: 50px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #e74c3c;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Responsive Design */
        @media (max-width: 1200px) {
            .modern-sidebar {
                width: 210px;
            }
            
            .main-content-area {
                margin-left: 210px;
            }
        }

        @media (max-width: 992px) {
            .user-stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .modern-header {
                padding: 0 15px;
            }
            
            .modern-sidebar {
                width: 70px;
                padding: 15px 0;
            }
            
            .main-content-area {
                margin-left: 70px;
                padding: 20px;
            }
            
            .menu-text, 
            .brand-text {
                display: none;
            }
            
            .menu-link {
                justify-content: center;
                padding: 15px;
                border-left: none;
                border-radius: 10px;
                margin: 0 5px;
            }
            
            .menu-icon {
                margin: 0;
                font-size: 18px;
            }
            
            .user-info {
                display: none;
            }
            
            .kpi-badge span:first-child {
                display: none;
            }
            
            .sidebar-search {
                display: none;
            }
        }
        
        /* FontAwesome Fixes pour FA4 */
        .fa {
            font-family: FontAwesome !important;
        }
    </style>

    <!-- ace settings handler -->
    <script src="Public/js/ace-extra.min.js"></script>

    <!--[if lte IE 8]>
    <script src="Public/js/html5shiv.min.js"></script>
    <script src="Public/js/respond.min.js"></script>
    <![endif]-->
</head>

<body class="no-skin">
    <!-- Modern Header -->
    <header class="modern-header">
        <div class="header-container">
            <div class="logo-container">
                <div class="logo">
                    <i class="fa fa-credit-card"></i>
                </div>
                <div class="brand-text">
                    <div class="brand-main">ARRÊTS CAISSES</div>
                    <div class="brand-sub">Système de Gestion</div>
                </div>
            </div>
            
            <div class="header-actions">
                <!-- KPI Badges -->
                
                <?php if(in_array($user['privilege'], explode(',', 'Agence,Caissiere,CaissiereLD,CaissiereSage,OPAgence'))) : ?>
                <div class="kpi-badge agence <?= ($montantVentes >= $avance) ? 'success' : '' ?>" title="Progression">
                    <i class="fa fa-cash-register"></i>
                    <span>Caisses</span>
                    <span class="badge"><?= $CAP; ?>%</span>
                </div>
                <?php endif; ?>
                
                <div class="user-profile-container">
                    <div class="user-profile" id="userProfileToggle">
                        <div class="user-avatar">
                            <?= strtoupper(substr($user['login'], 0, 1)) ?>
                        </div>
                        <div class="user-info">
                            <div class="user-name"><?= $user['login'] ?></div>
                            <div class="user-role"><?= $user['privilege'] ?></div>
                        </div>
                        <i class="fa fa-chevron-down dropdown-arrow"></i>
                    </div>
                    
                    <div class="user-dropdown" id="userDropdown">
                        <div class="dropdown-header">
                            <div class="user-avatar">
                                <?= strtoupper(substr($user['login'], 0, 1)) ?>
                            </div>
                            <div class="user-info">
                                <div class="user-name"><?= $user['login'] ?></div>
                                <div class="user-role"><?= $user['privilege'] ?></div>
                                <div class="status-text" style="margin-top: 5px; font-size: 11px;">
                                    <i class="fa fa-circle" style="color: #2ecc71; font-size: 8px;"></i> En ligne
                                </div>
                            </div>
                        </div>
                        <div class="dropdown-body">
                            <a href="#" class="ResetPassUser dropdown-item">
                                <i class="fa fa-key"></i>Changer Mot de passe
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="#" class="dropdown-item" id="deconnexionBtn">
                                <i class="fa fa-sign-out"></i>Déconnexion
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Modern Sidebar -->
    <aside class="modern-sidebar">
        <!-- Sidebar Header -->
        <div class="sidebar-header">
            <h5 class="sidebar-title">
                <i class="fa fa-cash-register"></i>
                arrêts caisses
            </h5>
            <small style="display: block; margin-top: 5px; font-weight: 400; font-size: 0.75em; color: #cccccc; text-transform: lowercase; letter-spacing: 0.4px;">
                gestion des fonds
            </small>
        </div>

        <!-- Search Module -->
        <div class="sidebar-search" style="padding: 15px 20px;">
            <div class="search-container" style="position: relative; display: flex; align-items: center;">
                <input type="text" 
                       class="search-input" 
                       id="moduleSearch" 
                       placeholder="rechercher un module..." 
                       autocomplete="off"
                       style="width: 100%; padding: 10px 40px 10px 15px; border-radius: 25px; border: none; background: rgba(255,255,255,0.1); color: #fff; font-family: 'Poppins', sans-serif; font-size: 0.95em; transition: all 0.3s ease; outline: none;">
                <div class="search-icon" style="position: absolute; right: 15px; font-size: 1em; pointer-events: none;">
                    <i class="fa fa-search"></i>
                </div>
                <div class="search-notification" id="searchNotification" style="position: absolute; top: -5px; right: -5px; background: #ff4757; color: #fff; font-size: 0.7em; font-weight: bold; border-radius: 50%; width: 18px; height: 18px; display: flex; align-items: center; justify-content: center;">0</div>
                <div class="search-results" id="searchResults" style="position: absolute; top: 110%; left: 0; width: 100%; background: #2c2c2c; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.3); max-height: 200px; overflow-y: auto; display: none; z-index: 10;"></div>
            </div>
        </div>

        <!-- Navigation Menu -->
        <div class="sidebar-menu-container">
            <ul class="sidebar-menu" id="sidebarMenu">
                <!-- Dashboard -->
                <li class="menu-item" data-search="DASHBOARD ACCUEIL TABLEAU DE BORD">
                    <a href="<?= App::url('home.accuiel') ?>" class="menu-link">
                        <div class="menu-icon">
                            <i class="fa fa-tachometer"></i>
                        </div>
                        <div class="menu-text">DASHBOARD</div>
                    </a>
                </li>

                <!-- Administration -->
                <?php
                    $privileges = 'Administration,SuperAdministration';
                    if(in_array($user['privilege'] ,explode(',',$privileges))) {
                ?>
                <li class="menu-item" data-search="ADMINISTRATION GESTION UTILISATEURS AGENCES">
                    <a href="#" class="menu-link dropdown-toggle">
                        <div class="menu-icon">
                            <i class="fa fa-cogs"></i>
                        </div>
                        <div class="menu-text">ADMINISTRATION</div>
                        <div class="menu-arrow">
                            <i class="fa fa-chevron-down"></i>
                        </div>
                    </a>
                    <ul class="submenu">
                        <li class="submenu-item" data-search="Gestion Utilisateurs USERS">
                            <a href="<?= App::url('User.index') ?>" class="submenu-link">
                                <div class="submenu-icon">
                                    <i class="fa fa-user"></i>
                                </div>
                                <span>Gestion Utilisateurs</span>
                            </a>
                        </li>
                        <li class="submenu-item" data-search="Gestion des agences AGENCES">
                            <a href="<?= App::url('agence.index') ?>" class="submenu-link">
                                <div class="submenu-icon">
                                    <i class="fa fa-building"></i>
                                </div>
                                <span>Gestion des agences</span>
                            </a>
                        </li>
                    </ul>
                </li>
                <?php
                    }
                ?>

                <!-- MODULE EFFET DE COMMERCE - Visible par TOUS les utilisateurs -->
                <li class="menu-item" data-search="EFFET DE COMMERCE BILLET ORDRE LETTRE CHANGE">
                    <a href="<?= App::url('effetCommerce.index') ?>" class="menu-link">
                        <div class="menu-icon">
                            <i class="fa fa-bank"></i>
                        </div>
                        <div class="menu-text">EFFET DE COMMERCE</div>
                    </a>
                </li>

                <!-- Confirmation Chèque -->
                <?php
                    $privilegesConfirmation = 'Administration,SuperAdministration,Agence,AgenceSage,Comptabilite,Caissiere,CaissiereLD,CaissiereSage,OPAgence';
                    if(in_array($user['privilege'], explode(',', $privilegesConfirmation))) {
                ?>
                <li class="menu-item" data-search="CONFIRMATION CHÈQUE CHEQUE VALIDATION">
                    <a href="<?= App::url('confirmationCheque.index') ?>" class="menu-link">
                        <div class="menu-icon">
                            <i class="fa fa-money"></i>
                        </div>
                        <div class="menu-text">CONFIRMATION CHÈQUE</div>
                    </a>
                </li>
                <?php
                    }
                ?>

                <!-- Ramassage Fond -->
                <?php
                    $privilegesRamassage = 'Administration,SuperAdministration,Agence,AgenceSage,Comptabilite,ControleInterne,Controleur,Caissiere,OPAgence';
                    if(in_array($user['privilege'], explode(',', $privilegesRamassage))) {
                ?>
                <li class="menu-item" data-search="RAMASSAGE FOND FONDS TRANSPORT">
                    <a href="<?= App::url('ramassage.index') ?>" class="menu-link">
                        <div class="menu-icon">
                            <i class="fa fa-truck"></i>
                        </div>
                        <div class="menu-text">RAMASSAGE FOND</div>
                    </a>
                </li>
                <?php
                    }
                ?>

                <!-- Arrêt Caissière -->
                <?php
                    $privileges = 'Administration,Caissiere,ControleInterne,Controleur,Comptabilite,SuperAdministration';
                    if(in_array($user['privilege'] ,explode(',',$privileges))) {
                ?>
                <li class="menu-item" data-search="ARRÊT CAISSIÈRE CAISSE ARRET">
                    <a href="<?= App::url('arretsCaisses.index') ?>" class="menu-link">
                        <div class="menu-icon">
                            <i class="fa fa-briefcase"></i>
                        </div>
                        <div class="menu-text">ARRÊT CAISSIÈRE</div>
                    </a>
                </li>
                <?php
                    }
                ?>

                <!-- Arrêt Caissière LD -->
                <?php
                    $privileges = 'Administration,CaissiereLD,Controleur,Comptabilite,SuperAdministration';
                    if(in_array($user['privilege'] ,explode(',',$privileges))) {
                ?>
                <li class="menu-item" data-search="ARRÊT CAISSIÈRE LD">
                    <a href="<?= App::url('arretsCaissesLD.index') ?>" class="menu-link">
                        <div class="menu-icon">
                            <i class="fa fa-briefcase"></i>
                        </div>
                        <div class="menu-text">ARRÊT CAISSIÈRE LD</div>
                    </a>
                </li>
                <?php
                    }
                ?>

                <!-- Arrêt Chef -->
                <?php
                    $privileges = 'Administration,Agence,ControleInterne,Controleur,SuperAdministration';
                    if(in_array($user['privilege'] ,explode(',',$privileges))) {
                ?>
                <li class="menu-item" data-search="ARRÊT CHEF DOUANIER">
                    <a href="<?= App::url('arretsDouanier.index') ?>" class="menu-link">
                        <div class="menu-icon">
                            <i class="fa fa-briefcase"></i>
                        </div>
                        <div class="menu-text">ARRÊT CHEF</div>
                    </a>
                </li>
                <?php
                    }
                ?>

                <!-- Arrêt Compta -->
                <?php
                    $privileges = 'Administration,Comptabilite,ControleInterne,SuperAdministration';
                    if(in_array($user['privilege'] ,explode(',',$privileges))) {
                ?>
                <li class="menu-item" data-search="ARRÊT COMPTA COMPTABILITE">
                    <a href="<?= App::url('arretsDouanier.interfaceCpta') ?>" class="menu-link">
                        <div class="menu-icon">
                            <i class="fa fa-suitcase"></i>
                        </div>
                        <div class="menu-text">ARRÊT COMPTA</div>
                    </a>
                </li>
                <?php
                    }
                ?>

                <!-- Arrêt Contrôle Interne -->
                <?php
                    $privileges = 'Administration,ControleInterne,Controleur,SuperAdministration';
                    if(in_array($user['privilege'] ,explode(',',$privileges))) {
                ?>
                <li class="menu-item" data-search="ARRÊT CONTRÔLE INTERNE">
                    <a href="<?= App::url('arretsDouanier.interfaceGestion') ?>" class="menu-link">
                        <div class="menu-icon">
                            <i class="fa fa-plus-square"></i>
                        </div>
                        <div class="menu-text">ARRÊT CONTRÔLE INTERNE</div>
                    </a>
                </li>
                <?php
                    }
                ?>

                <!-- Arrêt Caissière Sage -->
                <?php
                    $privileges = 'Administration,CaissiereSage,ControleInterne,Controleur,Comptabilite,SuperAdministration';
                    if(in_array($user['privilege'] ,explode(',',$privileges))) {
                ?>
                <li class="menu-item" data-search="ARRÊT CAISSIÈRE SAGE">
                    <a href="<?= App::url('arretsCaisses.arretSage') ?>" class="menu-link">
                        <div class="menu-icon">
                            <i class="fa fa-briefcase"></i>
                        </div>
                        <div class="menu-text">ARRÊT CAISSIÈRE SAGE</div>
                    </a>
                </li>
                <?php
                    }
                ?>

                <!-- Arrêt Chef Sage -->
                <?php
                    $privileges = 'Administration,AgenceSage,ControleInterne,Controleur,SuperAdministration';
                    if(in_array($user['privilege'] ,explode(',',$privileges))) {
                ?>
                <li class="menu-item" data-search="ARRÊT CHEF SAGE">
                    <a href="<?= App::url('arretsDouanierSage.index') ?>" class="menu-link">
                        <div class="menu-icon">
                            <i class="fa fa-briefcase"></i>
                        </div>
                        <div class="menu-text">ARRÊT CHEF SAGE</div>
                    </a>
                </li>
                <?php
                    }
                ?>

                <!-- Arrêt Compta Sage -->
                <?php
                    $privileges = 'Administration,Comptabilite,ControleInterne,SuperAdministration';
                    if(in_array($user['privilege'] ,explode(',',$privileges))) {
                ?>
                <li class="menu-item" data-search="ARRÊT COMPTA SAGE">
                    <a href="<?= App::url('arretsDouanierSage.interfaceCpta') ?>" class="menu-link">
                        <div class="menu-icon">
                            <i class="fa fa-suitcase"></i>
                        </div>
                        <div class="menu-text">ARRÊT COMPTA SAGE</div>
                    </a>
                </li>
                <?php
                    }
                ?>

                <!-- Arrêt Contrôle Interne Sage -->
                <?php
                    $privileges = 'Administration,ControleInterne,Controleur,SuperAdministration';
                    if(in_array($user['privilege'] ,explode(',',$privileges))) {
                ?>
                <li class="menu-item" data-search="ARRÊT CONTRÔLE INTERNE SAGE">
                    <a href="<?= App::url('arretsDouanierSage.interfaceGestionSage') ?>" class="menu-link">
                        <div class="menu-icon">
                            <i class="fa fa-plus-square"></i>
                        </div>
                        <div class="menu-text">ARRÊT CONTRÔLE INTERNE SAGE</div>
                    </a>
                </li>
                <?php
                    }
                ?>
            </ul>
        </div>
    </aside>

    <!-- Main Content Area -->
    <main class="main-content-area">
        <div class="content-container">
            <!-- Main Content -->
            <div class="content-area">
                <div class="content-header">
                    <h2 class="content-title">
                        <?php 
                        $currentUrl = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
                        $title = "ARRÊTS CAISSES";
                        $icon = "fa fa-cash-register";
                        
                        if (strpos($currentUrl, 'effetCommerce') !== false) {
                            $title = "Effet de Commerce";
                            $icon = "fa fa-bank";
                        } elseif (strpos($currentUrl, 'confirmationCheque') !== false) {
                            $title = "Confirmation Chèque";
                            $icon = "fa fa-money";
                        } elseif (strpos($currentUrl, 'ramassage') !== false) {
                            $title = "Ramassage Fond";
                            $icon = "fa fa-truck";
                        } elseif (strpos($currentUrl, 'arretsCaisses') !== false && strpos($currentUrl, 'LD') === false && strpos($currentUrl, 'Sage') === false) {
                            $title = "Arrêt Caissière";
                            $icon = "fa fa-briefcase";
                        } elseif (strpos($currentUrl, 'arretsCaissesLD') !== false) {
                            $title = "Arrêt Caissière LD";
                            $icon = "fa fa-briefcase";
                        } elseif (strpos($currentUrl, 'arretsDouanier') !== false && strpos($currentUrl, 'Sage') === false) {
                            $title = "Arrêt Chef";
                            $icon = "fa fa-briefcase";
                        } elseif (strpos($currentUrl, 'interfaceCpta') !== false && strpos($currentUrl, 'Sage') === false) {
                            $title = "Arrêt Compta";
                            $icon = "fa fa-suitcase";
                        } elseif (strpos($currentUrl, 'interfaceGestion') !== false && strpos($currentUrl, 'Sage') === false) {
                            $title = "Arrêt Contrôle Interne";
                            $icon = "fa fa-plus-square";
                        } elseif (strpos($currentUrl, 'arretSage') !== false) {
                            $title = "Arrêt Caissière Sage";
                            $icon = "fa fa-briefcase";
                        } elseif (strpos($currentUrl, 'arretsDouanierSage') !== false && strpos($currentUrl, 'interfaceCpta') === false && strpos($currentUrl, 'interfaceGestionSage') === false) {
                            $title = "Arrêt Chef Sage";
                            $icon = "fa fa-briefcase";
                        } elseif (strpos($currentUrl, 'interfaceCpta') !== false && strpos($currentUrl, 'Sage') !== false) {
                            $title = "Arrêt Compta Sage";
                            $icon = "fa fa-suitcase";
                        } elseif (strpos($currentUrl, 'interfaceGestionSage') !== false) {
                            $title = "Arrêt Contrôle Interne Sage";
                            $icon = "fa fa-plus-square";
                        }
                        ?>
                        <i class="<?= $icon ?>"></i>
                        <?= $title ?>
                    </h2>
                </div>
                <?= $content ?>
            </div>
            
            <!-- Footer -->
            <footer class="modern-footer">
                <div class="footer-content">
                    <span class="footer-brand">ARRÊTS CAISSES</span> 
                    &copy; <?= date('Y') ?> - SOREPCO
                </div>
            </footer>
        </div>
    </main>

    <!-- Scroll to Top Button -->
    <button class="scroll-top-btn" id="btn-scroll-up">
        <i class="fa fa-chevron-up"></i>
    </button>

    <!-- LOGOUT MODAL - STYLES INLINE DIRECTEMENT DANS LA BALISE -->
    <div id="customLogoutModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0,0,0,0.5); z-index: 99999; align-items: center; justify-content: center;">
        <div style="max-width: 400px; width: 90%; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 15px 40px rgba(0,0,0,0.2);">
            <div style="background: #e74c3c; padding: 15px 20px; display: flex; align-items: center; justify-content: space-between;">
                <h3 style="color: white; font-weight: 600; font-size: 18px; margin: 0; display: flex; align-items: center; gap: 10px;">
                    <i class="fa fa-sign-out" style="color: white; font-size: 20px;"></i>
                    Déconnexion
                </h3>
                <button type="button" id="customModalClose" style="background: none; border: none; color: white; font-size: 28px; cursor: pointer; padding: 0; line-height: 1; opacity: 0.8;">&times;</button>
            </div>
            <div style="padding: 30px 25px; text-align: center;">
                <div style="width: 70px; height: 70px; background: #fee7e7; border-radius: 8px; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
                    <i class="fa fa-sign-out" style="font-size: 35px; color: #e74c3c;"></i>
                </div>
                <h4 style="font-size: 20px; font-weight: 600; color: #2c3e50; margin-bottom: 8px;">Quitter l'application ?</h4>
                <p style="color: #7f8c8d; font-size: 14px; margin-bottom: 25px;">
                    Vous allez être redirigé vers la page de connexion
                </p>
                <div style="display: flex; gap: 12px; justify-content: center;">
                    <button type="button" id="customModalCancel" style="padding: 10px 25px; background: #95a5a6; border: none; color: white; font-weight: 500; border-radius: 6px; min-width: 120px; cursor: pointer; transition: all 0.2s;">Annuler</button>
                    <button type="button" id="customModalLogout" style="padding: 10px 25px; background: #e74c3c; border: none; color: white; font-weight: 500; border-radius: 6px; min-width: 120px; cursor: pointer; transition: all 0.2s;">Se déconnecter</button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL DE CHANGEMENT DE MOT DE PASSE MODERNISÉ -->
    <div id="modalResetPassUser" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content" style="border: none; border-radius: 12px; overflow: hidden; box-shadow: 0 20px 60px rgba(0,0,0,0.3);">
                <div class="modal-header" style="background: linear-gradient(135deg, #4361ee 0%, #3a0ca3 100%); border: none; padding: 20px 25px;">
                    <h5 class="modal-title" style="color: white; font-weight: 600; font-size: 18px; display: flex; align-items: center; gap: 10px; margin: 0;">
                        <i class="fa fa-key" style="color: white; font-size: 20px;"></i>
                        Réinitialiser le mot de passe
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" style="color: white; opacity: 0.8; font-size: 28px; font-weight: 300; text-shadow: none; margin: -5px 0 0 0;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body" style="padding: 30px 25px;">
                    <form action="<?= App::url('ajax.home.resetPass') ?>" method="POST" id="form-ResetPassUser">
                        <div style="margin-bottom: 20px;">
                            <label style="font-weight: 500; color: #2c3e50; margin-bottom: 5px; display: block;">Ancien mot de passe</label>
                            <div style="position: relative;">
                                <input type="password" class="form-control" id="oldPasswordUser" name="oldPasswordUser1" placeholder="Saisissez votre ancien mot de passe" style="height: 45px; border-radius: 8px; border: 1px solid #e0e0e0; padding-left: 45px; font-size: 14px;">
                                <i class="fa fa-lock" style="position: absolute; left: 15px; top: 15px; color: #95a5a6; font-size: 16px;"></i>
                            </div>
                        </div>
                        
                        <div style="margin-bottom: 20px;">
                            <label style="font-weight: 500; color: #2c3e50; margin-bottom: 5px; display: block;">Nouveau mot de passe</label>
                            <div style="position: relative;">
                                <input type="password" class="form-control" id="newPasswordUser" name="newPassword1" placeholder="Saisissez votre nouveau mot de passe" style="height: 45px; border-radius: 8px; border: 1px solid #e0e0e0; padding-left: 45px; font-size: 14px;">
                                <i class="fa fa-lock" style="position: absolute; left: 15px; top: 15px; color: #95a5a6; font-size: 16px;"></i>
                            </div>
                        </div>

                        <div style="margin-bottom: 25px;">
                            <label style="font-weight: 500; color: #2c3e50; margin-bottom: 5px; display: block;">Confirmer le mot de passe</label>
                            <div style="position: relative;">
                                <input type="password" class="form-control" id="confirmNewPassword" name="confirmNewPassword1" placeholder="Confirmez votre nouveau mot de passe" style="height: 45px; border-radius: 8px; border: 1px solid #e0e0e0; padding-left: 45px; font-size: 14px;">
                                <i class="fa fa-repeat" style="position: absolute; left: 15px; top: 15px; color: #95a5a6; font-size: 16px;"></i>
                            </div>
                        </div>

                        <div class="clearfix resetPassUser" style="display: flex; gap: 15px; justify-content: flex-end;">
                            <button type="button" class="btn btn-sm" data-dismiss="modal" style="padding: 10px 25px; background: #f8f9fa; border: 1px solid #e0e0e0; color: #7f8c8d; font-weight: 500; border-radius: 8px;">Annuler</button>
                            <button type="submit" class="btn btn-sm" style="padding: 10px 25px; background: linear-gradient(135deg, #4361ee 0%, #3a0ca3 100%); border: none; color: white; font-weight: 500; border-radius: 8px;">
                                <i class="fa fa-arrow-right icon-on-right" style="margin-left: 5px;"></i>
                                Réinitialiser
                            </button>
                        </div>
                        
                        <div class="clearfix hidden loaderPassReset" style="display: none;">
                            <center>
                                <h2 class="header smaller lighter grey">
                                    <i class="fa fa-spinner fa-spin green bigger-125"></i>
                                </h2>
                            </center>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript Libraries (tous en local) -->
    <script src="Public/js/jquery-2.1.4.min.js"></script>
    <script src="Public/js/bootstrap.min.js"></script>
    <script src="Public/js/jquery.dataTables.min.js"></script>
    <script src="Public/js/jquery.dataTables.bootstrap.min.js"></script>
    <script src="Public/js/chosen.jquery.min.js"></script>
    <script src="Public/js/dataTables.buttons.min.js"></script>
    <script src="Public/js/buttons.html5.min.js"></script>
    <script src="Public/js/buttons.print.min.js"></script>
    <script src="Public/js/buttons.colVis.min.js"></script> 
    <script src="Public/js/Programme.js"></script>
    <script src="Public/js/Charges.js"></script>
    <script src="Public/js/jquery.maskedinput.min.js"></script>
    <script src="Public/js/ace-elements.min.js"></script>
    <script src="Public/js/ace.min.js"></script>
    <script src="Public/js/icheck.min.js"></script>
    <script src="Public/js/jquery-typeahead.js"></script>
    <script src="Public/js/jquery.easyWizard.js"></script>
    <script src="Public/js/form-wizard.js"></script>

    <!-- Modern JavaScript -->
    <script>
    $(document).ready(function() {
        // FIXED: User Profile Dropdown Management
        let dropdownTimeout;
        const userProfile = $('#userProfileToggle');
        const userDropdown = $('#userDropdown');
        
        userProfile.click(function(e) {
            e.stopPropagation();
            clearTimeout(dropdownTimeout);
            userDropdown.addClass('show');
        });
        
        $(document).click(function(e) {
            if (!$(e.target).closest('.user-profile-container').length) {
                dropdownTimeout = setTimeout(function() {
                    userDropdown.removeClass('show');
                }, 100);
            }
        });
        
        userDropdown.hover(
            function() { clearTimeout(dropdownTimeout); },
            function() {
                dropdownTimeout = setTimeout(function() {
                    userDropdown.removeClass('show');
                }, 300);
            }
        );
        
        userProfile.hover(
            function() {
                clearTimeout(dropdownTimeout);
                userDropdown.addClass('show');
            },
            function() {
                if (!userDropdown.is(':hover')) {
                    dropdownTimeout = setTimeout(function() {
                        userDropdown.removeClass('show');
                    }, 300);
                }
            }
        );
        
        // Scroll to top
        $(window).scroll(function() {
            if ($(this).scrollTop() > 300) {
                $('#btn-scroll-up').addClass('show');
            } else {
                $('#btn-scroll-up').removeClass('show');
            }
        });
        
        $('#btn-scroll-up').click(function(e) {
            e.preventDefault();
            $('html, body').animate({scrollTop: 0}, 300);
        });
        
        // Modal triggers
        $('.ResetPassUser').click(function(e) {
            e.preventDefault();
            userDropdown.removeClass('show');
            $('#modalResetPassUser').modal('show');
        });
        
        // Gestion du modal personnalisé
        const $customModal = $('#customLogoutModal');
        
        $('#deconnexionBtn').click(function(e) {
            e.preventDefault();
            userDropdown.removeClass('show');
            $customModal.css('display', 'flex');
        });
        
        $('#customModalClose, #customModalCancel').click(function() {
            $customModal.css('display', 'none');
        });
        
        $('#customModalLogout').click(function(e) {
            e.preventDefault();
            $customModal.css('display', 'none');
            
            $('body').append('<div class="logout-loader"><div class="spinner"></div></div>');
            
            $.ajax({
                url: 'index.php?p=home.logout',
                type: 'POST',
                data: { logout: true },
                success: function() {
                    setTimeout(function() {
                        window.location.href = 'index.php?p=home.index';
                    }, 1000);
                },
                error: function() {
                    setTimeout(function() {
                        window.location.href = 'index.php?p=home.index';
                    }, 1000);
                }
            });
        });
        
        // Fermer en cliquant sur l'overlay
        $customModal.click(function(e) {
            if (e.target === this) {
                $customModal.css('display', 'none');
            }
        });
        
        // Form validation
        $('#form-ResetPassUser').submit(function(e) {
            const newPass = $('#newPasswordUser').val();
            const confirmPass = $('#confirmNewPassword').val();
            
            if (newPass !== confirmPass) {
                alert('Les mots de passe ne correspondent pas !');
                e.preventDefault();
                return false;
            }
            
            if (newPass.length < 6) {
                alert('Le mot de passe doit contenir au moins 6 caractères !');
                e.preventDefault();
                return false;
            }
            
            return true;
        });
        
        // Menu active state
        const currentUrl = window.location.pathname + window.location.search;
        $('.menu-link[href]').each(function() {
            if (this.href.includes(currentUrl.split('?')[0])) {
                $(this).addClass('active');
                $(this).closest('.menu-item').addClass('open');
            }
        });
        
        // Menu dropdown toggle
        $('.menu-link.dropdown-toggle').click(function(e) {
            e.preventDefault();
            const menuItem = $(this).closest('.menu-item');
            menuItem.toggleClass('open');
            $('.menu-item').not(menuItem).removeClass('open');
        });
        
        // MODULE SEARCH FUNCTIONALITY
        const moduleSearch = $('#moduleSearch');
        const searchResults = $('#searchResults');
        const searchNotification = $('#searchNotification');
        
        const modules = [];
        
        $('.menu-item').each(function() {
            const $item = $(this);
            const $link = $item.find('.menu-link[href]').first();
            const $text = $item.find('.menu-text').first();
            const $icon = $item.find('.menu-icon i').first();
            
            if ($link.length && $text.length) {
                const href = $link.attr('href');
                const text = $text.text().trim();
                const searchData = $item.data('search') || text;
                const iconClass = $icon.attr('class') || 'fa fa-file';
                
                modules.push({
                    type: 'main',
                    text: text,
                    search: searchData.toLowerCase(),
                    href: href,
                    icon: iconClass,
                    element: $item
                });
            }
        });
        
        $('.submenu-item').each(function() {
            const $item = $(this);
            const $link = $item.find('.submenu-link[href]').first();
            const $text = $item.find('span').first();
            const $icon = $item.find('.submenu-icon i').first();
            
            if ($link.length && $text.length) {
                const href = $link.attr('href');
                const text = $text.text().trim();
                const searchData = $item.data('search') || text;
                const iconClass = $icon.attr('class') || 'fa fa-file';
                
                modules.push({
                    type: 'sub',
                    text: text,
                    search: searchData.toLowerCase(),
                    href: href,
                    icon: iconClass,
                    element: $item
                });
            }
        });
        
        let searchTimeout;
        moduleSearch.on('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(performSearch, 300);
        });
        
        moduleSearch.on('keyup', function(e) {
            if (e.key === 'Escape') {
                hideResults();
                moduleSearch.val('');
            }
        });
        
        $(document).click(function(e) {
            if (!$(e.target).closest('.sidebar-search').length) {
                hideResults();
            }
        });
        
        moduleSearch.on('keydown', function(e) {
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                const firstResult = searchResults.find('.search-result-item').first();
                if (firstResult.length) {
                    firstResult.focus();
                }
            } else if (e.key === 'Enter' && moduleSearch.val().trim()) {
                e.preventDefault();
                const firstResult = searchResults.find('.search-result-item').first();
                if (firstResult.length) {
                    firstResult.click();
                }
            }
        });
        
        function performSearch() {
            const query = moduleSearch.val().trim().toLowerCase();
            
            if (!query) {
                hideResults();
                return;
            }
            
            const results = [];
            
            modules.forEach(module => {
                if (module.search.includes(query) || module.text.toLowerCase().includes(query)) {
                    results.push(module);
                }
            });
            
            results.sort((a, b) => {
                const aStartsWith = a.text.toLowerCase().startsWith(query);
                const bStartsWith = b.text.toLowerCase().startsWith(query);
                
                if (aStartsWith && !bStartsWith) return -1;
                if (!aStartsWith && bStartsWith) return 1;
                
                const aSearchMatch = a.search.includes(query);
                const bSearchMatch = b.search.includes(query);
                
                if (aSearchMatch && !bSearchMatch) return -1;
                if (!aSearchMatch && bSearchMatch) return 1;
                
                return a.text.length - b.text.length;
            });
            
            displayResults(results, query);
        }
        
        function displayResults(results, query) {
            if (results.length === 0) {
                searchResults.html('<div class="no-results">Aucun module trouvé pour "' + query + '"</div>');
                searchResults.addClass('show');
                searchNotification.text('0').hide();
                return;
            }
            
            let html = '';
            let mainMenuResults = [];
            let subMenuResults = [];
            
            results.forEach(result => {
                if (result.type === 'main') {
                    mainMenuResults.push(result);
                } else {
                    subMenuResults.push(result);
                }
            });
            
            if (mainMenuResults.length > 0) {
                html += '<div class="search-category">Menu Principal</div>';
                mainMenuResults.forEach(result => {
                    const highlightedText = highlightText(result.text, query);
                    html += `
                        <a href="${result.href}" class="search-result-item">
                            <div class="search-result-icon">
                                <i class="${result.icon}"></i>
                            </div>
                            <div class="search-result-text">${highlightedText}</div>
                        </a>
                    `;
                });
            }
            
            if (subMenuResults.length > 0) {
                html += '<div class="search-category">Sous-menus</div>';
                subMenuResults.forEach(result => {
                    const highlightedText = highlightText(result.text, query);
                    html += `
                        <a href="${result.href}" class="search-result-item">
                            <div class="search-result-icon">
                                <i class="${result.icon}"></i>
                            </div>
                            <div class="search-result-text">${highlightedText}</div>
                        </a>
                    `;
                });
            }
            
            searchResults.html(html);
            searchResults.addClass('show');
            searchNotification.text(results.length).show();
            
            searchResults.find('.search-result-item').click(function(e) {
                e.preventDefault();
                const href = $(this).attr('href');
                if (href) {
                    window.location.href = href;
                }
            });
            
            searchResults.find('.search-result-item').on('keydown', function(e) {
                const $current = $(this);
                const $items = searchResults.find('.search-result-item');
                const currentIndex = $items.index($current);
                
                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    const $next = $items.eq(currentIndex + 1);
                    if ($next.length) {
                        $next.focus();
                    }
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    const $prev = $items.eq(currentIndex - 1);
                    if ($prev.length) {
                        $prev.focus();
                    } else {
                        moduleSearch.focus();
                    }
                } else if (e.key === 'Enter') {
                    e.preventDefault();
                    $current.click();
                } else if (e.key === 'Escape') {
                    hideResults();
                    moduleSearch.focus();
                }
            });
        }
        
        function highlightText(text, query) {
            if (!query) return text;
            const regex = new RegExp(`(${query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi');
            return text.replace(regex, '<span class="search-highlight">$1</span>');
        }
        
        function hideResults() {
            searchResults.removeClass('show');
            searchNotification.hide();
        }
        
        $(document).keydown(function(e) {
            if ((e.ctrlKey || e.metaKey) && e.key === 'f') {
                e.preventDefault();
                moduleSearch.focus();
                moduleSearch.select();
            }
        });
        
        moduleSearch.on('focus', function() {
            $(this).attr('placeholder', 'Tapez pour rechercher un module...');
        });
        
        moduleSearch.on('blur', function() {
            $(this).attr('placeholder', 'Rechercher un module...');
        });
        
        $(document).keydown(function(e) {
            if (e.key === 'Escape' && moduleSearch.is(':focus') && moduleSearch.val()) {
                moduleSearch.val('');
                hideResults();
            }
        });
    });
    </script>

    <!-- Scripts originaux conservés -->
    <script type="text/javascript">
    jQuery(function($) {
        $.mask.definitions['~']='[+-]';
        $('.input-mask-date').mask('99/99/9999');
        $('.input-mask-phone').mask('699-999-999');
        
        var substringMatcher = function(strs) {
            return function findMatches(q, cb) {
                var matches = [];
                var substrRegex = new RegExp(q, 'i');
                
                $.each(strs, function(i, str) {
                    if (substrRegex.test(str)) {
                        matches.push({ value: str });
                    }
                });

                cb(matches);
            }
        }

        $('input.typeahead').typeahead({
            hint: true,
            highlight: true,
            minLength: 1
        }, {
            name: 'states',
            displayKey: 'value',
            source: substringMatcher(<?= json_encode($articles) ?>),
            limit: 10
        });
        
        $('input.typeahead1').typeahead({
            hint: true,
            highlight: true,
            minLength: 1
        }, {
            name: 'states',
            displayKey: 'value',
            source: substringMatcher(<?= json_encode($arts) ?>),
            limit: 10
        });
        
        $( "#codeArtVeil90" ).on('change', function(e) {
            e.preventDefault();
            var articles1 = <?= json_encode($arts) ?> 
            var codeArticle = $('#codeArtVeil90').val();
            
            for (var  i = 0; i < articles1.length; i++) {
              var codeArticle1 = articles1[i].split('_'); 
                if( codeArticle1[0] == codeArticle){
                    document.getElementById("familleArtVeil90").value = codeArticle1[1];
                    document.getElementById("designationArtVeil90").value = codeArticle1[2];
                }
                if( codeArticle1[3] == codeArticle){
                    document.getElementById("familleArtVeil90").value = codeArticle1[1];
                    document.getElementById("designationArtVeil90").value = codeArticle1[2];
                }
            }
        });
        
        if(!ace.vars['touch']) {
            $('.chosen-select').chosen({allow_single_deselect:true});
            
            $(window)
            .off('resize.chosen')
            .on('resize.chosen', function() {
                $('.chosen-select').each(function() {
                     var $this = $(this);
                     $this.next().css({'width': $this.width()});
                })
            }).trigger('resize.chosen');
            
            $(document).on('settings.ace.chosen', function(e, event_name, event_val) {
                if(event_name != 'sidebar_collapsed') return;
                $('.chosen-select').each(function() {
                     var $this = $(this);
                     $this.next().css({'width': $this.width()});
                })
            });
        }
    });
    </script>
</body>
</html>