<?php

namespace macklus\SimpleQueue;

use yii\helpers\Json;
use yii\base\Exception;
use yii\db\Expression;
use yii\base\Component;
use yii\queue\QueueInterface;
use macklus\SimpleQueue\models\Queue;

class SimpleQueue extends Component implements QueueInterface
{

    public $db;
    public $queueTable;

    public function putInTube($queue, $data = [], $delay = 0, $state = Queue::STATUS_READY, $priority = 0)
    {
        $payload = [
            'data' => $data,
            'state' => $state,
            'priority' => $priority
        ];
        return $this->push($payload, $queue, $delay);
    }

    public function push($payload, $queue, $delay = 0)
    {
        $q = new Queue();
        $q->queue = $queue;
        $q->data = is_string($payload['data']) ? $payload['data'] : Json::encode($payload['data']);
        $q->state = $payload['state'];
        $q->priority = $payload['priority'];
        $q->ready = ($delay != 0 ) ? new Expression('NOW() + INTERVAL :sec SECOND', ['sec' => $delay]) : new Expression('NOW()');
        if ($q->save()) {
            return $q->getMessage();
        } else {
            throw new Exception('Error saving object: ' . print_R($q->getErrors(), true));
        }
    }

    public function delete(array $message)
    {

    }

    public function pop($queue)
    {
        
    }

    public function purge($queue)
    {
        
    }

    public function release(array $message, $delay = 0)
    {

    }
}
