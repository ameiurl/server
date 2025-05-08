<?php
/**
 * Butterfly : Non-business component library
 *
 * @copyright Copyright (c) 2016 CNCN Information Co., Ltd.
 * All rights reserved.
 */

namespace Butterfly\Web;

use Butterfly\Container\ContainerAwareTrait;

/**
 * 控制器
 *
 * @author 黄景祥(Joel Huang) <joelhy@gmail.com>
 * @since  1.0
 * @property \Butterfly\View\ViewInterface $view
 */
abstract class Controller
{
    use ContainerAwareTrait;
}
