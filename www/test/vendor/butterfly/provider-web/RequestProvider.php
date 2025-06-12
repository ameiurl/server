<?php
/**
 * Butterfly : Non-business component library
 *
 * @copyright Copyright (c) 2016 CNCN Information Co., Ltd.
 * All rights reserved.
 */

namespace Butterfly\Provider;

use Butterfly\Container\Container;
use Butterfly\Foundation\ServiceProvider;
use Butterfly\Web\Request;

/**
 * HTTP Request 请求数据对象
 *
 * @author 黄景祥(Joel Huang) <joelhy@gmail.com>
 * @since  1.0
 */
class RequestProvider extends ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function register(Container $container)
    {
        $container['request'] = function () use ($container) {
            return new Request();
        };
    }
}
