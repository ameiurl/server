<?php
/**
 * Butterfly : Non-business component library
 *
 * @copyright Copyright (c) 2016 CNCN Information Co., Ltd.
 * All rights reserved.
 */

namespace Butterfly\Rest\Routing;

use Butterfly\Container\Container;
use Butterfly\Container\ContainerAwareTrait;
use FastRoute\Dispatcher;
use Butterfly\Rest\{
    MiddlewareTrait,
    NotAllowedHandler,
    NotFoundHandler,
    Runner
};
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;

class Collection
{
    use MiddlewareTrait;
    use ContainerAwareTrait;

    /**
     * 路由群组
     *
     * @var Group[]
     */
    protected $groups = [];

    /**
     * 当前注册的路由
     *
     * @var Route[]
     */
    protected $routes = [];

    /**
     * 有赋予名称的路由
     *
     * @var Route[]
     */
    protected $namedRoutes = [];

    /**
     * 路由计数器
     *
     * @var int
     */
    protected $routeCounter = 0;

    /**
     * Callable 解析程序
     *
     * @var CallableResolver
     */
    protected $resolver;

    /**
     * 构造函数
     *
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->resolver  = new CallableResolver($this->container);
    }

    /**
     * 添加 HTTP GET 路由
     *
     * @param string $pattern 路由规则
     * @param mixed  $handler 路由处理程序
     * @param string $name    路由名称
     * @return Route
     */
    public function get(string $pattern, $handler, $name = null) : Route
    {
        return $this->map(['GET'], $pattern, $handler, $name);
    }

    /**
     * 添加 HTTP POST 路由
     *
     * @param string $pattern 路由规则
     * @param mixed  $handler 路由处理程序
     * @param string $name    路由名称
     * @return Route
     */
    public function post(string $pattern, $handler, $name = null) : Route
    {
        return $this->map(['POST'], $pattern, $handler, $name);
    }

    /**
     * 添加 HTTP PUT 路由
     *
     * @param string $pattern 路由规则
     * @param mixed  $handler 路由处理程序
     * @param string $name    路由名称
     * @return Route
     */
    public function put(string $pattern, $handler, $name = null) : Route
    {
        return $this->map(['PUT'], $pattern, $handler, $name);
    }

    /**
     * 添加 HTTP PATCH 路由
     *
     * @param string $pattern 路由规则
     * @param mixed  $handler 路由处理程序
     * @param string $name    路由名称
     * @return Route
     */
    public function patch(string $pattern, $handler, $name = null) : Route
    {
        return $this->map(['PATCH'], $pattern, $handler, $name);
    }

    /**
     * 添加 HTTP DELETE 路由
     *
     * @param string $pattern 路由规则
     * @param mixed  $handler 路由处理程序
     * @param string $name    路由名称
     * @return Route
     */
    public function delete(string $pattern, $handler, $name = null) : Route
    {
        return $this->map(['DELETE'], $pattern, $handler, $name);
    }

    /**
     * 添加 HTTP OPTIONS 路由
     *
     * @param string $pattern 路由规则
     * @param mixed  $handler 路由处理程序
     * @param string $name    路由名称
     * @return Route
     */
    public function options(string $pattern, $handler, $name = null) : Route
    {
        return $this->map(['OPTIONS'], $pattern, $handler, $name);
    }

    /**
     * 添加 HTTP GET 路由
     *
     * @param string          $pattern 路由规则
     * @param callable|string $handler 路由处理程序
     * @param string          $name    路由名称
     * @return Route
     */
    public function any(string $pattern, $handler, $name = null) : Route
    {
        return $this->map(['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'options'],
            $pattern, $handler, $name);
    }

    /**
     * 添加多种 HTTP 请求方法的路由
     *
     * @param string[]        $methods HTTP GET/POST/PUT/DELETE/PATCH/OPTIONS 数组
     * @param string          $pattern 路由规则
     * @param callable|string $handler 路由处理程序
     * @param string|null     $name    路由名称
     * @return Route
     */
    public function map(
        array $methods,
        string $pattern,
        $handler,
        $name = null
    ) : Route
    {
        if ($this->groups) {
            $pattern = $this->processGroups() . $pattern;
        }

        $methods = array_map('strtoupper', $methods);

        $route = new Route($methods, $pattern, $handler, $this->groups,
            $this->routeCounter);

        $this->routes[$route->getIdentifier()] = $route;
        $this->routeCounter++;

        if ($name !== null) {
            $this->namedRoutes[$name] = $route;
            $route->setName($name);
        }

        return $route;
    }

    /**
     * 获取路由群组的路由规则前缀
     *
     * @return string
     */
    protected function processGroups() : string
    {
        $pattern = '';
        foreach ($this->groups as $routeGroup) {
            $pattern .= $routeGroup->getPattern();
        }

        return $pattern;
    }

    /**
     * 开始一个路由群组
     *
     * @param string          $pattern 路由规则
     * @param callable|string $handler 路由处理程序
     * @return Group
     */
    public function group($pattern, $handler) : Group
    {
        // 获取 callable 的 $handler
        $resolver = $this->resolver;
        $handler  = $resolver($handler);
        if ($handler instanceof \Closure) {
            $handler = $handler->bindTo($this);
        }

        $group          = new Group($pattern, $handler);
        $this->groups[] = $group;
        $handler($this);
        array_pop($this->groups);
        return $group;
    }

    /**
     * 根据路由标识符查找路由
     *
     * @param int $identifer
     * @return Route
     */
    public function lookupRoute($identifer) : Route
    {
        if (isset($this->routes[$identifer])) {
            return $this->routes[$identifer];
        }

        throw new RuntimeException('Route not found');
    }

    /**
     * 根据路由名称查找路由
     *
     * @param string $name
     * @return Route
     */
    public function getNamedRoute(string $name) : Route
    {
        if (isset($this->namedRoutes[$name])) {
            return $this->namedRoutes[$name];
        }

        foreach ($this->routes as $route) {
            if ($route->getName() === $name) {
                $this->namedRoutes[$name] = $route;
                return $route;
            }
        }

        $tpl = '%s(): No route named [ %s ] has been defined.';
        throw new RuntimeException(vsprintf($tpl, [__METHOD__, $name]));
    }

    /**
     * 执行路由
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface      $response
     * @return ResponseInterface
     */
    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response
    ) : ResponseInterface
    {
        $routeInfo = $request->getAttribute('routeInfo');
        if ($routeInfo[0] === Dispatcher::FOUND) {
            /** @var Route $route */
            $route = $request->getAttribute('route');

            $middleware = array_merge(
                $this->getMiddleware(),
                $route->getGroupMiddleware(),
                $route->getMiddleware()
            );
            $runner     = new Runner($middleware, $this->resolver);
            $handler    = $route->getCallable($this->resolver);
            $runner->add($handler);

            return $runner($request, $response);
        } elseif ($routeInfo[0] === Dispatcher::METHOD_NOT_ALLOWED) {
            /** @var NotAllowedHandler $handler */
            $handler = $this->container['notAllowedHandler'];
            return $handler($request, $response, $routeInfo[1]);
        } else {
            /** @var NotFoundHandler $handler */
            $handler = $this->container['notFoundHandler'];
            return $handler($request, $response);
        }
    }

    /**
     * 获取所有路由
     *
     * @return Route[]
     */
    public function getRoutes()
    {
        return $this->routes;
    }
}
