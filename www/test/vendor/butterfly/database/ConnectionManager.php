<?php
/**
 * Butterfly : Non-business component library
 *
 * @copyright Copyright (c) 2016 CNCN Information Co., Ltd.
 * All rights reserved.
 */

namespace Butterfly\Database;

use UnexpectedValueException;

/**
 * 数据库连接管理器
 *
 * @author 黄景祥(Joel Huang) <joelhy@gmail.com>
 * @since  1.0
 */
class ConnectionManager
{
    /**
     * 默认指向的配置数组键值
     *
     * @var mixed
     */
    protected $defaultIndex;

    /**
     * 数据库连接配置
     *
     * @var array
     */
    protected $configs = [];

    /**
     * 所有连接的 Connection 对象数组
     *
     * @var array
     */
    protected $connections = [];

    /**
     * 当前连接对应的配置数组键值
     *
     * @var mixed
     */
    protected $currentIndex;

    /**
     * ConnectionManager constructor.
     *
     * @param int|string $defaultIndex
     * @param array      $configs
     */
    public function __construct($defaultIndex, array $configs)
    {
        $this->defaultIndex = $defaultIndex;
        $this->configs      = $configs;
    }

    /**
     * 建立数据库连接
     *
     * 使用示例：
     * ```php
     * $this->connect(0);  // 方式1，数字索引
     * $this->connect('custom_index'); // 方式2，字符串索引
     * ```
     *
     * @param  int|string $index 要使用的数据库连接配置
     * @return Connection
     * @throws UnexpectedValueException
     */
    public function connect($index = null)
    {
        $index = $index ?? $this->defaultIndex;
        if (!isset($this->connections[$index])) {
            $this->currentIndex = $index;

            $config = $this->configs[$index] ?? [];
            if (!$config) {
                throw new UnexpectedValueException(
                    '$config ' . $index .
                    ' must be an non-empty array to connect to database.'
                );
            }

            $this->connections[$index] = new Connection($config);
        }

        return $this->connections[$index];
    }
}
