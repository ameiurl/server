<?php
/**
 * Butterfly : Non-business component library
 *
 * @copyright Copyright (c) 2016 CNCN Information Co., Ltd.
 * All rights reserved.
 */

namespace Butterfly\Cache;

/**
 * Redis 缓存
 *
 * @author 黄景祥(Joel Huang) <joelhy@gmail.com>
 * @since  1.0
 */
class RedisCache extends Cache
{
    /**
     * Redis 实例
     *
     * @var \Redis
     */
    public $redis = null;

    /**
     * {@inheritdoc}
     */
    public function __construct(array $config, CacheManager $cacheManager)
    {
        parent::__construct($config,  $cacheManager);
        $this->connect();
    }

    /**
     * 连接 Redis 服务器
     */
    public function connect()
    {
        $this->redis = null;  // 清除之前已设置的 redis 存储

        $config  = $this->config;
        $host    = $config['host'] ?? '127.0.0.1';
        $port    = $config['port'] ?? 6379;
        $timeout = $config['timeout'] ?? null;

        $redis = new \Redis();
        $redis->connect($host, $port, $timeout);

        // 验证密码
        if (isset($config['password'])) {
            $redis->auth($config['password']);
        }

        // 设置 redis 客户端选项
        if (!empty($config['options'])) {
            foreach ($config['options'] as $option => $value) {
                $redis->setOption($option, $value);
            }
        }

        // 使用指定的数据库
        if (isset($config['database'])) {
            $redis->select($config['database']);
        }

        $this->redis = $redis;
    }

    /**
     * {@inheritdoc}
     */
    public function set($key, $value, $expire = 0)
    {
        $value = is_numeric($value) ? $value : serialize($value);

        if ($expire === 0) {
            return $this->redis->set($key, $value);
        } else {
            return $this->redis->setex($key, $expire, $value);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function get($key)
    {
        $value = $this->redis->get($key);
        if ($value === false) {
            return false;
        }

        return is_numeric($value) ? $value : unserialize($value);
    }

    /**
     * {@inheritdoc}
     */
    public function has($key) : bool
    {
        return $this->redis->exists($key);
    }

    /**
     * {@inheritdoc}
     */
    public function delete($key) : bool
    {
        return $this->redis->del($key);
    }

    /**
     * {@inheritdoc}
     */
    public function flush() : bool
    {
        return $this->redis->flushDB();
    }

    /**
     * {@inheritdoc}
     */
    public function mget(array $keys)
    {
        $values = $this->redis->mget($keys);
        $items = [];
        foreach ($values as $key => $value) {
            $items[$key] = is_numeric($value) ? $value : unserialize($value);
        }

        return $items;
    }

    /**
     * {@inheritdoc}
     */
    public function mset(array $items, $expire = 0)
    {
        $values = [];
        foreach ($items as $key => $item) {
            $values[$key] = is_numeric($item) ? $item : serialize($item);
        }

        return $this->redis->mset($values);
    }

    /**
     * {@inheritdoc}
     */
    public function increment($key, $offset = 1)
    {
        return $this->redis->incrBy($key, $offset);
    }

    /**
     * {@inheritdoc}
     */
    public function decrement($key, $offset = 1)
    {
        return $this->redis->decrBy($key, $offset);
    }
}
