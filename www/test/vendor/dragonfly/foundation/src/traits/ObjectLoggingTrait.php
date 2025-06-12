<?php
/**
 * Created by IntelliJ IDEA.
 * User: nathena
 * Date: 2019/4/2 0002
 * Time: 15:44
 */

namespace Dragonfly\foundation\traits;


use Dragonfly\foundation\interfaces\LoggerInterface;

trait ObjectLoggingTrait
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @return LoggerInterface
     */
    public function getLogger()
    {
        return $this->logger;
    }

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    protected function logDebug($message)
    {
        $this->logger && $this->logger->debug($message);
    }

    protected function logInfo($message)
    {
        $this->logger && $this->logger->info($message);
    }

    protected function logWarn($message)
    {
        $this->logger && $this->logger->warn($message);
    }

    protected function logNotice($message)
    {
        $this->logger && $this->logger->notice($message);
    }

    protected function logError($message)
    {
        $this->logger && $this->logger->error($message);
    }
}
