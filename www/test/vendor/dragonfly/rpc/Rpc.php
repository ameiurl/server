<?php
namespace Dragonfly\Rpc;

ini_set('yar.connect_timeout', 2000);  // 连接超时时间
ini_set('yar.timeout', 10000);  // 执行超时时间

use Yar_Client;
use Yar_Client_Transport_Exception;

class Rpc
{
    /**
     * RPC 服务的 host
     *      RPC 服务只在内网监听，故需提供IP地址
     *
     * @var string
     */
    protected $host;

    /**
     * 重试次数
     *
     * @var int
     */
    protected $retries = 1;

    public function __construct()
    {
        if (getenv('APP_ENV') === 'prod') {
            $this->host = 'http://192.168.1.27:810';
        } else {
            if ($_SERVER['SERVER_ADDR'] === '172.16.0.235') {
                $this->host = 'http://172.16.0.235:810';
            }elseif (substr($_SERVER['SERVER_ADDR'], 0, 7) === '172.18.') {
                $this->host = 'http://172.16.0.100:810';
            } else {
                $this->host = 'http://192.168.9.12:810';
            }
            //$this->host = 'http://' . $_SERVER['SERVER_ADDR'] . ':810';
        }
    }

    /**
     * 接口请求函数
     *
     * 使用示例
     *
     * ```php
     * <?php
     * $rpc = new Cncn\Rpc();
     * $res = $rpc->call('weixin', 'tplMsg', [$tplCode, $touser, $url, $data]);
     * ?>
     * ```
     *
     * @param string $action 调用的类
     * @param string $method 对应的方法
     * @param array  $params post 传输参数
     * @return array
     * @throws $ex
     */
    public function call($action, $method, $params = [])
    {
        if (empty($action) || empty($method)) {
            return [];
        }
        $yar = new Yar_Client($this->host . '/?action=' . $action);
        do {
            try {
                return $yar->__call($method, $params);
            } catch (Exception $ex) {
                if (!($ex instanceof Yar_Client_Transport_Exception)) {
                    // 只有网络连接出问题才需要重试
                    break;
                }
                $this->retries--;
            }
        } while ($this->retries >= 0);

        throw $ex;  // 只发送一次错误报警邮件

    }

    public function setHost($host = '')
    {
        if (!empty($host)) {
            $this->host = $host;
        }
    }
}
