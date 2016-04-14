<?php

namespace macklus\SimpleQueue;

use yii\helpers\Json;

class SimpleQueueMessage
{

    public $id;
    public $queue;
    public $data;
    public $state;
    public $priority;
    public $ready;
    public $start;
    public $end;
    public $_data = [];

    public function setAttributes($data = [])
    {
        foreach (['id', 'queue', 'data', 'state', 'priority', 'ready', 'start', 'end'] as $key) {
            if (isset($data[$key])) {
                $this->$key = $data[$key];
            }
        }
        $this->_data = Json::decode($this->data);
    }

    public function getData()
    {
        return (object) $this->_data;
    }
}
