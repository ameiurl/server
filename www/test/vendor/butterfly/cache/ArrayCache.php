<?php
/**
 * Butterfly : Non-business component library
 *
 * @copyright Copyright (c) 2016 CNCN Information Co., Ltd.
 * All rights reserved.
 */

namespace Butterfly\Cache;

/**
 * 数组缓存
 *
 * @author 黄景祥(Joel Huang) <joelhy@gmail.com>
 * @since  1.0
 */
class ArrayCache extends Cache
{
    /**
     * 数组缓存存储的值
     *
     * @var array
     */
    protected $storage = [];

    /**
     * {@inheritdoc}
     */
    public function set($key, $value, $expire = 0)
    {
        if ($expire === 0) {
            $timeout = $expire;
        } else {
            $timeout = time() + $expire;
        }

        $this->storage[$key] = ['value' => $value, 'timeout' => $timeout];
    }

    /**
     * {@inheritdoc}
     */
    public function get($key)
    {
        if (!isset($this->storage[$key])) {
            return false;
        }
        $timeout = $this->storage[$key]['timeout'];

        if ($timeout === 0 || $timeout > time()) {
            return $this->storage[$key];
        } else {
            unset($this->storage[$key]);
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function has($key) : bool
    {
        if (!isset($this->storage[$key])) {
            return false;
        }
        $timeout = $this->storage[$key]['timeout'];

        return $timeout === 0 || $timeout > time();
    }

    /**
     * {@inheritdoc}
     */
    public function delete($key) : bool
    {
        unset($this->storage[$key]);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function flush() : bool
    {
        $this->storage = [];

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function increment($key, $offset = 1)
    {
        if (!isset($this->storage[$key])) {
            $this->storage[$key] = 0;
        }

        $this->storage[$key] += (int)$offset;

        return $this->storage[$key];
    }
}
