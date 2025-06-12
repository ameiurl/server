<?php
namespace Dragonfly\foundation\event;

class EventDispatcher
{
    private static $_instance;

    /**
     * @return EventDispatcher
     */
    public static function getInstance()
    {
        if (!isset(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    protected function __construct()
    {
    }

    public function publish(EventInterface ...$events)
    {
        foreach($events as $event)
        {
            $listenerList = $event->getListenerList();
            foreach($listenerList as $listenerClass)
            {
                if(class_exists($listenerClass))
                {
                    $listener = new $listenerClass();
                    if($listener instanceof EventListenerInterface)
                    {
                        $listener->handler($event);
                    }
                }
            }
        }
    }

}
