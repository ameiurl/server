<?php
/**
 * Butterfly : Non-business component library
 *
 * @copyright Copyright (c) 2016 CNCN Information Co., Ltd.
 * All rights reserved.
 */

namespace Butterfly\Foundation;

use Butterfly\Container\Container;

/**
 * Class ServiceProvider
 *
 * @author 黄景祥(Joel Huang) <joelhy@gmail.com>
 * @since  1.0
 */
abstract class ServiceProvider
{
    /**
     * 注册服务
     *
     * @param Container $container
     * @return mixed
     */
    abstract public function register(Container $container);
}
