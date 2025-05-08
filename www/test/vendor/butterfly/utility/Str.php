<?php
/**
 * Butterfly : Non-business component library
 *
 * @copyright Copyright (c) 2016 CNCN Information Co., Ltd.
 * All rights reserved.
 */

namespace Butterfly\Utility;

/**
 * 字符串处理类
 *
 * @author 黄景祥(Joel Huang) <joelhy@gmail.com>
 * @since  1.0
 */
class Str
{
    /**
     * 把下划线分隔的字符串转换成 "PascalCase" 形式的字符串
     *      （第一个单词首字母大写，后几个单词首字母大写）
     *
     * 使用示例：
     *
     * ```php
     * Str::pascal('controller_name');  // 返回 ControllerName
     * ```
     *
     * @param  string $string
     * @return string
     */
    public static function pascal($string) : string
    {
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $string)));
    }

    /**
     * 把下划线分隔的字符串转换成 "camelCase" 形式的字符串
     *      （第一个单词首字母小写，后几个单词首字母大写）
     *
     * 使用示例：
     *
     * ```php
     * Str::camel('method_name');  // 返回 methodName
     * ```
     *
     * @param  string $string
     * @return string
     */
    public static function camel($string) : string
    {
        return lcfirst(self::pascal($string));
    }

    /**
     * 把 pascal case / camel case 形式的字符串转换成下划线分隔的形式
     *
     * 使用示例：
     *
     * ```php
     * Str::snake('ControllerName');  // 返回 controller_name
     * ```
     *
     * @param  string $string
     * @return string
     */
    public static function snake($string) : string
    {
        // (?<=) 为正向后行断言
        // ref. http://www.regular-expressions.info/lookaround.html#lookbehind
        return strtolower(preg_replace('/(?<=\\w)([A-Z])/', '_$1', $string));
    }

    /**
     * 获取当前字段的字符串长度
     *
     * @param string $string
     * @return int|false
     */
    public static function len(string $string)
    {
        return mb_strlen($string, 'UTF-8');
    }
}
