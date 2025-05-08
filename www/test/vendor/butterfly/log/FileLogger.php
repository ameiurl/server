<?php
/**
 * Butterfly : Non-business component library
 *
 * @copyright Copyright (c) 2016 CNCN Information Co., Ltd.
 * All rights reserved.
 */

namespace Butterfly\Log;

use Psr\Log\AbstractLogger;

/**
 * 文件日志记录
 *
 * @author 黄景祥(Joel Huang) <joelhy@gmail.com>
 * @since  1.0
 */
class FileLogger extends AbstractLogger
{
    /**
     * 日志存放路径
     *
     * @var string
     */
    protected $logPath;

    public function __construct(array $config)
    {
        $this->logPath = $config['path'] ?? './';
    }

    /**
     * {@inheritdoc}
     */
    public function log($level, $message, array $context = [])
    {
        if ($this->logPath === './') {
            $dir = $this->logPath;
        } else {
            $dir = $this->logPath . '/' . date('Y-m') . '/';
        }
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $filePath = $dir . $level . '.log';
        $logInfo = sprintf('[%s] %s', date('d H:i:s'), $message) . PHP_EOL;
        $result = file_put_contents($filePath, $logInfo, FILE_APPEND);

        return $result !== false;
    }
}
