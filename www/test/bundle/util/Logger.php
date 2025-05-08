<?php
/**
 * 日志
 * @author nathena
 *
 */
namespace erp\util;

use cncn\foundation\interfaces\LoggerInterface;
use think\exception\Handle;
use think\Log;
use think\Request;

class Logger extends Handle implements LoggerInterface
{
    private static $ERR    = 'ERROR';
    private static $WARN   = 'WARN';
    private static $NOTICE = 'NOTICE';
    private static $INFO   = 'INFO';
    private static $DEBUG  = 'DEBUG';

    private static $instance = [];

    private $ip;
    private $log_path;

    /**
     * @param string $log_path
     * @return static
     */
    public static function getInstance($log_path='.')
    {
        if(!isset(self::$instance[$log_path]))
        {
            self::$instance[$log_path] = new self($log_path);
        }
        return self::$instance[$log_path];
    }

    public static function doReport(\Exception $e)
    {
        Log::error($e->getMessage()."\n".$e->getTraceAsString());
    }

    protected function __construct($log_path='.')
    {
        $this->ip = Request::instance()->ip();
        $this->log_path = LOG_PATH.$log_path;
    }

    public function debug($message)
    {
        $this->save($message,self::$DEBUG);
    }

    public function info($message)
    {
        $this->save($message,self::$INFO);
    }

    public function warn($message)
    {
        $this->save($message,self::$WARN);
    }

    public function notice($message)
    {
        $this->save($message,self::$NOTICE);
    }
    
    public function error($message)
    {
        $this->save($message,self::$ERR);
    }

    private function save($message, $level)
    {
        if( !is_dir($this->log_path)) {
            mkdir($this->log_path,0766,true);
        }
        $file = $this->log_path .'/'.date('Ymd').'_log.log';
        $log = sprintf('[PID] %s %s [TIME] %s [IP] %s [MSG] %s',
                getmypid(),
                $level,
                date('H:i:s'),
                $this->ip,
                $message) . "\n";

        file_put_contents($file, $log,FILE_APPEND);
    }


}