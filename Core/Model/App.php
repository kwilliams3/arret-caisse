<?php

namespace Core\Model;

use Core\Auth\DBAuth;

class App{

    private static $auth;
    private static $scripts = [];
    private static $styles = [];
    const DB_SERVERNAME = 'PC\SQLEXPRESS1';
    const DB_USER = '';
    const DB_NAME = 'GMAO';
    const DB_PASS = '';

    private static $database;

    public static function getDB (){
        if( self::$database === null){
            self::$database = new Database(self::DB_SERVERNAME,self::DB_USER,self::DB_NAME,self::DB_PASS);
        }
        return self::$database;
    }

    public static function getDBAuth(){
        if(self::$auth === null){
            self::$auth = new DBAuth(Session::getInstance());
        }
        return self::$auth;
    }

    public static function deleteSession(){
        unset($_SESSION);
    }

    public static function url($url){
        return 'index.php?p='.$url;
    }

    public static function interdit(){
        header('Location:interdit.php');
    }

    public static function redirect($page){
        header("location: $page");
        exit();
    }

    public static function error(){
        header('HTTP/1.0 403 FORBIDDEN');
        die('Acces interdit');
    }

    public static function getRoute(){
        if(isset($_GET['p'])){
            $p = $_GET['p'];
        }else{
            $p = 'Home.index';
        }

        $page = explode('.', $p);
        
        // Vérifier si le tableau a assez d'éléments
        if (count($page) < 2) {
            // Si pas assez d'éléments, utiliser des valeurs par défaut
            $page[0] = isset($page[0]) ? $page[0] : 'Home';
            $page[1] = 'index';
        }
        
        // Route pour ConfirmationCheque (normale et ajax)
        if ($page[0] == 'confirmationCheque' || ($page[0] == 'ajax' && isset($page[1]) && $page[1] == 'confirmationCheque')) {
            $controller = 'Core\Controller\ConfirmationChequeController';
            
            if ($page[0] == 'ajax') {
                $action = isset($page[2]) ? $page[2] : 'index'; // ajax.confirmationCheque.ajoutCheque
            } else {
                $action = isset($page[1]) ? $page[1] : 'index'; // confirmationCheque.index
            }
            
            if (method_exists($controller, $action)) {
                $controller = new $controller();
                $controller->$action();
            } else {
                self::error();
            }
            return;
        }
        
        // Route pour Ramassage (normale et ajax)
        if ($page[0] == 'ramassage' || ($page[0] == 'ajax' && isset($page[1]) && $page[1] == 'ramassage')) {
            $controller = 'Core\Controller\RamassageController';
            
            if ($page[0] == 'ajax') {
                $action = isset($page[2]) ? $page[2] : 'index'; // ajax.ramassage.ajoutRamassage
            } else {
                $action = isset($page[1]) ? $page[1] : 'index'; // ramassage.index
            }
            
            if (method_exists($controller, $action)) {
                $controller = new $controller();
                $controller->$action();
            } else {
                self::error();
            }
            return;
        }
        
        // Route générique pour les autres contrôleurs
        if (isset($page[0]) && $page[0] == 'ajax'){
            // Route AJAX: ajax.controller.action
            if (isset($page[1])) {
                $controller = 'Core\Controller\\'.ucfirst($page[1]).'Controller';
                $action = isset($page[2]) ? $page[2] : 'index';
                
                if (class_exists($controller) && method_exists($controller, $action)) {
                    $controller = new $controller();
                    $controller->$action();
                } else {
                    self::error();
                }
            } else {
                self::error();
            }
        } else {
            // Route normale: controller.action
            if (isset($page[0]) && isset($page[1])) {
                $controller = 'Core\Controller\\'.ucfirst($page[0]).'Controller';
                $action = $page[1];
                
                if (class_exists($controller) && method_exists($controller, $action)) {
                    $controller = new $controller();
                    $controller->$action();
                } else {
                    // Si le contrôleur n'existe pas, vérifier si c'est un fichier de vue direct
                    $path = 'Views/'.ucfirst($page[0]).'/'.($page[1]).'.php';
                    if (file_exists($path)) {
                        // Inclure la vue directement
                        require $path;
                    } else {
                        self::error();
                    }
                }
            } else {
                // Route par défaut
                $controller = 'Core\Controller\HomeController';
                $action = 'index';
                
                if (class_exists($controller) && method_exists($controller, $action)) {
                    $controller = new $controller();
                    $controller->$action();
                } else {
                    self::error();
                }
            }
        }
    }

    public static function addScript($script, $isSource = false, $isDefault = false){
        if ($isSource){
            if($isDefault){
                if (!isset(self::$scripts['default'])) {
                    self::$scripts['default'] = [];
                }
                self::$scripts['default'][] = '<script src="'.$script.'?token='.date('YmdH').'" type="text/javascript"></script>'."\r\n";
            }else{
                if (!isset(self::$scripts['source'])) {
                    self::$scripts['source'] = [];
                }
                self::$scripts['source'][] = '<script src="'.$script.'?token='.date('YmdH').'" type="text/javascript"></script>'."\r\n";
            }
        }else{
            if (!isset(self::$scripts['script'])) {
                self::$scripts['script'] = [];
            }
            self::$scripts['script'][] = '<script type="text/javascript">$(document).ready(function(){'.$script.'});</script>'."\r\n";
        }
    }

    public static function addStyle($style, $isSource = false, $isDefault = false){
        if($isSource){
            if($isDefault){
                if (!isset(self::$styles['default'])) {
                    self::$styles['default'] = [];
                }
                self::$styles['default'][] = '<link href="'.$style.'?token='.date('YmdH').'" rel="stylesheet" type="text/css" media="all">'."\r\n";
            }else{
                if (!isset(self::$styles['source'])) {
                    self::$styles['source'] = [];
                }
                self::$styles['source'][] = '<link href="'.$style.'?token='.date('YmdH').'" rel="stylesheet" type="text/css" media="all">'."\r\n";
            }
        }else{
            if (!isset(self::$styles['script'])) {
                self::$styles['script'] = [];
            }
            self::$styles['script'][] = '<style type="text/css">'.$style.'</style>'."\r\n";
        }
    }
}
?>