<?php

namespace macklus\SimpleQueue;

<<<<<<< HEAD
use Yii;
use yii\base\InvalidConfigException;
use yii\base\Component;
use yii\queue\QueueInterface;
use yii\helpers\Json;
use yii\db\Expression;
=======
use yii\helpers\Json;
use yii\base\Exception;
use yii\db\Expression;
use yii\base\Component;
use yii\queue\QueueInterface;
use macklus\SimpleQueue\models\Queue;
>>>>>>> 16ddd46cd73126a8c6d010b549e3d6060642c38d

class SimpleQueue extends Component implements QueueInterface
{

<<<<<<< HEAD
    const STATE_WAIT = 'WAIT';
    const STATE_READY = 'READY';
    const STATE_WORKING = 'WORKING';
    const STATE_ENDED = 'ENDED';

    public $connection = 'db';
    public $table = '{{%queue}}';

    public function init()
    {

        parent::init();

        if (is_string($this->connection)) {
            $this->connection = Yii::$app->get($this->connection);
        }

        if (!$this->connection instanceof yii\db\Connection) {
            throw new InvalidConfigException("Queue::connection must be application component ID of a SQL connection.");
        }
        if (!$this->hasTable()) {
            $this->createTable();
        }
    }

    public function getTableName()
    {
        return $this->table;
    }

    private function hasTable()
    {
        $schema = $this->connection->schema->getTableSchema($this->getTableName(), true);
        if ($schema == null) {
            return false;
        }
        return true;
    }

    private function createTable()
    {
        $this->connection->createCommand()->createTable($this->getTableName(), [
            'id' => 'BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY',
            'queue' => 'VARCHAR(255) CHARSET utf8 COLLATE utf8_general_ci NOT NULL',
            'data' => 'TEXT CHARSET utf8 COLLATE utf8_general_ci NOT NULL',
            'state' => 'ENUM("WAIT","READY","WORKING","ENDED") NOT NULL DEFAULT "WAIT"',
            'priority' => 'INT NOT NULL DEFAULT 0',
            'ready' => 'TIMESTAMP NOT NULL',
            'start' => 'TIMESTAMP',
            'end' => 'TIMESTAMP'])->execute();
        $this->connection->schema->refresh();
    }

    public function dropTable()
    {
        $this->connection->createCommand()->dropTable($this->getTableName())->execute();
    }

    public function putInTube($queue, $payload = [], $delay = 0, $priority = 0, $avoid_duplicate = false)
    {
        // Search jobs with same queue and data to avoid duplicates
        if ($avoid_duplicate) {
            $command = $this->connection->createCommand('SELECT id FROM ' . $this->getTableName() . ' WHERE queue=:queue AND data =:payload')
                    ->BindValues(['queue' => $queue, 'payload' => Json::encode($payload)]);
            if ($command->queryOne()) {
                return true;
            }
        }

        $payload = [
            'data' => Json::encode($payload),
            'priority' => $priority,
        ];
        return $this->push($payload, $queue, $delay);
=======
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
>>>>>>> 16ddd46cd73126a8c6d010b549e3d6060642c38d
    }

    public function delete(array $message)
    {

    }

    public function pop($queue)
    {
        
    }

    public function purge($queue)
    {
<<<<<<< HEAD

    }

    public function push($payload, $queue, $delay = 0)
    {
        return $this->connection->createCommand()->insert($this->getTableName(), [
                    'id' => null,
                    'queue' => $queue,
                    'data' => $payload['data'],
                    'state' => self::STATE_READY,
                    'priority' => $payload['priority'],
                    'ready' => new Expression("DATE_ADD(NOW(), INTERVAL $delay SECOND)"),
                    'start' => null,
                    'end' => null
                ])->execute();
=======
        
>>>>>>> 16ddd46cd73126a8c6d010b549e3d6060642c38d
    }

    public function release(array $message, $delay = 0)
    {

    }
}
