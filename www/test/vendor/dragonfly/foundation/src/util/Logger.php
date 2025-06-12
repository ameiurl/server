<?php
/**
 * 日志
 * @author nathena
 *
 */
namespace Dragonfly\foundation\util;

use Dragonfly\foundation\interfaces\LoggerInterface;

class Logger implements LoggerInterface
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
     * @param string $log_name
     * @param String $root
     * @return static
     */
    public static function getInstance($log_name='',$root=".")
    {
        if(!isset(self::$instance[$log_name]))
        {
            self::$instance[$log_name] = new self($log_name,$root);
        }
        return self::$instance[$log_name];
    }

    public function __construct($log_name,$root=".")
    {
        $this->ip = get_client_ip();
        $log_name = str_replace("../",'',$log_name);
        $log_name = trim($log_name,'/');

        if(!empty($root)) {
            $this->log_path = $root . DIRECTORY_SEPARATOR . ($log_name ? $log_name . DIRECTORY_SEPARATOR : '');
        }else{
            $this->log_path = $log_name . DIRECTORY_SEPARATOR;
        }
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
        $file = $this->log_path .'log-' . date('Y-m-d') . '.txt';

        $log = sprintf('[PID] %s %s [TIME] %s [IP] %s [MSG] %s',
                getmypid(),
                $level,
                date('H:i:s'),
                $this->ip,
                $message) . "\n";

        file_put_contents($file, $log,FILE_APPEND);
    }


}
