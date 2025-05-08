<?php
/**
 * Butterfly : Non-business component library
 *
 * @copyright Copyright (c) 2016 CNCN Information Co., Ltd.
 * All rights reserved.
 */

namespace Butterfly\Provider;

use Butterfly\Container\Container;
use Butterfly\Error\ErrorHandler;
use Butterfly\Foundation\ServiceProvider;

/**
 * 错误处理服务
 *
 * @author 黄景祥(Joel Huang) <joelhy@gmail.com>
 * @since  1.0
 */
class ErrorHandlerProvider extends ServiceProvider
{
    /**
     * 设置错误处理程序
     *
     * @param ErrorHandler $errorHandler
     * @param Container    $container
     */
    protected function setLogger($errorHandler, $container)
    {
        /** @var \Butterfly\Config\Config $container ['config'] */
        if (!empty($container['config']['app.error_handler.log_errors'])) {
            $errorHandler->setLogger($container['logger']);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function register(Container $container)
    {
        $errorHandler  = new ErrorHandler();
        $displayErrors = $container['config']['app.error_handler.display_errors'] ?? false;
        $errorHandler->handle('\Throwable',
            function (\Throwable $ex) use ($errorHandler, $container, $displayErrors) {
                $this->setLogger($errorHandler, $container);

                if ($displayErrors) {
                    echo $errorHandler->errorInfo($ex);
                }
            });
        $container['errorHandler'] = $errorHandler;
    }
}
