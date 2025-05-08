<?php
/**
 * Butterfly : Non-business component library
 *
 * @copyright Copyright (c) 2016 CNCN Information Co., Ltd.
 * All rights reserved.
 */

namespace Butterfly\Cache;

/**
 * Memcached 缓存
 *
 * @author 黄景祥(Joel Huang) <joelhy@gmail.com>
 * @since  1.0
 */
class MemcachedCache extends Cache
{
    /**
     * Memcached 实例
     *
     * @var \Memcached
     */
    public $memcached = null;

    /**
     * {@inheritdoc}
     */
    public function __construct(array $config, CacheManager $cacheManager)
    {
        parent::__construct($config,  $cacheManager);

        $this->memcached = new \Memcached();
        if (!empty($config['options'])) {
            $this->memcached->setOptions($config['options']);
        }
        $result = $this->memcached->addServers($config['servers']);

        // 连接失败时，改用 dummy cache，以避免重复的 Memcached::set 错误
        if ($result === false) {
            $this->memcached = new DummyCache($config, $cacheManager);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function set($key, $value, $expire = 0)
    {
        return $this->memcached->set($key, $value, $expire);
    }

    /**
     * {@inheritdoc}
     */
    public function get($key)
    {
        return $this->memcached->get($key);
    }

    /**
     * {@inheritdoc}
     */
    public function has($key) : bool
    {
        $this->memcached->get($key);

        return $this->memcached->getResultCode() === \Memcached::RES_SUCCESS;
    }

    /**
     * {@inheritdoc}
     */
    public function delete($key) : bool
    {
        return $this->memcached->delete($key);
    }

    /**
     * {@inheritdoc}
     */
    public function flush() : bool
    {
        $this->memcached->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function mget(array $keys)
    {
        $prefixedKeys = [];
        foreach ($keys as $key) {
            $prefixedKeys[] = $key;
        }

        return $this->memcached->getMulti($prefixedKeys);
    }

    /**
     * {@inheritdoc}
     */
    public function mset(array $items, $expire = 0)
    {
        $prefixedValues = [];
        foreach ($items as $key => $value) {
            $prefixedValues[$key] = $value;
        }

        return $this->memcached->setMulti($prefixedValues, $expire);
    }

    /**
     * {@inheritdoc}
     */
    public function increment($key, $offset = 1)
    {
        return $this->memcached->increment($key, $offset);
    }

    /**
     * {@inheritdoc}
     */
    public function decrement($key, $offset = 1)
    {
        return $this->memcached->decrement($key, $offset);
    }
}
