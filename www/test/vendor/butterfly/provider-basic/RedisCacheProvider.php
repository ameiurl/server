<?php
/**
 * Butterfly : Non-business component library
 *
 * @copyright Copyright (c) 2016 CNCN Information Co., Ltd.
 * All rights reserved.
 */

namespace Butterfly\Provider;

use Butterfly\Container\Container;
use Butterfly\Cache\CacheManager;
use Butterfly\Foundation\ServiceProvider;

/**
 * Redis 缓存服务
 *
 * @author 黄景祥(Joel Huang) <joelhy@gmail.com>
 * @since  1.0
 */
class RedisCacheProvider extends ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function register(Container $container)
    {
        $container['redis'] = function () use ($container) {
            $config       = $container['config']['cache'];
            $cacheManager = new CacheManager($config['default'],
                $config['configs'], $container);

            return $cacheManager->store('redis');
        };
    }
}
