<?php
/**
 * Butterfly : Non-business component library
 *
 * @copyright Copyright (c) 2016 CNCN Information Co., Ltd.
 * All rights reserved.
 */

namespace Butterfly\Cache;

/**
 * 缓存类
 *
 * @author 黄景祥(Joel Huang) <joelhy@gmail.com>
 * @since  1.0
 */
abstract class Cache implements CacheInterface
{
    /**
     * 配置项数组
     *
     * @var array
     */
    protected $config = [];

    /**
     * 缓存管理器
     *
     * @var CacheManager
     */
    protected $cacheManager;

    /**
     * 构造函数
     *
     * @param array        $config
     * @param CacheManager $cacheManager
     */
    public function __construct(array $config, CacheManager $cacheManager)
    {
        $this->config       = $config;
        $this->cacheManager = $cacheManager;
    }

    /**
     * 获取缓存存储类
     *
     * @param string $index
     * @return CacheInterface
     */
    public function store($index)
    {
        return $this->cacheManager->store($index);
    }

    /**
     * {@inheritdoc}
     */
    public function mget(array $keys)
    {
        $items = [];
        foreach ($keys as $key) {
            $items[$key] = $this->get($key);
        }

        return $items;
    }

    /**
     * {@inheritdoc}
     */
    public function mset(array $items, $expire = 0)
    {
        $results = [];
        foreach ($items as $key => $value) {
            $results[$key] = $this->set($key, $value, $expire);
        }

        return $results;
    }

    /**
     * {@inheritdoc}
     */
    public function decrement($key, $offset = 1)
    {
        return $this->increment($key, $offset * -1);
    }

    /**
     * {@inheritdoc}
     */
    public function remember($key, callable $callback, $expire = 0)
    {
        if (!$this->has($key)) {
            $data = $callback();
            $this->set($key, $data, $expire);
            return $data;
        } else {
            return $this->get($key);
        }
    }
}
