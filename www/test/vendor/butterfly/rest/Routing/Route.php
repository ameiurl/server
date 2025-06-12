<?php
/**
 * Butterfly : Non-business component library
 *
 * @copyright Copyright (c) 2016 CNCN Information Co., Ltd.
 * All rights reserved.
 */

namespace Butterfly\Rest\Routing;

use Butterfly\Rest\MiddlewareTrait;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;

/**
 * 路由定义
 *
 * @author 黄景祥(Joel Huang) <joelhy@gmail.com>
 * @since  1.0
 */
class Route
{
    use MiddlewareTrait;

    /**
     * HTTP 请求方法
     *
     * @var string[]
     */
    protected $methods;

    /**
     * 路由规则
     *
     * @var string
     */
    protected $pattern;

    /**
     * 路由处理程序
     *
     * @var string|Callable
     */
    protected $handler;

    /**
     * 路由名称
     *
     * @var string
     */
    protected $name;

    /**
     * 路由参数
     *  例如： /hello/{name:\w+} 中的 name 的值
     *
     * @var array
     */
    protected $arguments = [];

    /**
     * 路由前缀
     *
     * @var string
     */
    protected $prefix = '';

    /**
     * 路由标识符
     *
     * @var string
     */
    protected $identifier;

    /**
     * 路由所属的路由群组
     *
     * @var Group[]
     */
    protected $groups;

    /**
     * 构造函数
     *
     * @param string[]        $methods
     * @param string          $pattern
     * @param callable|string $handler
     * @param Group[]         $groups
     * @param int             $identifier
     */
    public function __construct(
        array $methods,
        string $pattern,
        $handler,
        array $groups,
        int $identifier = 0
    ) {
        $this->methods    = $methods;
        $this->pattern    = $pattern;
        $this->handler    = $handler;
        $this->groups     = $groups;
        $this->identifier = 'route' . $identifier;
    }

    /**
     * 设置路由名称
     *
     * @param string $name
     * @return Route
     */
    public function setName(string $name) : Route
    {
        $this->name = $name;

        return $this;
    }

    /**
     * 获取路由名称
     *
     * @return string|null
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * 获取路由关联的 HTTP 方法
     *
     * @return string[]
     */
    public function getMethods() : array
    {
        return $this->methods;
    }

    /**
     * 获取路由规则
     *
     * @return string
     */
    public function getPattern() : string
    {
        return $this->pattern;
    }

    /**
     * 获取路由解析处理程序
     *
     * @param Callable|string $resolver
     * @return \Closure
     */
    public function getCallable($resolver) : \Closure
    {
        $handler = $resolver($this->handler);
        return function (
            ServerRequestInterface $request,
            Response $response,
            $next
        ) use ($handler) {
            return call_user_func($handler, $request, $response,
                $this->arguments);
        };
    }

    /**
     * 获取路由处理程序
     *
     * @return Callable|string
     */
    public function getHandler()
    {
        return $this->handler;
    }

    /**
     * 获取路由标识符
     *
     * @return string
     */
    public function getIdentifier() : string
    {
        return $this->identifier;
    }

    /**
     * 设置路由参数
     *
     * @param array $arguments
     */
    public function setArguments(array $arguments)
    {
        $this->arguments = $arguments;
    }

    /**
     * 获取关联的路由群组的中间件
     *
     * @return array
     */
    public function getGroupMiddleware() : array
    {
        $middleware = [];
        foreach ($this->groups as $group) {
            $middleware = array_merge($middleware, $group->getMiddleware());
        }

        return $middleware;
    }
}
