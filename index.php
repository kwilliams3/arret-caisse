<?php
/**
 * Created by PhpStorm.
 * User: hp
 * Date: 16/05/2017
 * Time: 16:25
 */

define('ROOT', dirname(__DIR__));
require 'Core/Autoloader.php';



Core\Autoloader::register();
Core\Model\App::getRoute();

?>