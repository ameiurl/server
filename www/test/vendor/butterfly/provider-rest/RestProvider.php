<?php
/**
 * Butterfly : Non-business component library
 *
 * @copyright Copyright (c) 2016 CNCN Information Co., Ltd.
 * All rights reserved.
 */

namespace Butterfly\Provider;

use Butterfly\Container\Container;
use Butterfly\Foundation\ServiceProvider;
use Butterfly\Http\Request;
use Butterfly\Http\Response;
use Butterfly\Rest\Routing\Collection;
use Butterfly\Rest\NotFoundHandler;
use Butterfly\Rest\NotAllowedHandler;

/**
 * HTTP Request 请求数据对象
 *
 * @author 黄景祥(Joel Huang) <joelhy@gmail.com>
 * @since  1.0
 */
class RestProvider extends ServiceProvider
{
    /**
     * 不带 HTTP_ 前缀的 HTTP 头
     *
     * @var array
     */
    protected static $special = [
        'CONTENT_TYPE'    => 1,
        'CONTENT_LENGTH'  => 1,
        'PHP_AUTH_USER'   => 1,
        'PHP_AUTH_PW'     => 1,
        'PHP_AUTH_DIGEST' => 1,
        'AUTH_TYPE'       => 1,
    ];

    /**
     * 从 $_SERVER 数组中提取 HTTP 请求头
     *
     * @param array $server
     * @return array
     */
    protected function headersFromServer($server)
    {
        $data = [];
        foreach ($server as $key => $value) {
            $key = strtoupper($key);
            if (isset(static::$special[$key]) || strpos($key, 'HTTP_') === 0) {
                if (strpos($key, 'HTTP_') === 0) {
                    $key = substr($key, 5);
                }
                $key = strtolower($key);
                $key = str_replace(' ', '-',
                    ucwords(str_replace('_', ' ', $key)));

                $data[$key] = $value;
            }
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function register(Container $container)
    {
        $container['request'] = function () {
            $uri     = \App\Http\Request::createUriFromServer($_SERVER);
            $method  = $_SERVER['REQUEST_METHOD'] ?? 'GET';
            $request = new \App\Http\Request($_SERVER, [], $uri, $method,
                'php://input', $this->headersFromServer($_SERVER),
                $_COOKIE, $_GET);

            if ($method === 'POST' && in_array($request->getMediaType(),
                    [
                        'application/x-www-form-urlencoded',
                        'multipart/form-data'
                    ])
            ) {
                $request = $request->withParsedBody($_POST);
            }

            return $request;
        };

        $container['response'] = function () {
            return new \App\Http\Response();
        };

        $container['routes'] = function () use ($container) {
            return new Collection($container);
        };

        $container['notFoundHandler'] = function () {
            return new NotFoundHandler();
        };

        $container['notAllowedHandler'] = function () {
            return new NotAllowedHandler();
        };
    }
}
