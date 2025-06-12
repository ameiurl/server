<?php
/**
 * Butterfly : Non-business component library
 *
 * @copyright Copyright (c) 2016 CNCN Information Co., Ltd.
 * All rights reserved.
 */

namespace Butterfly\Statical;

use Butterfly\foundation\StaticalProxy;

/**
 * Redis 缓存静态调用类
 *      由于名称 Redis 已经被 Redis 扩展占用，故类名取为 Reds，
 *      **不**是漏写 i 字母
 *
 * @author 黄景祥(Joel Huang) <joelhy@gmail.com>
 * @since  1.0
 */
class Reds extends StaticalProxy
{
    /**
     * {@inheritdoc}
     */
    protected static function getAccesor()
    {
        return 'redis';
    }
}
