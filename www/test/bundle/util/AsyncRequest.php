<?php
/**
 * Created by IntelliJ IDEA.
 * User: nathena
 * Date: 2018/6/14 0014
 * Time: 16:54
 */

namespace erp\util;

class AsyncRequest
{
    private $domain;
    /**
     * @var Logger
     */
    protected $logger;

    public function __construct($domain,$logger=null )
    {
        $this->domain = $domain;
        $this->logger = is_null($logger) ?  Logger::getInstance("api") : $logger;
    }

    public function doRequest($url,$params,$timeout=15)
    {
        $uuid = UUIDGenerator::numberNo();
        $ch = curl_init();
        $url = trim($this->domain,"/").$url;
        $this->logger->debug("========AsyncRequest_start {$uuid}:{$url}====".var_export($params,true)."========");
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, []);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);//这个可以让ignore_user_abort生效。
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_POST,true);
        curl_setopt($ch, CURLOPT_POSTFIELDS,$params);
        curl_setopt($ch, CURLOPT_NOSIGNAL, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);//这个是重点。
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $output = curl_exec($ch);
        $this->logger->debug("========AsyncRequest_end  {$uuid}:{$url} {$output}============".curl_errno($ch));
        curl_close($ch);
        return $output;
    }

    public function doBuildRequest($url, $params, $timeout=15)
    {
        $params = http_build_query($params);  //支持多维数组
        return $this->doRequest($url, $params, $timeout);
    }

    public function doGet($url,$timeout=15)
    {
        $uuid = UUIDGenerator::numberNo();
        $ch = curl_init();
        $url = trim($this->domain,"/").$url;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, []);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);//这个可以让ignore_user_abort生效。
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_NOSIGNAL, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);//这个是重点。
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $output = curl_exec($ch);
        $this->logger->debug("========AsyncRequest doGet {$uuid}:{$url} {$output}============".curl_errno($ch));
        curl_close($ch);
        return $output;
    }

    public static function deferCall($options,$callback=null)
    {
        $domain = isset($options["domain"]) ? trim($options["domain"]):"";
        if(empty($domain))
        {
            throw new \RuntimeException("The domain param for AsyncRequest::deferCall is undefined");
        }
        $url = isset($options["url"]) ? trim($options["url"]):"";
        if(empty($url))
        {
            throw new \RuntimeException("The url param for AsyncRequest::deferCall is undefined");
        }
        $params = isset($options["data"]) ? $options["data"]:[];
        $timeout = isset($options["timeout"]) ? trim($options["timeout"]):1;

        register_shutdown_function(function () use ($domain,$url,$params,$timeout,$callback){
            $request = new self($domain);
            $result = $request->doRequest($url,$params,$timeout);
            if(!is_null($callback) && is_callable($callback))
            {
                $callback(["result"=>$result]);
            }
        });
    }
}