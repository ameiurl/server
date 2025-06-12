<?php
/**
 * Butterfly : Non-business component library
 *
 * @copyright Copyright (c) 2016 CNCN Information Co., Ltd.
 * All rights reserved.
 */

namespace Butterfly\Console;

use Butterfly\Foundation\Application as BaseApplication;

/**
 * 命令行应用
 *
 * @author 黄景祥(Joel Huang) <joelhy@gmail.com>
 * @since  1.0
 */
class Application extends BaseApplication
{
    /**
     * {@inheritdoc}
     */
    public function run()
    {
        $launcher = new Launcher($this->container);
        $launcher->run(array_slice($_SERVER['argv'], 1));
    }
}
