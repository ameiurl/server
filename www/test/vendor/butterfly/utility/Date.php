<?php
/**
 * Butterfly : Non-business component library
 *
 * @copyright Copyright (c) 2016 CNCN Information Co., Ltd.
 * All rights reserved.
 */

namespace Butterfly\Utility;

use DateTime;

/**
 * Date - 日期类
 *
 * @author 黄景祥(Joel Huang) <joelhy@gmail.com>
 * @since  1.0
 */
class Date
{
    /**
     * 验证是否是有效的日期
     *     from: http://php.net/manual/en/function.checkdate.php#113205
     *
     * @param  string $date   要验证的日期
     * @param  string $format 日期的格式
     * @return bool
     */
    public static function isValid($date, $format = 'Y-m-d H:i:s')
    {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }
}
