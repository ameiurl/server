<?php
/**
 * Butterfly : Non-business component library
 *
 * @copyright Copyright (c) 2016 CNCN Information Co., Ltd.
 * All rights reserved.
 */

namespace Butterfly\Rest;

use Butterfly\Container\Container;
use Butterfly\Http\Response;
use Butterfly\Foundation\Application as BaseApplication;
use Butterfly\Rest\Routing\{
    Collection,
    Router
};

/**
 * REST API 支持
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
        $request  = $this->container['request'];
        $response = $this->container['response'];

        $routes  = $this->loadRouting();
        $router  = new Router($routes);
        $request = $router->route($request);

        /** @var Response $response */
        $response = $routes($request, $response);
        $this->respond($response);
    }

    /**
     * 输出 HTTP 响应
     *
     * @param Response $response
     */
    public function respond(Response $response)
    {
        if (headers_sent()) {
            throw new \RuntimeException('Headers already sent, unable to send header.');
        }

        // HTTP 状态
        $reasonPhrase = $response->getReasonPhrase();
        header(sprintf(
            'HTTP/%s %d%s',
            $response->getProtocolVersion(),
            $response->getStatusCode(),
            $reasonPhrase ? ' ' . $reasonPhrase : ''
        ));

        // HTTP 响应头
        foreach ($response->getHeaders() as $name => $values) {
            $first = true;
            foreach ($values as $value) {
                header(sprintf('%s: %s', $name, $value), $first);
                $first = false;
            }
        }

        // HTTP Body
        $body = $response->getBody();
        $body->rewind();

        $chunkSize = 4096;
        while (!$body->eof()) {
            echo $body->read($chunkSize);
        }
    }

    /**
     * 获取路由配置
     *
     * @return Collection
     */
    protected function loadRouting() : Collection
    {
        /** @noinspection PhpUnusedParameterInspection
         * @param \Butterfly\Foundation\Application $app
         * @param Container                         $container
         * @param Collection                        $routes
         * @return Collection
         */
        $loader = function ($app, $container, $routes) : Collection {
            include $this->appPath . '/routing/routes.php';
            include $this->appPath . '/routing/middleware.php';

            return $routes;
        };

        return $loader($this, $this->container, $this->container['routes']);
    }
}
