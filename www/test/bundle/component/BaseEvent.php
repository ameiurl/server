<?php
namespace My\component;

use Dragonfly\foundation\event\EventDispatcher;
use Dragonfly\foundation\event\EventInterface;
use Dragonfly\foundation\traits\ObjectAccessWrapTrait;
use Dragonfly\foundation\traits\ObjectLoggingTrait;

abstract class BaseEvent implements EventInterface
{
    use ObjectAccessWrapTrait;
    use ObjectLoggingTrait;

    public function publish()
    {
        EventDispatcher::getInstance()->publish($this);
    }
}
