<?php
/**
 * Butterfly : Non-business component library
 *
 * @copyright Copyright (c) 2016 CNCN Information Co., Ltd.
 * All rights reserved.
 */

namespace Butterfly\Http;

use RuntimeException;
use Zend\Diactoros\ServerRequest;

class Request extends ServerRequest
{
    /** 解析过的 HTTP body 内容
     *
     * @var
     */
    protected $bodyParsed;

    /**
     * 受信任的代理 IP
     *
     * @var array
     */
    protected static $trustedProxies = [
        '127.0.0.1',
    ];

    /**
     * 获取权限控制参数
     *
     * @return array
     */
    public function extraArgs()
    {
        return $this->getAttribute('extra_args', []);
    }

    /**
     * 获取 HTTP Ａuthorization 请求头
     *
     * @return string|null
     */
    public function accessToken()
    {
        $authorization = current($this->getHeader('Authorization'));
        if (!$authorization) {
            if (function_exists('apache_request_headers')) {
                $headers       = apache_request_headers();
                $authorization = $headers['Authorization'] ?? null;
            }
        }

        if (!$authorization) {
            return null;
        }

        if (stripos($authorization, 'Bearer') !== 0) {
            return null;
        }

        return substr($authorization, strlen('Bearer '));
    }

    // 以下是简单的获取请求参数的方法名，便于开发人员使用
    // get() 方法用于获取 $_GET 数据
    // input() 方法用于获取 HTTP POST/PUT 请求的 HTTP Body 数据
    // server() 方法用于获取 $_SERVER 数据
    // 不直接使用 $_GET, $_POST, $_SERVER 是为了方便写测试用例，以及以后为自动过滤用户非法输入做准备
    //
    // 使用示例：
    // $params = $req->get();  // 等价于 $params = $_GET;
    // $params = $req->get('foo', 'bar');  // 等价于 $params = $_GET['foo'] ?? 'bar';

    // $params = $req->input();  // post/put 请求均可用此方式
    // $params = $req->input('foo', 'bar');
    //
    // $params = $req->server();  // 等价于 $params = $_SERVER;
    // $params = $req->server('foo', 'bar');  // 等价于 $params = $_SERVER['foo'] ?? 'bar';

    /**
     * 获取 HTTP GET 请求参数
     *
     * @param array ...$params
     * @return array|null
     */
    public function get(...$params)
    {
        if (count($params) === 0) {
            return $this->getQueryParams();
        }

        return $this->getQueryParam(...$params);
    }

    /**
     * 获取请求数据类型
     *
     * @return string|null
     */
    public function getMediaType()
    {
        $contentType = $this->getHeader('Content-Type')[0] ?? null;
        if ($contentType) {
            $parts = preg_split('/\s*[;,]\s*/', $contentType);
            return strtolower($parts[0]);
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getParsedBody()
    {
        if ($this->bodyParsed !== null) {
            return $this->bodyParsed;
        }

        $this->bodyParsed = parent::getParsedBody();
        if ($this->bodyParsed !== null) {
            return $this->bodyParsed;
        }
        
        $mediaType = $this->getMediaType();
        switch ($mediaType) {
            case 'application/x-www-form-urlencoded':
                parse_str($this->rawBody(), $this->bodyParsed);
                break;
            case 'application/json':
            case 'text/json':
                $this->bodyParsed = json_decode($this->rawBody(), true);
                if (!is_array($this->bodyParsed)) {
                    $this->bodyParsed = [];
                }
                break;
            default:
                $this->bodyParsed = [];
        }
        
        return $this->bodyParsed;
    }

    /**
     * 获取 HTTP POST/PUT 请求参数
     *
     * @param array ...$params
     * @return array|string|int
     */
    public function input(...$params)
    {
        if (count($params) === 0) {
            return $this->getParsedBody();
        } else {
            return $this->getParsedBodyParam(...$params);
        }
    }

    /**
     * 获取 $_SERVER 数据
     *
     * @param array ...$params
     * @return array|mixed
     */
    public function server(...$params)
    {
        if (count($params) === 0) {
            return $this->getServerParams();
        }

        return $this->getServerParam(...$params);
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
            if (in_array($remoteAddr, static::$trustedProxies)) {
                return $clientIp;
            }
        }
        if (filter_var($forward, FILTER_VALIDATE_IP)) {
            if (in_array($remoteAddr, static::$trustedProxies)) {
                return $forward;
            }
        }

        return $remoteAddr;
    }

    /**
     * 获取 $_GET 的一个键值对应的值
     *
     * @param string      $key
     * @param string|null $default
     * @return string|null
     */
    public function getQueryParam($key, $default = null)
    {
        return $this->getQueryParams()[$key] ?? $default;
    }

    /**
     * 获取 $_SERVER 的一个键值对应的值
     *
     * @param string      $key
     * @param string|null $default
     * @return string|null
     */
    public function getServerParam($key, $default = null)
    {
        return $this->getServerParams()[$key] ?? $default;
    }

    /**
     * 获取 $_COOKIE 的一个键值对应的值
     *
     * @param string      $key
     * @param string|null $default
     * @return string|null
     */
    public function getCookieParam($key, $default = null)
    {
        return $this->getCookieParams()[$key] ?? $default;
    }

    /**
     * 获取 $this->parsedBody 的一个键值对应的值
     *
     * @param string      $key
     * @param string|null $default
     * @return string|null
     */
    public function getParsedBodyParam($key, $default = null)
    {
        $parsedBody = $this->getParsedBody();
        if (is_array($parsedBody)) {
            return $parsedBody[$key] ?? $default;
        } elseif (is_object($parsedBody)) {
            return property_exists($parsedBody, $key) ?
                $parsedBody->$key : $default;
        } else {
            return $default;
        }
    }

    /**
     * 从 $_SERVER 中拼接完整的 URI
     *
     * @param array $server
     * @return string
     */
    public static function createUriFromServer($server)
    {
        $schema  = empty($server['HTTPS']) ? 'http' : 'https';

        return $schema . '://' . $server['HTTP_HOST'] . $server['REQUEST_URI'];
    }

    /**
     * 获取基础路径后的路径
     *  例如所有路由是从 /api/index.php 路由的，则从 REQUEST_URI 中去掉 /api
     */
    public function getVirtualPath()
    {
        static $virtualPath;

        if ($virtualPath === null) {
            $path = $this->getUri()->getPath();
            $base = dirname($this->getServerParam('SCRIPT_NAME'));
            if ($base === '/') {
                $virtualPath = $path;
            } else {
                $virtualPath = substr($path, strlen($base));
            }
        }

        return $virtualPath;
    }

    /**
     * 获取 HTTP 请求的原始 Body
     *
     * @return string
     */
    public function rawBody()
    {
        return $this->getBody()->getContents();
    }
}
