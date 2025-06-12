<?php
/**
 * Butterfly : Non-business component library
 *
 * @copyright Copyright (c) 2016 CNCN Information Co., Ltd.
 * All rights reserved.
 */

namespace Butterfly\Rest;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * 中间件运行程序
 *
 * @author 黄景祥(Joel Huang) <joelhy@gmail.com>
 * @since  1.0
 */
class Runner
{
    /**
     * 中间件队列
     *
     * @var \SplQueue
     */
    protected $queue;

    /**
     * $handler 解析成可调用的函数/方法/可i nvoke 的类
     *
     * @var callable
     */
    protected $resolver;

    /**
     * Runner constructor.
     *
     * @param \SplQueue|array $queue
     * @param callable|null   $resolver
     * @throws \RuntimeException
     */
    public function __construct($queue = null, callable $resolver = null)
    {
        if (is_array($queue)) {
            $this->queue = new \SplQueue();
            $this->add($queue);
        } else {
            if (!($queue instanceof \SplQueue)) {
                throw new \RuntimeException('%s: $qeueu must be an SplQueue');
            }
            $this->queue = $queue;
        }

        $this->resolver = $resolver;
    }

    /**
     * 执行中间件运行程序
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
        $item       = $this->queue->dequeue();
        $middleware = $this->resolve($item);
        return $middleware($request, $response, $this);
    }

    /**
     * 中间件入队列操作
     *
     * @param mixed $item
     * @return $this
     */
    public function add($item)
    {
        if (is_array($item)) {
            foreach ($item as $it) {
                $this->queue->enqueue($it);
            }
        } else {
            $this->queue->enqueue($item);
        }

        return $this;
    }

    /**
     * 解析中间件处理程序
     *
     * @param mixed $item 中间件
     * @return \callable
     */
    public function resolve($item)
    {
        if (!$item) {
            return function (
                ServerRequestInterface $request,
                ResponseInterface $response,
                callable $next
            ) : ResponseInterface {
                return $response;
            };
        }

        if (!$this->resolver) {
            return $item;
        }

        return call_user_func($this->resolver, $item);
    }
}
