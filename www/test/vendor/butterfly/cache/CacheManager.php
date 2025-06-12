<?php
/**
 * Butterfly : Non-business component library
 *
 * @copyright Copyright (c) 2016 CNCN Information Co., Ltd.
 * All rights reserved.
 */

namespace Butterfly\Cache;

use Butterfly\Container\Container;
use RuntimeException;

/**
 * 缓存管理
 *
 * @author 黄景祥(Joel Huang) <joelhy@gmail.com>
 * @since  1.0
 */
class CacheManager
{
    /**
     * 默认配置键值
     *
     * @var string
     */
    protected $defaultIndex;

    /**
     * 配置数组
     *
     * @var array
     */
    protected $config;

    /**
     * 依赖注入容器
     *
     * @var Container
     */
    protected $container;

    /**
     * 缓存实例
     *
     * @var array
     */
    protected $instances = [];

    /**
     * 构造函数
     *
     * @param string    $defaultIndex
     * @param array     $configs
     * @param Container $container
     */
    public function __construct(
        $defaultIndex,
        array $configs,
        Container $container
    ) {
        $this->defaultIndex = $defaultIndex;
        $this->configs       = $configs;
        $this->container    = $container;
    }

    /**
     * 创建伪缓存存储
     *
     * @param array $config
     * @return DummyCache
     */
    protected function dummyFactory($config)
    {
        return new DummyCache($config, $this);
    }

    /**
     * 创建文件缓存存储
     *
     * @param array $config
     * @return FileCache
     */
    protected function fileFactory($config)
    {
        return new FileCache($config, $this);
    }

    /**
     * 创建 Memcached 缓存存储
     *
     * @param array $config
     * @return MemcachedCache
     */
    protected function memcachedFactory($config)
    {
        return new MemcachedCache($config, $this);
    }

    /**
     * 创建内存缓存存储
     *
     * @param array $config
     * @return ArrayCache
     */
    protected function arrayFactory($config)
    {
        return new ArrayCache($config, $this);
    }

    /**
     * 创建 redis 缓存存储
     *
     * @param array $config
     * @return RedisCache
     */
    protected function redisFactory($config)
    {
        return new RedisCache($config, $this);
    }

    /**
     * 获取相应的工厂方法
     *
     * @param string $type
     * @return string
     */
    public function getFactoryMethodName($type)
    {
        $method = $type . 'Factory';
        if (!method_exists($this, $method)) {
            $tpl = '%s(): A factory method for the [ %s ] adapter has not been defined.';
            throw new RuntimeException(vsprintf($tpl, [__METHOD__, $type]));
        }

        return $method;
    }

    /**
     * 获取缓存存储类
     *
     * @param string $index
     * @return CacheInterface
     */
    public function store($index = null)
    {
        $index = $index ?? $this->defaultIndex;
        if (!isset($this->instances[$index])) {
            if (!isset($this->configs[$index])) {
                $tpl = '%s(): [ %s ] has not been defined in the cache configuration.';
                throw new RuntimeException(vsprintf($tpl, [__METHOD__, $index]));
            }

            $config = $this->configs[$index];

            $factoryMethod = $this->getFactoryMethodName($config['type']);
            $this->instances[$index] = $this->$factoryMethod($config);
        }

        return $this->instances[$index];
    }
}
