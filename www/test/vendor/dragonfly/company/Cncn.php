<?php
/**
 * Dragonfly : Business component library of Firefly Framework
 *
 * @copyright Copyright (c) 2016 CNCN Information Co., Ltd.
 * All rights reserved.
 */

namespace Dragonfly\Company;

/**
 * 欣欣旅游公司相关信息
 *
 * @author 黄景祥(Joel Huang) <joelhy@gmail.com>
 * @since  1.0
 */
class Cncn
{
    /**
     * 获取公司的外网 IP
     *
     * @return array
     */
    public static function ips()
    {
        return [
            '117.25.182.10',
            '120.42.46.10',
            '110.80.36.234',
            '27.154.234.150',
            '120.41.7.98',
        ];
    }

    /**
     * 是否是来自公司 IP 的请求
     *
     * @param  string $ip IP 地址，可选，为空则默认为 $_SERVER['REMOTE_ADDR']
     * @return bool
     */
    public static function fromCompany($ip = null)
    {
        $ip      = $ip ?: $_SERVER['REMOTE_ADDR'];
        $cncnIps = self::ips();

        return in_array($ip, $cncnIps);
    }
}
