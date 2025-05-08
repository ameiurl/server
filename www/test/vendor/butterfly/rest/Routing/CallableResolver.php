<?php
/**
 * Butterfly : Non-business component library
 *
 * @copyright Copyright (c) 2016 CNCN Information Co., Ltd.
 * All rights reserved.
 */

namespace Butterfly\Rest\Routing;

use Butterfly\Container\Container;

/**
 * 把 'class:method' 字符串解析成 call_user_func 可调用的
 *
 * @author 黄景祥(Joel Huang) <joelhy@gmail.com>
 * @since  1.0
 */
class CallableResolver
{
    /**
     * 依赖注入容器
     *
     * @var Container
     */
    protected $container;

    /**
     * 构造函数
     *
     * @param Container $container 依赖注入容器
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * 调用解析程序
     *
     * @param callable|string $toResolve
     * @return callable
     */
    public function __invoke($toResolve) : callable
    {
        $resolved = $toResolve;
        if (is_string($toResolve) && strpos($toResolve, ':') !== false) {
            list($class, $method) = explode(':', $toResolve, 2);
            if (isset($this->container[$class])) {
                $resolved = [$this->container[$class], $method];
            } else {
                if (!class_exists($class)) {
                    throw new \RuntimeException(sprintf('Callable %s does not exist', $class));
                }

                $obj = new $class();

                // 设置依赖注入容器
                if (method_exists($obj, 'setContainer')) {
                    /** @var \Butterfly\Container\ContainerAwareTrait $obj */
                    $obj->setContainer($this->container);
                }

                $resolved = [$obj, $method];
            }
        }

        if (!is_callable($resolved)) {
            throw new \RuntimeException(sprintf('%s is not resolvable', $toResolve));
        }

        return $resolved;
    }
}
