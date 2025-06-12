<?php
/**
 * Butterfly : Non-business component library
 *
 * @copyright Copyright (c) 2016 CNCN Information Co., Ltd.
 * All rights reserved.
 */

namespace Butterfly\Rest\Routing;

use Butterfly\Rest\MiddlewareTrait;

/**
 * 路由群组
 *
 * @author 黄景祥(Joel Huang) <joelhy@gmail.com>
 * @since  1.0
 */
class Group
{
    use MiddlewareTrait;

    /**
     * 路由规则
     *
     * @var string
     */
    protected $pattern;

    /**
     * 路由处理程序
     *
     * @var callable|string
     */
    protected $handler;

    /**
     * 构造函数
     *
     * @param string $pattern
     * @param callable|string $handler
     */
    public function __construct(string $pattern, $handler)
    {
        $this->pattern = $pattern;
        $this->handler = $handler;
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
     * 获取路由处理程序
     *
     * @return callable|string
     */
    public function getHandler()
    {
        return $this->handler;
    }
}
