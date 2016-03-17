<?php

namespace macklus\SimpleQueue\models;

use yii\base\Component;
use macklus\SimpleQueue\models\Queue;

class QueueMessage extends Component
{

    const STATUS_WAIT = Queue::STATUS_WAIT;
    const STATUS_READY = Queue::STATUS_READY;
    const STATUS_WORKING = Queue::STATUS_WORKING;
    const STATUS_ENDED = Queue::STATUS_ENDED;

    public $id = false;
    public $queue = false;
    public $data = false;
    public $state = false;
    public $priority = false;
    public $ready = false;
    public $start = false;
    public $end = false;

    public function load($object)
    {
        foreach (['id', 'queue', 'data', 'state', 'priority', 'ready', 'start', 'end'] as $v) {
            $this->$v = $object->$v ? $object->$v : false;
        }
    }
}
