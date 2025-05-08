<?php
/**
 * Butterfly : Non-business component library
 *
 * @copyright Copyright (c) 2016 CNCN Information Co., Ltd.
 * All rights reserved.
 */

namespace Butterfly\Rest\Routing;

use Butterfly\Http\Request;
use FastRoute\{
    Dispatcher,
    RouteCollector,
    RouteParser\Std as StdParser
};
use Psr\Http\Message\ServerRequestInterface;

class Router
{
    /**
     * 所属的路由集合
     *
     * @var Collection
     */
    protected $collection;

    /**
     * 路由分发程序
     *
     * @var Dispatcher\GroupCountBased
     */
    protected $dispatcher;

    /**
     * 构造函数
     *
     * @param Collection $collection
     */
    public function __construct(Collection $collection)
    {
        $this->collection  = $collection;
        $this->routeParser = new StdParser();
    }

    /**
     * 执行路由操作
     *
     * @param Request $request
     * @return ServerRequestInterface
     */
    public function route(Request $request) : ServerRequestInterface
    {
        $routeInfo = $this->dispatch($request);

        if ($routeInfo[0] === Dispatcher::FOUND) {
            $route = $this->lookupRoute($routeInfo[1]);

            $routeArguments = array_map('urldecode', $routeInfo[2]);
            $route->setArguments($routeArguments);

            $request = $request->withAttribute('route', $route);
        }

        $routeInfo['request'] = [
            $request->getMethod(),
            $request->getVirtualPath()
        ];

        return $request->withAttribute('routeInfo', $routeInfo);
    }

    /**
     * 按标识符查找路由
     *
     * @param string $identifer
     * @return Route
     */
    public function lookupRoute(string $identifer) : Route
    {
        return $this->collection->lookupRoute($identifer);
    }

    /**
     * 分发路由
     *
     * @param Request $request
     * @return array
     */
    public function dispatch(Request $request) : array
    {
        return $this->createDispatcher()->dispatch(
            $request->getMethod(),
            $request->getVirtualPath()
        );
    }

    /**
     * 创建路由分发器
     *
     * @return Dispatcher
     */
    protected function createDispatcher() : Dispatcher
    {
        return $this->dispatcher ?: \FastRoute\simpleDispatcher(function (
            RouteCollector $r
        ) {
            foreach ($this->getRoutes() as $route) {
                $r->addRoute($route->getMethods(), $route->getPattern(),
                    $route->getIdentifier());
            }
        });
    }

    /**
     * 获取路由
     *
     * @return Route[]
     */
    public function getRoutes() : array
    {
        return $this->collection->getRoutes();
    }
}
