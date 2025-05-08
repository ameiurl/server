<?php
/**
 * Butterfly : Non-business component library
 *
 * @copyright Copyright (c) 2016 CNCN Information Co., Ltd.
 * All rights reserved.
 */

namespace Butterfly\Validator;

use Butterfly\Utility\Str;

/**
 * 验证方法
 *
 * @author 黄景祥(Joel Huang) <joelhy@gmail.com>
 * @since  1.0
 */
class Valid
{
    /**
     * 验证该字段是否必须
     *
     * @param  string $value
     * @return bool
     */
    public static function required($value)
    {
        return $value !== '';
    }

    /**
     * 验证是否匹配正则规则
     *
     * @param  string $value
     * @param  array  $parameters
     * @return bool
     */
    public static function regex($value, $parameters)
    {
        return preg_match($parameters[0], $value) === 1;
    }

    /**
     * 验证是否是有效的电子邮件地址
     *
     * @param  string $value
     * @return bool
     */
    public static function email($value)
    {
        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * 验证是否是有效的 URL 地址
     *
     * @param  string $value
     * @return bool
     */
    public static function url($value)
    {
        return filter_var($value, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * 验证是否是有效的 IPv4 地址
     *
     * @param  string $value
     * @return bool
     */
    public static function ipv4($value)
    {
        return filter_var($value, FILTER_VALIDATE_IP,
            FILTER_FLAG_IPV4) !== false;
    }

    /**
     * 验证是否是有效的 IPv6 地址
     *
     * @param  string $value
     * @return bool
     */
    public static function ipv6($value)
    {
        return filter_var($value, FILTER_VALIDATE_IP,
            FILTER_FLAG_IPV6) !== false;
    }

    /**
     * 验证是否是有效的 IP 地址
     *
     * @param  string $value
     * @return bool
     */
    public static function ip($value)
    {
        return filter_var($value, FILTER_VALIDATE_IP) !== false;
    }

    /**
     * 验证是否是有效的手机号
     *
     * @param  string $value
     * @return bool
     */
    public static function mobilePhone($value)
    {
        $pattern = '/^1[3456789]\d{9}$/';

        return preg_match($pattern, $value) === 1;
    }

    /**
     * 验证是否是有效的标识符（以字母开头，字母、数字、下划线组成）
     *
     * @param  string $value
     * @param  array  $parameters
     * @return int
     */
    public static function identifier($value, $parameters)
    {
        $min = $parameters[0];
        $min -= 1;
        $min = ($min > 0) ? $min : 0;
        $max = $parameters[1];

        return preg_match('/^[a-zA-Z]\w{' . $min . ',' . $max . '}$/', $value);
    }

    /**
     * 验证是否是为十进制数
     *
     * @param  string $value
     * @return bool
     */
    public static function number($value)
    {
        return is_numeric($value);
    }

    /**
     * 验证是否是由数字组成
     *
     * @param  string $value
     * @return bool
     */
    public static function digits($value)
    {
        return ctype_digit((string)$value);
    }

    /**
     * 验证是否是由大写字母组成
     *
     * @param  string $value
     * @return bool
     */
    public static function upper($value)
    {
        return ctype_upper((string)$value);
    }

    /**
     * 验证是否是由小写字母组成
     *
     * @param  string $value
     * @return bool
     */
    public static function lower($value)
    {
        return ctype_lower((string)$value);
    }

    /**
     * 验证是否全是由字母组成
     *
     * @param  string $value
     * @return bool
     */
    public static function alpha($value)
    {
        return ctype_alpha((string)$value);
    }

    /**
     * 验证是否是由字母、数字组成
     *
     * @param  string $value
     * @return bool
     */
    public static function alphaNum($value)
    {
        return ctype_alnum((string)$value);
    }

    /**
     * 验证是否是由字母、数字、下划线组成
     *
     * @param  string $value
     * @return bool
     */
    public static function alphaDash($value)
    {
        return preg_match('/^[-\w]+$/', $value) === 1;
    }

    /**
     * 验证是否大于或等于最小值
     *
     * @param  string $value
     * @param  array  $parameters
     * @return bool
     */
    public static function min($value, $parameters)
    {
        return $value >= $parameters[0];
    }

    /**
     * 验证是否小于或等于最大值
     *
     * @param  string $value
     * @param  array  $parameters
     * @return bool
     */
    public static function max($value, $parameters)
    {
        return $value <= $parameters[0];
    }

    /**
     * 验证字符串长度是否大于或等于指定值
     *
     * @param  string $value
     * @param  array  $parameters
     * @return bool
     */
    public static function minLength($value, $parameters)
    {
        return Str::len($value) >= $parameters[0];
    }

    /**
     * 验证字符串长度是否小于或等于指定值
     *
     * @param  string $value
     * @param  array  $parameters
     * @return bool
     */
    public static function maxLength($value, $parameters)
    {
        return Str::len($value) <= $parameters[0];
    }

    /**
     * 验证长度是否等于指定的长度
     *
     * @param  string $value
     * @param  array  $parameters
     * @return bool
     */
    public static function length($value, $parameters)
    {
        return Str::len($value) == $parameters[0];
    }

    /**
     * 验证值是否在指定的值里面
     *
     * @param  string $value
     * @param  array  $parameters
     * @return bool
     */
    public static function in($value, $parameters)
    {
        return in_array($value, $parameters);
    }

    /**
     * 验证值是否不在指定的值里面
     *
     * @param  string $value
     * @param  array  $parameters
     * @return bool
     */
    public static function notIn($value, $parameters)
    {
        return !in_array($value, $parameters);
    }

    /**
     * 验证值是否在指定范围之内
     *
     * @param  string $value
     * @param  array  $parameters
     * @return bool
     */
    public static function range($value, $parameters)
    {
        return $value >= $parameters[0] && $value <= $parameters[1];
    }

    /**
     * 验证字符串长度是否在指定范围之内
     *
     * @param  string $value
     * @param  array  $parameters
     * @return bool
     */
    public static function rangelength($value, $parameters)
    {
        return Str::len($value) >= $parameters[0] &&
        Str::len($value) <= $parameters[1];
    }
}
