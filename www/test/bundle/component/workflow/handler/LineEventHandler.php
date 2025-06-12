<?php
namespace My\component\workflow\handler;

use Dragonfly\foundation\event\EventInterface;
use My\component\BaseEventHandler;
use My\component\line\event\CreatedLineEvent;
//use My\type\line\LineType;
//use My\type\ProductType;
//use My\type\workflow\WorkflowType;

class LineEventHandler extends BaseEventHandler
{
    public function handler(EventInterface $event)
    {
        if($event instanceof CreatedLineEvent)
        {
            $lineModel = $event->getLineModel();
			//print_r($lineModel);exit;
            $params = $event->getParams();

			\Log::log('testEvent', 'model:'.var_export($lineModel, true) . 'params:'.var_export($params, true));
            
        }
    }
}
