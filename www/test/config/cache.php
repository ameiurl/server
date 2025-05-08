<?php
/**
 * 缓存配置
 */
if (getenv('APP_ENV') === 'prod') {
    $memcachedServers = [
        ['192.168.1.73', 12121],
        ['192.168.1.72', 12121],
    ];
    $redis_cfg = [
        'host' => '192.168.1.72',
        'auth' => 'nnTI15f6cde9Ya'
    ];
} else {
    $memcachedServers = [
        ['192.168.9.1', 12121],
        ['192.168.9.1', 12122],
    ];
    if (isset($_SERVER['SERVER_ADDR']) && $_SERVER['SERVER_ADDR'] == '172.18.0.252') {
        $redis_cfg = [
            'host' => '127.0.0.1',
            'auth' => NULL
        ];
    } else {
        $redis_cfg = [
            'host' => '192.168.9.1',
            'auth' => 'linzc@cncn.com'
        ];
    }
}

return [
    'default' => 'redis',  // 默认使用的缓存配置

    'configs' => [
        'file'      => [
            'type'   => 'file',
            'prefix' => 'firefly',
            'path'   => FIREFLY_STORAGE_PATH . '/cache/',
        ],
        'memcached' => [
            'type'    => 'memcached',
            'servers' => $memcachedServers,
            'options' => [
                // http://cn2.php.net/manual/en/memcached.setoptions.php 调用的参数
                //Memcached::OPT_PREFIX_KEY      => 'firefly',
                Memcached::OPT_PREFIX_KEY      => '',
                Memcached::OPT_BINARY_PROTOCOL => true,  // 使用二进制协议
            ]
        ],
        'redis'     => [
            'type'     => 'redis',
            'host'     => $redis_cfg['host'],
            'port'     => 6379,
            'password' => $redis_cfg['auth'],
            // 'timeout'  => 3,
            'database' => 10,
            'options'  => [
                Redis::OPT_PREFIX       => 'firefly',
                Redis::OPT_READ_TIMEOUT => -1,
                // ref. https://github.com/phpredis/phpredis/pull/260
            ],
        ]
    ]
];
