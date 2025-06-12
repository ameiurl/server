<?php
/**
 * Butterfly : Non-business component library
 *
 * @copyright Copyright (c) 2016 CNCN Information Co., Ltd.
 * All rights reserved.
 */

namespace Butterfly\Utility;

/**
 * 数组操作类
 *
 * @author 黄景祥(Joel Huang) <joelhy@gmail.com>
 * @since  1.0
 */
class Arr
{
    /**
     * 使用英文点“.”分隔的形式获取数组元素
     *
     * 使用示例：
     * ```
     * // $name = $arr['user']['name'];
     * $name = Arr::get($arr, 'user.name');
     *
     * // 对应的值不存在时，返回默认值 joelhy
     * $name = Arr::get($arr, 'user.name', 'John');
     * ```
     *
     * @param  array      $array
     * @param  int|string $key
     * @param  mixed      $default
     * @return mixed
     */
    public static function get($array, $key, $default = null)
    {
        if ($key === null) {
            return $array;
        }

        $segments = explode('.', $key);
        foreach ($segments as $segment) {
            if (!is_array($array) || !isset($array[$segment])) {
                return $default;
            }

            $array = $array[$segment];
        }

        return $array;
    }

    /**
     * 使用英文点“.”分隔的形式设置数组的值
     *
     * 使用示例：
     * ```php
     * // 设置 $array['user']['name'] 为 'John Doe'
     * Arr::set($array, 'user.name', 'John Doe');
     *
     * // 设置 $array['user']['name']['first'] 为 'Jane'
     * Arr::set($array, 'user.name.first', 'Jane');
     * ```
     *
     * @param array  $array
     * @param string $path
     * @param mixed  $value
     * @return array
     */
    public static function set(&$array, $path, $value) : array
    {
        $segments = explode('.', $path);
        while (count($segments) > 1) {
            $segment = array_shift($segments);
            if (!isset($array[$segment]) || !is_array($array[$segment])) {
                $array[$segment] = [];
            }

            $array =& $array[$segment];
        }

        $array[array_shift($keys)] = $value;

        return $array;
    }

    /**
     * 从指定数组获取键值在 $keys 数组中的元素
     *
     * 使用示例：
     * ```php
     * $data = $_POST;
     * // 从 $data 数组中，只取 username, email 两项
     * $filtered = Arr::only($data, ['username', 'email']);
     *
     * $filtered = Arr::only($data, ['created_at']);
     * // 等价于
     * $filtered = Arr::only($data, 'created_at');
     * ```
     *
     * @param  array $array
     * @param  array $keys
     * @return array
     */
    public static function only($array, $keys) : array
    {
        return array_intersect_key($array, array_flip((array)$keys));
    }

    /**
     * 从指定数组获取键值不在 $keys 数组中的元素
     *
     * 使用示例：
     * ```php
     * $data = $_POST;
     * // 从 $data 数组中，取 除 password 的所有项
     * $filtered = Arr::except($data, ['password']);
     * // 或者
     * $filtered = Arr::except($data, 'password');
     * ```
     *
     * @param  array $array
     * @param  array $keys
     * @return array
     */
    public static function except($array, $keys) : array
    {
        return array_diff_key($array, array_flip((array)$keys));
    }

    /**
     * 将索引数组转换为以某键的值为索引的数组
     *
     * 代码示例：
     * ```php
     * $arr = [
     *      ['id' => 123, 'username' => 'John Doe'],
     *      ['id' => 456, 'username' => 'Jane Doe'],
     * ];
     * $res = Arr::keyList($arr, 'id');
     *
     * // 得到的结果为
     * [
     *      123 => ['id' => 123, 'username' => 'John Doe'],
     *      456 => ['id' => 456, 'username' => 'Jane Doe'],
     * ];
     * ```
     *
     * @param array  $array 要进行转换的数组
     * @param string $key   以该 key 对应的数组元素的值作为索引
     * @return array
     */
    public static function keyList($array, $key = 'id') : array
    {
        if (!is_array($array)) {
            return [];
        }

        $result = [];
        foreach ($array as $arr) {
            if (!isset($arr[$key])) {
                break;
            }

            $result[$arr[$key]] = $arr;
        }

        return $result;
    }

}
