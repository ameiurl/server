<?php
/**
 * Created by IntelliJ IDEA.
 * User: nathena
 * Date: 2019/3/13 0013
 * Time: 14:35
 */
$root = dirname(__DIR__);
//require_once $root.'/vendor/autoload.php';
require_once $root.'/src/FunctionCommon.php';
require_once $root.'/src/Autoloader.php';
$autoloader = \zeus\sandbox\Autoloader::getInstance();
$autoloader->registerNamespaces('cncn\foundation',$root."/src");