<?php
/**
 * Butterfly : Non-business component library
 *
 * @copyright Copyright (c) 2016 CNCN Information Co., Ltd.
 * All rights reserved.
 */

namespace Butterfly\Config;

use ArrayAccess;
use Butterfly\Utility\Arr;

/**
 * 配置项获取
 *
 * @author 黄景祥(Joel Huang) <joelhy@gmail.com>
 * @since  1.0
 */
class Config implements ArrayAccess
{
    /**
     * 配置数组
     *
     * @var array
     */
    protected $items = [];

    /**
     * 当前运行环境： dev - 开发环境； prod - 产品环境
     *
     * @var string
     */
    protected $env;

    /**
     * 配置缓存
     *
     * @var array
     */
    protected $keyCache = [];

    /**
     * 配置文件存储路径
     *
     * @var string
     */
    protected $path;

    /**
     * 是否查找上级目录中的公共配置
     *
     * @var bool
     */
    protected $searchCommonConfig;

    /**
     * Config constructor.
     *
     * @param string $path               配置文件路径
     * @param string $env                运行环境： dev, prod, test etc.
     * @param bool   $searchCommonConfig 是否查找上级目录中的公共配置
     */
    public function __construct(
        string $path,
        $env = null,
        $searchCommonConfig = true
    ) {
        $this->path = $path;
        $this->env  = $env ?: 'prod';
        $this->searchCommonConfig = $searchCommonConfig;
    }

    /**
     * 获取指定键值的配置项
     *
     * @param  string $key 配置项的键值
     * @param  mixed  $default 找不到该键值时的默认值
     * @return mixed
     */
    public function get($key, $default = null)
    {
        return $this->offsetGet($key) ?? $default;
    }

    /**
     * 获取指定键值的配置项
     *
     * @param  string $key 配置项的键值
     * @return mixed
     */
    public function offsetGet($key)
    {
        $default = null;
        if (array_key_exists($key, $this->keyCache)) {
            return $this->keyCache[$key];
        }

        $segments = explode('.', $key, 2);
        $items    = $this->load($segments[0]);
        if (!$items) {
            return $this->keyCache[$key] = $default;
        }

        if (!isset($segments[1])) {
            $item = $items;
        } else {
            $item = Arr::get($items, $segments[1], $default);
        }

        return $this->keyCache[$key] = $item;
    }

    /**
     * 设置配置项的值
     *
     * @param string $key
     * @param mixed  $value
     */
    public function set($key, $value)
    {
        return $this->offsetSet($key, $value);
    }

    /**
     * 设置配置项的值
     *
     * @param string $key
     * @param mixed  $value
     */
    public function offsetSet($key, $value)
    {
        $this->keyCache[$key] = $value;
    }

    /**
     * 加载配置文件
     *
     * @param  string $file
     * @return mixed
     */
    protected function load($file)
    {
        $loadConfig = function ($dir, $file) {
            $path = sprintf('%s/%s.%s.php', $dir, $file, $this->env);
            if (file_exists($path)) {
                $config = include $path;
            } else {
                $path = sprintf('%s/%s.php', $dir, $file);
                if (file_exists($path)) {
                    $config = include $path;
                } else {
                    $config = [];
                }
            }

            return $config;
        };

        if (!isset($this->items[$file])) {
            $dir          = realpath(dirname($this->path, 2) . '/config');
            if ($this->searchCommonConfig && is_dir($dir)) {
                $commonConfig = $loadConfig($dir, $file);
            } else {
                $commonConfig = [];
            }
            $appConfig    = $loadConfig($this->path, $file);
            $config       = array_replace_recursive($commonConfig, $appConfig);

            $this->items[$file] = $config;
        }

        return $this->items[$file];
    }

    /**
     * 判断当前配置项是否存在
     *
     * ```php
     *  $exist = isset($config['app.error_reporting']);
     * ```
     *
     * @param  string $key
     * @return bool
     */
    public function offsetExists($key) : bool
    {
        return $this->offsetGet($key) !== null;
    }

    /**
     * 置空配置项
     *
     * @param string $key
     */
    public function offsetUnset($key)
    {
        $this->keyCache[$key] = null;
    }
}
