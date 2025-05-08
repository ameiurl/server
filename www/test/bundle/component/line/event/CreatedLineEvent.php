<?php
namespace My\component\line\event;

use My\component\BaseEvent;
use My\component\line\LineModel;

class CreatedLineEvent extends BaseEvent
{
    /**
     * @var LineModel
     */
    protected $lineModel;
    protected $params;

    public function __construct($lineModel,$params)
    {
        $this->lineModel = $lineModel;
        $this->params = $params;
    }

    public function getLineModel()
    {
        return $this->lineModel;
    }

    public function getParams()
    {
        return $this->params;
    }

    public function getListenerList()
    {
        $listener = [
            'My\component\workflow\handler\LineEventHandler',
            //'erp\gateway\handler\LineEventHandler',
        ];

        return $listener;
    }
}
