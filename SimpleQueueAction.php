<?php

namespace macklus\SimpleQueue;

use Yii;
use yii\web\Controller;

//use macklus\SimpleQueue\SimpleQueue;

class SimpleQueueAction extends Controller
{

    public $queue;
    public $action;
    public $connection = 'db';
    public $queues = [];
    private $_connection;

    public function init()
    {
// Params
        $this->queue = Yii::$app->request->get('q', false);
        $this->action = Yii::$app->request->get('a', false);

// Conection
        $this->_connection = Yii::$app->get($this->connection);
        if (!$this->_connection instanceof yii\db\Connection) {
            throw new InvalidConfigException("Queue::connection must be application component ID of a SQL connection.");
        }

        Yii::$app->layoutPath = '@vendor/macklus/yii2-simple-queue/views/layout';
        Yii::$app->viewPath = '@vendor/macklus/yii2-simple-queue/views/';

        $this->getQueueInfo();
    }

    public function actionIndex()
    {
        echo '-->' . \Yii::$app->request->url;
        if ($this->queue) {
            return $this->render('/queue', ['queues' => $this->queues, 'jobs' => $this->getAllJobsFromQueue($this->queue)]);
        }
        return $this->render('/index', ['queues' => $this->queues]);
    }

    public function getAllJobsFromQueue($queue)
    {
        return $this->_connection->createCommand("SELECT * FROM " . Yii::$app->queue->getTableName() . " WHERE queue = '" . $queue . "' ORDER BY id DESC")->queryAll();
    }

    private function getQueueInfo()
    {
        $this->queues = $this->_connection->createCommand("SELECT queue, SUM(state = 'READY') AS ready, SUM(state = 'DELAYED') AS 'delayed', SUM(state = 'WORKING') AS working, SUM(state = 'ENDED') AS ended, SUM(state = 'BURIED') AS buried, COUNT(queue) AS total FROM " . Yii::$app->queue->getTableName() . " GROUP BY queue")->queryAll();
    }

    public function runWithParams()
    {
        return $this->actionIndex();
    }
}
