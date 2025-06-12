<?php
/**
 * Butterfly : Non-business component library
 *
 * @copyright Copyright (c) 2016 CNCN Information Co., Ltd.
 * All rights reserved.
 */

namespace Butterfly\Provider;

use Butterfly\{
    Container\Container,
    Foundation\ServiceProvider,
    View\PhpView,
    View\SmartyView
};

/**
 * 视图服务
 *
 * @author 黄景祥(Joel Huang) <joelhy@gmail.com>
 * @since  1.0
 */
class ViewProvider extends ServiceProvider
{
    /**
     * 支持的视图引擎
     *
     * @var array
     */
    protected $engines = [
        'php'       => PhpView::class,
        'smarty'    => SmartyView::class,
    ];

    /**
     * {@inheritdoc}
     */
    public function register(Container $container)
    {
        $container['view'] = function () use ($container) {
            $config = $container['config']['app.view'];
            $viewEngine = $config['engine'] ?? 'php';
            if (!isset($this->engines[$viewEngine])) {
                $tpl = 'Unable to resolve view engine [ %s ].';
                throw new \RuntimeException(vsprintf($tpl, [$viewEngine]));
            }

            $view = $this->engines[$viewEngine];

            return new $view($config);
        };
    }
}
