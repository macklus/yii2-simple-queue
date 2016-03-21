<?php

namespace macklus\SimpleQueue;

use Yii;
use yii\base\InvalidConfigException;
use yii\base\Component;
use yii\queue\QueueInterface;
use yii\helpers\Json;
use yii\db\Expression;

class SimpleQueue extends Component implements QueueInterface
{

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
    }

    public function release(array $message, $delay = 0)
    {

    }
}
