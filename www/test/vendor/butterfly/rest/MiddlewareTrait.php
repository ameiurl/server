<?php
/**
 * Butterfly : Non-business component library
 *
 * @copyright Copyright (c) 2016 CNCN Information Co., Ltd.
 * All rights reserved.
 */

namespace Butterfly\Rest;

/**
 * 中间件 trait
 *
 * @author 黄景祥(Joel Huang) <joelhy@gmail.com>
 * @since  1.0
 */
trait MiddlewareTrait
{
    /**
     * 中间件数组
     *
     * @var array
     */
    protected $middleware = [];

    /**
     * 添加中间件
     *
     * @param callable|string $middleware
     * @return $this
     */
    public function add($middleware)
    {
        $this->middleware[] = $middleware;
        return $this;
    }

    /**
     * 获取中间件数组
     *
     * @return array
     */
    public function getMiddleware() : array
    {
        return $this->middleware;
    }
}
