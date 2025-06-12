<?php
/**
 * Butterfly : Non-business component library
 *
 * @copyright Copyright (c) 2016 CNCN Information Co., Ltd.
 * All rights reserved.
 */

namespace Butterfly\Foundation;

use LogicException;
use Butterfly\{
    Autoloading\AliasLoader,
    Config\Config,
    Container\Container
};

/**
 * 应用程序
 *
 * @author 黄景祥(Joel Huang) <joelhy@gmail.com>
 * @since  1.0
 */
abstract class Application
{
    /**
     * 单实例
     *
     * @var Application
     */
    protected static $instance;

    /**
     * 配置实例
     *
     * @var Config
     */
    protected $config;

    /**
     * 依赖注入容器
     *
     * @var Container
     */
    protected $container;

    /**
     * 应用程序所在路径
     *
     * @var string
     */
    protected $appPath;

    /**
     * 运行环境文件
     *
     * @var string
     */
    protected $envFile = '.env';

    /**
     * 当前运行环境： dev - 开发环境； prod - 产品环境
     *
     * @var string
     */
    protected $env;

    /**
     * Application constructor.
     *
     * @param string $appPath 应用程序路径
     */
    public function __construct(string $appPath)
    {
        $this->appPath = $appPath;

        $this->boot();
    }

    /**
     * 加载自定义的环境变量
     */
    protected function loadEnvs()
    {
        $filePath = $this->appPath . '/../' . $this->envFile;
        if (!is_file($filePath)) {
            putenv('APP_ENV=prod');
            return;
        }

        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            putenv($line);
        }

        // 设置默认环境
        if (!getenv('APP_ENV')) {
            putenv('APP_ENV=prod');
        }
    }

    /**
     * 启动应用并返回应用的单实例
     *
     * @param $appPath
     * @return static
     */
    public static function start(string $appPath)
    {
        if (!empty(static::$instance)) {
            $tpl = '%s(): The application has already been started.';
            throw new LogicException(vsprintf($tpl, [__METHOD__]));
        }

        return static::$instance = new static($appPath);
    }

    /**
     * 获取应用的单实例
     *
     * @return Application
     */
    public static function instance() : Application
    {
        if (empty(static::$instance)) {
            $tpl = '%s(): The application has not been started yet.';
            throw new LogicException(vsprintf($tpl, [__METHOD__]));
        }

        return static::$instance;
    }

    /**
     * 启动应用
     */
    protected function boot()
    {
        // 加载环境变量
        $this->loadEnvs();

        // 初始化框架
        $this->initFramework();

        // 加载配置项
        $this->configure();

        // 注册服务到依赖注入容器中
        $this->registerServices();

        // 注册类的别名
        $this->registerClassAliases();

        // 设置 StaticalProxy 关联的应用实例
        StaticalProxy::setApplication($this);

        // 加载引导程序
        $this->bootstrap();
    }

    /**
     * 初始化框架
     */
    protected function initFramework()
    {
        $this->container              = new Container();
        $this->container['container'] = $this->container;
        $this->container['app']       = $this;
        $this->config                 = new Config($this->appPath . '/config',
            $this->getEnv());
        $this->container['config']    = $this->config;
    }

    /**
     * 配置应用程序
     */
    protected function configure()
    {
        // 正式环境不显示 PHP 错误信息
        ini_set('display_errors', getenv('APP_ENV') !== 'prod');
    }

    /**
     * 获取运行环境
     *
     * 没有指定时返回 null
     *
     * @return string|null
     */
    public function getEnv()
    {
        return getenv('APP_ENV') ?: null;
    }

    /**
     * 注册服务
     */
    protected function registerServices()
    {
        // 注册核心服务
        $this->serviceRegistrar('core');

        // 注册环境关联服务
        if ($this->isCli()) {
            $this->serviceRegistrar('cli');
        } else {
            if ($this instanceof \Butterfly\Rest\Application) {
                $this->serviceRegistrar('rest');
            } else {
                $this->serviceRegistrar('web');
            }
        }
    }

    /**
     * 注册服务到依赖注入容器
     *
     * @param string $type 服务类型
     */
    protected function serviceRegistrar(string $type)
    {
        $services = $this->config['app.services.' . $type];
        foreach ($services as $service) {
            /** @var ServiceProvider $srv */
            $srv = new $service();
            $srv->register($this->container);
        }
    }

    /**
     * 是否应用是跑在命令行模式下
     *
     * @return bool
     */
    public function isCli() : bool
    {
        return PHP_SAPI === 'cli';
    }

    /**
     * 注册类的别名加载器
     */
    protected function registerClassAliases()
    {
        $aliases = $this->config['app.class_aliases'];
        if (!empty($aliases)) {
            $aliasLoader = new AliasLoader($aliases);
            $aliasLoader->register();
        }
    }

    /**
     * 加载引导程序
     */
    protected function bootstrap()
    {
        /** @noinspection PhpUnusedParameterInspection
         * @param Application $app
         * @param Container   $container
         */
        $bootstrap = function ($app, $container) {
            $oldDir = getcwd();
            chdir($this->appPath);

            // 加载 \d{2}_.+\.php 形式的文件
            $pattern = 'bootstrap/[0-9][0-9]_*.php';
            if (is_dir('../bootstrap')) {
                $commonBootstrap = glob('../' . $pattern);
            } else {
                $commonBootstrap = [];
            }
            if (is_dir('bootstrap')) {
                $appBootstrap = glob($pattern);
            } else {
                $appBootstrap = [];
            }
            $files = array_merge($commonBootstrap, $appBootstrap);

            foreach ($files as $file) {
                include $file;
            }

            chdir($oldDir);
        };

        $bootstrap($this, $this->container);
    }

    /**
     * 运行应用
     *
     * @return mixed
     */
    abstract public function run();

    /**
     * 获取依赖注入容器
     *
     * @return Container
     */
    public function getContainer() : Container
    {
        return $this->container;
    }
}
