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
 * HTTP 405 错误处理程序
 *
 * @author 黄景祥(Joel Huang) <joelhy@gmail.com>
 * @since  1.0
 */
class NotAllowedHandler
{
    /**
     * 调用错误处理程序
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface      $response
     * @param string[]               $methods
     * @return ResponseInterface
     */
    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $methods
    ) : ResponseInterface
    {
        $allow = implode(', ', $methods);
        $body  = sprintf('{"code":405,"msg":"Method not allowed. Must be one of: %s"}',
            $allow);

        $response->withStatus(405)
                 ->withHeader('Content-Type', 'application/json')
                 ->getBody()
                 ->write($body);

        return $response;
    }
}
