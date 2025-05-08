<?php
/**
 * Butterfly : Non-business component library
 *
 * @copyright Copyright (c) 2016 CNCN Information Co., Ltd.
 * All rights reserved.
 */

namespace Butterfly\Cache;

/**
 * 缓存接口
 *
 * @author 黄景祥(Joel Huang) <joelhy@gmail.com>
 * @since  1.0
 */
interface CacheInterface
{
    /**
     * 设置缓存
     *
     * @param  string $key
     * @param  mixed  $value
     * @param  int    $expire 过期时间，0代表不过期，单位为秒
     * @return bool   成功返回 true，失败返回 false
     */
    public function set($key, $value, $expire = 0);

    /**
     * 获取缓存
     *
     * @param  string $key
     * @return mixed  不存在返回 false
     */
    public function get($key);

    /**
     * 判断是否存在键值为 $key 的有效缓存
     *
     * @param string $key
     * @return bool
     */
    public function has($key) : bool;

    /**
     * 删除缓存
     *
     * @param  string $key
     * @return bool
     */
    public function delete($key) : bool;

    /**
     * 清空所有缓存
     */
    public function flush() : bool;

    /**
     * 获取多个键值对应的缓存
     *
     * @param array $keys
     * @return array
     */
    public function mget(array $keys);

    /**
     * 设置多个缓存
     *
     * @param array $items
     * @param int   $expire
     * @return mixed
     */
    public function mset(array $items, $expire = 0);

    /**
     * 增加数值元素的值
     *
     * @param string $key
     * @param int    $offset
     * @return int|false
     */
    public function increment($key, $offset = 1);

    /**
     * 减小数值元素的值
     *
     * @param string $key
     * @param int    $offset
     * @return int|false
     */
    public function decrement($key, $offset);

    /**
     * 从缓存中获取值，缓存中无值时，则返回 $callback 的返回值
     *
     * @param string   $key
     * @param Callable $callback
     * @param int      $expire
     * @return mixed
     */
    public function remember($key, callable $callback, $expire = 0);
}
