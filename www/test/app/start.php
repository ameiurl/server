<?php
use Butterfly\Web\Application;
//程序根目录定义
define('ROOT', dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR);
//定义程序运行时的文件缓存路径
define('RUNTIME_PATH', ROOT . 'sitedata/runtime/' . APP_NAME . '/');
//定义程序日志路径
define('LOG_PATH', ROOT . 'sitedata/logs/' . APP_NAME . '/');
//配置目录
define('CONF_PATH', ROOT . '/config/');

/**
 * 定义存储路径
 */
define('FIREFLY_STORAGE_PATH', realpath(__DIR__ . '/../sitedata'));

/**
 * 更改错误日志的默认存储位置
 */
ini_set('error_log', FIREFLY_STORAGE_PATH . '/logs/php_error/' . date('Y-m') . '.log');

//定义程序目录
//程序入口
define('APP_NAMESPACE', APP_NAME);
define('APP_PATH', ROOT . 'app/');

// 加载框架引导文件
require_once ROOT.'bundle/Autoloader.php';
$autoloader = \zeus\sandbox\Autoloader::getInstance();
$autoloader->registerNamespaces('My\component', ROOT . 'bundle/component');
$autoloader->registerNamespaces('My\data',ROOT . 'bundle/data');
$autoloader->registerNamespaces('My\Util',ROOT . 'bundle/util');
$autoloader->registerNamespaces('My\lib',ROOT . 'bundle/lib');

//加载公用函数库
require_once ROOT.'bundle/util/FunctionCommon.php';


define('FIREFLY_APP_PATH', __DIR__);

include dirname(__DIR__) . '/vendor/autoload.php';

/**
 * 启动并运行应用
 */
Application::start(FIREFLY_APP_PATH)->run();
