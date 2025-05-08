<?php
/**
 * Butterfly : Non-business component library
 *
 * @copyright Copyright (c) 2016 CNCN Information Co., Ltd.
 * All rights reserved.
 */

namespace Butterfly\Web;

/**
 * HTTP 请求类
 *
 * @author 黄景祥(Joel Huang) <joelhy@gmail.com>
 * @since  1.0
 */
class Request
{
    /**
     * $_GET 数据
     *
     * @var array
     */
    protected $get;

    /**
     * $_POST 数据
     *
     * @var array
     */
    protected $post;

    /**
     * $_COOKIE 数据
     *
     * @var array
     */
    protected $cookies;

    /**
     * $_SERVER 数据
     *
     * @var array
     */
    protected $server;

    /**
     * $_FILES 数据
     *
     * @var array
     */
    protected $files;

    /**
     * HTTP Body 内容
     *
     * @var string|null
     */
    protected $body;

    /**
     * 解析过后的 HTTP Body 内容
     *
     * @var array
     */
    protected $parsedBody;

    /**
     * 构造函数
     *
     * @param array $request
     */
    public function __construct(array $request = [])
    {
        $this->get     = $request['get'] ?? $_GET;
        $this->post    = $request['post'] ?? $_POST;
        $this->cookies = $request['cookies'] ?? $_COOKIE;
        $this->server  = $request['server'] ?? $_SERVER;
        $this->files   = $request['files'] ?? $_FILES;
        $this->body    = $request['body'] ?? null;
    }

    /**
     * 获取 $_GET 请求数据
     *
     * @param string $key
     * @param string $default
     * @return mixed
     */
    public function get($key = null, $default = null)
    {
        return $key === null ? $this->get : ($this->get[$key] ?? $default);
    }

    /**
     * 获取 $_POST 请求数据
     *
     * @param string $key
     * @param string $default
     * @return mixed
     */
    public function post($key = null, $default = null)
    {
        return $key === null ? $this->post : ($this->post[$key] ?? $default);
    }

    /**
     * 获取 HTTP 请求的 Body 内容
     *
     * @return string
     */
    public function body()
    {
        if ($this->body === null) {
            $this->body = file_get_contents('php://input');
        }

        return $this->body;
    }

    /**
     * 获取 HTTP 请求的 Body 内容
     *
     * @param string $key
     * @param string $default
     * @return string|null
     */
    public function server($key = null, $default = null)
    {
        return $key === null ? $this->server : ($this->server[$key] ?? $default);
    }

    /**
     * 获取 PUT/DELETE/PATCH HTTP Body 请求数据
     *
     * @param string $key
     * @param string $default
     * @return mixed
     */
    public function input($key = null, $default = null)
    {
        if ($this->parsedBody === null) {
            $contentType = $this->server('CONTENT_TYPE');
            switch ($contentType) {
                case 'application/x-www-form-urlencoded':
                    parse_str($this->body(), $this->parsedBody);
                    break;
                case 'text/json':
                case 'application/json':
                    $this->parsedBody = json_decode($this->body(), true);
                    break;
                default:
                    $this->parsedBody = [];
            }
        }

        return $key === null ? $this->parsedBody :
            ($this->parsedBody[$key] ?? $default);
    }

    /**
     * 获取 IP 地址
     *
     * @param bool $trustProxies 是否信任代理提供的客户端 IP 地址
     * @return string
     */
    public function ip($trustProxies = false)
    {
        $remoteAddr = $this->server('REMOTE_ADDR');
        if (!$trustProxies) {
            return $remoteAddr;
        }

        // ref. http://stackoverflow.com/questions/13646690/how-to-get-real-ip-from-visitor
        $clientIp = $this->server('HTTP_CLIENT_IP');
        $forward  = $this->server('HTTP_X_FORWARDED_FOR');
        if (filter_var($clientIp, FILTER_VALIDATE_IP)) {
            return $clientIp;
        }
        if (filter_var($forward, FILTER_VALIDATE_IP)) {
            return $forward;
        }

        return $remoteAddr;
    }

    /**
     * 是否是 ajax 请求
     *
     * @return bool
     */
    public function isAjax()
    {
        return $this->server('HTTP_X_REQUESTED_WITH') === 'XMLHttpRequest';
    }

    /**
     * 获取当前的 HTTP 请求方法
     *
     * @return string
     */
    public function method()
    {
        return $this->server('REQUEST_METHOD', 'GET');
    }

    /**
     * 是否请求来自命令行
     *
     * @return bool
     */
    public function isCli()
    {
        return defined('STDIN');
    }
}
