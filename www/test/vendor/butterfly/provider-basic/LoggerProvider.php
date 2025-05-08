<?php
/**
 * Butterfly : Non-business component library
 *
 * @copyright Copyright (c) 2016 CNCN Information Co., Ltd.
 * All rights reserved.
 */

namespace Butterfly\Provider;

use Butterfly\Container\Container;
use Butterfly\Log\FileLogger;
use Butterfly\Foundation\ServiceProvider;

/**
 * 日志记录服务
 *
 * @author 黄景祥(Joel Huang) <joelhy@gmail.com>
 * @since  1.0
 */
class LoggerProvider extends ServiceProvider
{
    /**
     * 支持的日志引擎
     *
     * @var array
     */
    protected $engines = [
        'file' => FileLogger::class,
    ];

    /**
     * {@inheritdoc}
     */
    public function register(Container $container)
    {
        $container['logger'] = function () use ($container) {
            $config    = $container['config']['app.log'];
            $logEngine = $config['engine'] ?? 'file';
            if (!isset($this->engines[$logEngine])) {
                $tpl = 'Unable to resolve log engine [ %s ].';
                throw new \RuntimeException(vsprintf($tpl, [$logEngine]));
            }

            $logger = $this->engines[$logEngine];

            return new $logger($config);
        };
    }
}
