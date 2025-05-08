<?php
/**
 * Butterfly : Non-business component library
 *
 * @copyright Copyright (c) 2016 CNCN Information Co., Ltd.
 * All rights reserved.
 */

namespace Butterfly\Utility;

/**
 * Ip 操作类 （支持 IPv6）
 *
 * @author 黄景祥(Joel Huang) <joelhy@gmail.com>
 * @since  1.0
 */
class Ip
{
    // 禁止 new Ip()
    private function __construct()
    {
    }

    /**
     * 是否是有效的 IP 地址
     *
     * @param  string $ip IP 地址，可选，为空则默认为 $_SERVER['REMOTE_ADDR']
     * @return bool
     */
    public static function isValid($ip = null)
    {
        $ip = $ip ?: $_SERVER['REMOTE_ADDR'];

        return filter_var($ip, FILTER_VALIDATE_IP) !== false;
    }

    /**
     * 是否是内网 IP
     *
     * @param  string $ip IP 地址，可选，为空则默认为 $_SERVER['REMOTE_ADDR']
     * @return bool
     */
    public static function isPrivate($ip = null)
    {
        $ip = $ip ?: $_SERVER['REMOTE_ADDR'];

        return static::isValid($ip) && !static::isPublic($ip);
    }

    /**
     * 是否是公网 IP
     *
     * @param  string $ip IP 地址，可选，为空则默认为 $_SERVER['REMOTE_ADDR']
     * @return bool
     */
    public static function isPublic($ip = null)
    {
        $ip = $ip ?: $_SERVER['REMOTE_ADDR'];

        return filter_var($ip, FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false;
    }
}
