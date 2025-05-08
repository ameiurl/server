<?php
/**
 * Butterfly : Non-business component library
 *
 * @copyright Copyright (c) 2016 CNCN Information Co., Ltd.
 * All rights reserved.
 */

namespace Butterfly\Provider;

use Butterfly\Container\Container;
use Butterfly\Database\{
    Query\Builder,
    ConnectionManager,
    Model
};
use Butterfly\Foundation\ServiceProvider;

/**
 * 数据库服务
 *
 * @author 黄景祥(Joel Huang) <joelhy@gmail.com>
 * @since  1.0
 */
class DatabaseProvider extends ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function register(Container $container)
    {
        $container['database'] = function () use ($container) {
            if (isset($container['pagination'])) {
                Builder::setPaginationFactory(function () use ($container) {
                    return $container['pagination'];
                });
            }

            $config = $container['config']['database'];

            return new ConnectionManager($config['default'],
                $config['configs']);
        };

        Model::setConnectionManager($container['database']);
    }
}
