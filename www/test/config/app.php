<?php
/**
 * 配置文件
 */
return [
    /**
     * 视图配置
     */
    'view'          => [
//        'engine'    => 'php',
//        'path'      => FIREFLY_APP_PATH . '/views/',  // PHP 视图引擎的配置
        'engine' => 'smarty',
        'config' => [
            'template_dir' => ROOT . '/templates/',
            'compile_dir'  => FIREFLY_STORAGE_PATH . '/smarty/templates_c/',
            'cache_dir'    => FIREFLY_STORAGE_PATH . '/smarty/cache/',
        ]
    ],

    /**
     * 日志记录配置
     */
    'log'           => [
        'engine' => 'file',
        'path'   => FIREFLY_STORAGE_PATH . '/logs/',
    ],

    /**
     * 注册的服务
     */
    'services'      => [
        'core' => [
            Butterfly\Provider\LoggerProvider::class,
            Butterfly\Provider\ErrorHandlerProvider::class,
            Butterfly\Provider\DatabaseProvider::class,
            Butterfly\Provider\CacheProvider::class,
            Butterfly\Provider\PaginationFactoryProvider::class,
            Butterfly\Provider\RedisCacheProvider::class,
        ],
        'web'  => [
            Butterfly\Provider\RequestProvider::class,
            Butterfly\Provider\ViewProvider::class,
        ],
        'cli'  => [
        ],
        'rest' => [
            Butterfly\Provider\RestProvider::class,
        ]
    ],

    /**
     * 错误处理
     */
    'error_handler' => [
        'log_errors'     => true,
        'display_errors' => getenv('APP_ENV') !== 'prod',  // 生产环境建议设置为 false
    ],

    /**
     * 类的别名
     */
    'class_aliases' => [
        'Arr'   => Butterfly\Utility\Arr::class,
        'Str'   => Butterfly\Utility\Str::class,
        'Cache' => Butterfly\Statical\Cache::class,
        'Config'  => Butterfly\Statical\Config::class,
        'Log'   => Butterfly\Statical\Log::class,
        'Reds'  => Butterfly\Statical\Reds::class,
        'Mail'  => Butterfly\Mail\Mail::class,
        'Ip'    => Butterfly\Utility\Ip::class,
        'Session'=> Dragonfly\Session\Session::class,
        'Ubb'   => Dragonfly\Ubb\Ubb::class,
        'Valid'=> Butterfly\Validator\Valid::class,
        'Xml'  => Butterfly\Utility\Xml::class,
        'Date' => Butterfly\Utility\Date::class,
        'File' => Butterfly\Utility\File::class
    ],

    /**
     * 获取控制名、方法名的参数
     */
    'trigger'       => [
        'controller' => 'action',
        'method'     => 'todo',
    ],
];
