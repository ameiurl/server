<?php
/**
 * Butterfly : Non-business component library
 *
 * @copyright Copyright (c) 2016 CNCN Information Co., Ltd.
 * All rights reserved.
 */

namespace Butterfly\Cache;

/**
 * 伪Cache（实际不进行缓存操作）
 *      把原有使用缓存的程序的缓存驱动换成此驱动，即会禁用缓存
 *      无需更改缓存设置相关代码即完成切换
 *
 * @author 黄景祥(Joel Huang) <joelhy@gmail.com>
 * @since  1.0
 */
class DummyCache extends Cache
{
    /**
     * {@inheritdoc}
     */
    public function set($key, $value, $expire = 0)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function get($key)
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function has($key) : bool
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function delete($key) : bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function flush() : bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function increment($key, $offset = 1)
    {
        return (int) $offset;
    }
}
