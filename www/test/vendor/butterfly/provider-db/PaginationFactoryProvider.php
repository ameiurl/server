<?php
/**
 * Butterfly : Non-business component library
 *
 * @copyright Copyright (c) 2016 CNCN Information Co., Ltd.
 * All rights reserved.
 */

namespace Butterfly\Provider;

use Butterfly\Container\Container;
use Butterfly\Pagination\PaginationFactory;
use Butterfly\Foundation\ServiceProvider;

/**
 * 分页工厂服务
 *
 * @author 黄景祥(Joel Huang) <joelhy@gmail.com>
 * @since  1.0
 */
class PaginationFactoryProvider extends ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function register(Container $container)
    {
        $container['pagination'] = function () use ($container) {
            $config = $container['config']['pagination'];
            return new PaginationFactory($config);
        };
    }
}
