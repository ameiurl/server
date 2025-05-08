<?php
/**
 * 数据库配置
 */
return [
    'default' => 'new_cncn',  // 默认使用的数据库配置

    'configs' => [
        'cncn_net'   => [
            'dsn'         => 'mysql:dbname=cncn_net;host=192.168.9.7;port=3306',
            // PDO data source name
            'username'    => 'cncn',
            // 数据库连接的用户名
            'password'    => 'cncn@123#456',
            // 数据库连接的密码
            'log_queries' => false,
            // 是否启用查询日志
            'options'     => [  // PDO::__construct($dsn, $username, $password, $options); 的 $options 参数
                PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES UTF8',
                // MYSQL 初始执行的命令
                PDO::ATTR_TIMEOUT            => 10,
                // 超时时间，单位秒
            ],

        ],
        'new_cncn'   => [
            'dsn'         => 'mysql:dbname=new_cncn;host=192.168.9.7;port=3306',
            // PDO data source name
            'username'    => 'cncn',
            // 数据库连接的用户名
            'password'    => 'cncn@123#456',
            // 数据库连接的密码
            'log_queries' => false,
            // 是否启用查询日志
            'options'     => [  // PDO::__construct($dsn, $username, $password, $options); 的 $options 参数
                PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES UTF8',
                // MYSQL 初始执行的命令
                PDO::ATTR_TIMEOUT            => 10,
                // 超时时间，单位秒
            ],
        ],
        'jipiao'     => [
            'dsn'         => 'mysql:dbname=jipiao;host=192.168.9.7;port=3306',
            // PDO data source name
            'username'    => 'cncn',
            // 数据库连接的用户名
            'password'    => 'cncn@123#456',
            // 数据库连接的密码
            'log_queries' => false,
            // 是否启用查询日志
            'options'     => [  // PDO::__construct($dsn, $username, $password, $options); 的 $options 参数
                PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES UTF8',
                // MYSQL 初始执行的命令
                PDO::ATTR_TIMEOUT            => 10,
                // 超时时间，单位秒
            ],
        ],
        'jingdian'   => [
            'dsn'         => 'mysql:dbname=jingdian;host=192.168.9.7;port=3306',
            // PDO data source name
            'username'    => 'cncn',
            // 数据库连接的用户名
            'password'    => 'cncn@123#456',
            // 数据库连接的密码
            'log_queries' => false,
            // 是否启用查询日志
            'options'     => [  // PDO::__construct($dsn, $username, $password, $options); 的 $options 参数
                PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES UTF8',
                // MYSQL 初始执行的命令
                PDO::ATTR_TIMEOUT            => 10,
                // 超时时间，单位秒
            ],
        ],
        'ucenter'    => [
            'dsn'         => 'mysql:dbname=ucenter;host=192.168.9.7;port=3306',
            // PDO data source name
            'username'    => 'cncn',
            // 数据库连接的用户名
            'password'    => 'cncn@123#456',
            // 数据库连接的密码
            'log_queries' => false,
            // 是否启用查询日志
            'options'     => [  // PDO::__construct($dsn, $username, $password, $options); 的 $options 参数
                PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES UTF8',
                // MYSQL 初始执行的命令
                PDO::ATTR_TIMEOUT            => 10,
                // 超时时间，单位秒
            ],
        ],
        'cncn_guwen' => [
            'dsn'         => 'mysql:dbname=cncn_guwen;host=192.168.9.7;port=3306',
            // PDO data source name
            'username'    => 'cncn',
            // 数据库连接的用户名
            'password'    => 'cncn@123#456',
            // 数据库连接的密码
            'log_queries' => false,
            // 是否启用查询日志
            'options'     => [  // PDO::__construct($dsn, $username, $password, $options); 的 $options 参数
                PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES UTF8',
                // MYSQL 初始执行的命令
                PDO::ATTR_TIMEOUT            => 10,
                // 超时时间，单位秒
            ],
        ],
        'hotel' => [
            'dsn'         => 'mysql:dbname=hotel;host=192.168.9.7;port=3306',
            // PDO data source name
            'username'    => 'hotel',
            // 数据库连接的用户名
            'password'    => '123#456',
            // 数据库连接的密码
            'log_queries' => false,
            // 是否启用查询日志
            'options'     => [  // PDO::__construct($dsn, $username, $password, $options); 的 $options 参数
                PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES UTF8',
                // MYSQL 初始执行的命令
                PDO::ATTR_TIMEOUT            => 10,
                // 超时时间，单位秒
            ],
        ],
        'chat' => [
            'dsn'         => 'mysql:dbname=chat;host=192.168.9.10;port=3306',
            // PDO data source name
            'username'    => 'cncn',
            // 数据库连接的用户名
            'password'    => 'cncn@123#456',
            // 数据库连接的密码
            'log_queries' => false,
            // 是否启用查询日志
            'options'     => [  // PDO::__construct($dsn, $username, $password, $options); 的 $options 参数
                PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES UTF8',
                // MYSQL 初始执行的命令
                PDO::ATTR_TIMEOUT            => 10,
                // 超时时间，单位秒
            ],
        ],
        'rpc' => [
            'dsn'           => 'mysql:dbname=rpc;host=192.168.9.10;port=3306',  // PDO data source name
            'username'      => 'rpc',  // 数据库连接的用户名
            'password'      => 'bptsvqs7OKgl5vP',  // 数据库连接的密码
            'log_queries'   => false,  // 是否启用查询日志
            'options'       => [  // PDO::__construct($dsn, $username, $password, $options); 的 $options 参数
                PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES UTF8',  // MYSQL 初始执行的命令
                PDO::ATTR_TIMEOUT => 10,  // 超时时间，单位秒
            ],
        ],
        'b2b' => [
            'dsn'           => 'mysql:dbname=cgfx;host=192.168.9.7;port=3306',  // PDO data source name
            'username'      => 'cgfx',  // 数据库连接的用户名
            'password'      => 'caigoufenxiao',  // 数据库连接的密码
            'log_queries'   => false,  // 是否启用查询日志
            'options'       => [  // PDO::__construct($dsn, $username, $password, $options); 的 $options 参数
                PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES UTF8',  // MYSQL 初始执行的命令
                PDO::ATTR_TIMEOUT => 10,  // 超时时间，单位秒
            ],
        ],
        'new_jipiao'     => [
            'dsn'         => 'mysql:dbname=jipiao;host=192.168.9.10;port=3306',
            // PDO data source name
            'username'    => 'jipiao',
            // 数据库连接的用户名
            'password'    => 'kbnNZVCW4rQPcI',
            // 数据库连接的密码
            'log_queries' => false,
            // 是否启用查询日志
            'options'     => [  // PDO::__construct($dsn, $username, $password, $options); 的 $options 参数
                PDO::MYSQL_ATTR_INIT_COMMAND => 'SET sql_mode="NO_ENGINE_SUBSTITUTION", NAMES UTF8',
                // MYSQL 初始执行的命令
                PDO::ATTR_TIMEOUT            => 10,
                // 超时时间，单位秒
            ],
        ],
        'archive'   => [
            'dsn'         => 'mysql:dbname=archive;host=192.168.9.7;port=3306',
            // PDO data source name
            'username'    => 'cncn',
            // 数据库连接的用户名
            'password'    => 'cncn@123#456',
            // 数据库连接的密码
            'log_queries' => false,
            // 是否启用查询日志
            'options'     => [  // PDO::__construct($dsn, $username, $password, $options); 的 $options 参数
                PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES UTF8',
                // MYSQL 初始执行的命令
                PDO::ATTR_TIMEOUT            => 10,
                // 超时时间，单位秒
            ],
        ],
        'wan'   => [
            'dsn'         => 'mysql:dbname=wan;host=192.168.9.7;port=3306',
            // PDO data source name
            'username'    => 'cncn',
            // 数据库连接的用户名
            'password'    => 'cncn@123#456',
            // 数据库连接的密码
            'log_queries' => false,
            // 是否启用查询日志
            'options'     => [  // PDO::__construct($dsn, $username, $password, $options); 的 $options 参数
                PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES UTF8',
                // MYSQL 初始执行的命令
                PDO::ATTR_TIMEOUT            => 10,
                // 超时时间，单位秒
            ],
        ],
        'cncn_site'   => [
            'dsn'         => 'mysql:dbname=cncn_site;host=192.168.9.7;port=3306',
            // PDO data source name
            'username'    => 'cncn',
            // 数据库连接的用户名
            'password'    => 'cncn@123#456',
            // 数据库连接的密码
            'log_queries' => false,
            // 是否启用查询日志
            'options'     => [  // PDO::__construct($dsn, $username, $password, $options); 的 $options 参数
                PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES UTF8',
                // MYSQL 初始执行的命令
                PDO::ATTR_TIMEOUT            => 10,
                // 超时时间，单位秒
            ],
        ],
    ]
];
