<?php
/**
 * Created by IntelliJ IDEA.
 * User: nathena
 * Date: 2019/1/23 0023
 * Time: 10:49
 */

namespace My\component;

use Dragonfly\foundation\event\EventListenerInterface;
use Dragonfly\foundation\traits\ObjectAccessWrapTrait;
use Dragonfly\foundation\traits\ObjectLoggingTrait;

abstract class BaseEventHandler implements EventListenerInterface
{
    use ObjectAccessWrapTrait;
    use ObjectLoggingTrait;

}
