<?php
/**
 * Butterfly : Non-business component library
 *
 * @copyright Copyright (c) 2016 CNCN Information Co., Ltd.
 * All rights reserved.
 */

namespace Butterfly\Error;

use Closure;
use ErrorException;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * 错误处理程序
 *
 * @author 黄景祥(Joel Huang) <joelhy@gmail.com>
 * @since  1.0
 */
class ErrorHandler
{
    /**
     * 异常处理方法
     *
     * @var array
     */
    protected $handlers = [];

    /**
     * 是否禁用 shutdown 中的错误处理程序
     *
     * @var bool
     */
    protected $disableShutdownHandler = false;

    /**
     * 日志记录实例
     *
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * 不记录的异常类型
     *
     * @var array
     */
    protected $disableLoggingFor = [];

    /**
     * 构造函数
     */
    public function __construct()
    {
//        $this->handle('\Throwable', function (Throwable $ex) {
//            echo vsprintf('[ %s ] %s on line [ %d ] in [ %s ]', [
//                    get_class($ex),
//                    $ex->getMessage(),
//                    $ex->getLine(),
//                    $ex->getFile()
//                ]) . PHP_EOL;
//            echo $ex->getTraceAsString();
//        });

        // 注册异常处理程序
        $this->register();
    }

    /**
     * 添加异常处理方法
     *
     * @param string  $exceptionType
     * @param Closure $handler
     */
    public function handle(string $exceptionType, Closure $handler)
    {
        array_unshift($this->handlers,
            ['exceptionType' => $exceptionType, 'handler' => $handler]);
    }

    /**
     * 注册错误处理程序
     */
    protected function register()
    {
        // 致命错误处理
        register_shutdown_function(function () {
            $err = error_get_last();
            // http://cn2.php.net/manual/en/function.error-get-last.php#100310
            // If an error handler (see set_error_handler ) successfully handles
            // an error then that error will not be reported by this function.

            if ($err !== null && (error_reporting() & $err['type']) !== 0 &&
                !$this->disableShutdownHandler
            ) {
                $this->handler(new ErrorException($err['message'],
                    $err['type'], $err['type'], $err['file'], $err['line']));
                exit(1);
            }
        });

        // 异常处理
        set_exception_handler([$this, 'handler']);
    }

    /**
     * 是否异常信息应当记日志
     *
     * @param Throwable $exception
     * @return bool
     */
    protected function shouldExceptionBeLogged(Throwable $exception) : bool
    {
        if ($this->logger === null) {
            return false;
        }

        foreach ($this->disableLoggingFor as $exceptionType) {
            if ($exception instanceof $exceptionType) {
                return false;
            }
        }

        return true;
    }

    /**
     * 清除输出缓存
     */
    protected function clearOutputBuffers()
    {
//        while (ob_get_level() > 0) {
//            ob_end_clean();
//        }
    }

    /**
     * 处理未捕获的异常
     *
     * @param Throwable $exception
     */
    public function handler(Throwable $exception)
    {
        try {
            $this->clearOutputBuffers();

            foreach ($this->handlers as $handler) {
                if ($exception instanceof $handler['exceptionType']) {
                    if ($handler['handler']($exception) !== null) {
                        break;
                    }
                }
            }

            if ($this->shouldExceptionBeLogged($exception)) {
                $this->logger->error($exception);
            }
        } catch (Throwable $ex) {  // 仍然有异常抛出
            $this->clearOutputBuffers();

            echo $ex->getMessage() . ' on line [ ' . $ex->getLine() .
                ' ] in [ ' . $ex->getFile() . ' ]' . PHP_EOL;
            echo $this->errorInfo($ex);
        }

        exit(1);
    }

    /**
     * 禁用错误处理程序
     */
    public function disableShutdownHandler()
    {
        $this->disableShutdownHandler = true;
    }

    /**
     * 设置日志实例
     *
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * 获取详细的错误信息
     *
     * @param Throwable $ex
     * @param array     $extras 额外需要显示的错误信息
     * @return string
     */
    public function errorInfo(Throwable $ex, array $extras = [])
    {
        if (!empty($_SERVER['HTTP_HOST'])) {
            $url = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        } else {
            $url = '';
        }
        $serverIp = $_SERVER["SERVER_ADDR"] ?? '';
        $userIp   = $_SERVER['REMOTE_ADDR'] ?? '';

        $content = vsprintf("
PHP_SELF: %s
Url: %s
Server IP: %s
User IP: %s
Time: %s
Exception: %s
Code: %s
Message: %s
Line: %s
File: %s

Trace: 
%s

", [
            $_SERVER['PHP_SELF'],
            $url,
            $serverIp,
            $userIp,
            date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME']),
            get_class($ex),
            $ex->getCode(),
            $ex->getMessage(),
            $ex->getLine(),
            $ex->getFile(),
            $ex->getTraceAsString()
        ]);

        // 额外扩展信息
        foreach ($extras as $key => $value) {
            $content .= "$key: $value" . "\n";
        }

        return $content;
    }
}
