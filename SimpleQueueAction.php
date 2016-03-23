<?php

namespace macklus\SimpleQueue;

use Yii;
use yii\base\Action;

//use macklus\SimpleQueue\SimpleQueue;

class SimpleQueueAction extends Action
{

    public $queue;
    public $action;
    private $_queues;
    private $_connection;

    public function init()
    {
        // Params
        $this->queue = Yii::$app->request->get('q', false);
        $this->action = Yii::$app->request->get('a', false);

        // Conection
        if (is_string(Yii::$app->queue->connection)) {
            $this->_connection = Yii::$app->get($this->connection);
        }
        if (!$this->_connection instanceof yii\db\Connection) {
            throw new InvalidConfigException("Queue::connection must be application component ID of a SQL connection.");
        }
    }

    public function run()
    {

    }

    private function getQueueInfo()
    {
        $this->_connection->createCommand("SELECT queue, SUM(state = 'READY') AS ready, SUM(state = 'DELAYED') AS 'delayed', SUM(state = 'WORKING') AS working, SUM(state = 'ENDED') AS ended, SUM(state = 'BURIED') AS buried, COUNT(queue) AS total FROM " . Yii::$app->queue->getTableName() . " GROUP BY queue");
    }
}
