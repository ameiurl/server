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
 * Log Proxy
 *
 * @author 黄景祥(Joel Huang) <joelhy@gmail.com>
 * @since  1.0
 */
class Log extends StaticalProxy
{
    /**
     * {@inheritdoc}
     */
    protected static function getAccesor()
    {
        return 'logger';
    }
}
