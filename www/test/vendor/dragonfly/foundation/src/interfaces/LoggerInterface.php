<?php
/**
 * Created by IntelliJ IDEA.
 * User: nathena
 * Date: 2019/3/19 0019
 * Time: 14:25
 */

namespace Dragonfly\foundation\interfaces;


interface LoggerInterface
{
    public function debug($message);
    public function info($message);
    public function warn($message);
    public function notice($message);
    public function error($message);
}
