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

    const STATE_READY = 'READY';
    const STATE_DELAYED = 'DELAYED';
    const STATE_BURIED = 'BURIED';
    const STATE_WORKING = 'WORKING';
    const STATE_ENDED = 'ENDED';

    public $connection = 'db';
    public $table = '{{%queue}}';
    public $wait = 3;
    public $persistent = false;
    public $duplicate_jobs = false;
    public $queues = [];

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
            'state' => 'ENUM("READY","DELAYED","WORKING","ENDED","BURIED") NOT NULL DEFAULT "READY"',
            'priority' => 'INT NOT NULL DEFAULT 0',
            'ready' => 'TIMESTAMP NOT NULL',
            'start' => 'TIMESTAMP NULL DEFAULT NULL',
            'end' => 'TIMESTAMP NULL DEFAULT NULL'])->execute();
        $this->connection->schema->refresh();
    }

    public function dropTable()
    {
        $this->connection->createCommand()->dropTable($this->getTableName())->execute();
    }

    public function putInTube($queue, $payload = [], $delay = 0, $priority = 0)
    {
        // Search jobs with same queue and data to avoid duplicates
        if (!$this->duplicate_jobs) {
            $command = $this->connection->createCommand('SELECT id FROM ' . $this->getTableName() . ' WHERE queue=:queue AND data =:payload')
                    ->BindValues(['queue' => $queue, 'payload' => Json::encode($payload)]);
            if ($command->queryOne()) {
                return true;
            }
        }

        return $this->push(['data' => Json::encode($payload), 'priority' => $priority], $queue, $delay);
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
                ])->execute();
    }

    public function pop($queue = false)
    {
        $transaction = $this->connection->beginTransaction();
        try {
            $data = $this->connection->createCommand('SELECT * FROM ' . $this->getTableName() . ' WHERE queue IN ("' . implode('","', $this->queues) . '") AND state IN ("READY","DELAYED") AND ready <= NOW() ORDER BY priority DESC;')->queryOne();

            if ($data) {
                $this->connection->createCommand('UPDATE ' . $this->getTableName() . ' SET state=:state, start=NOW() WHERE id=:id')
                        ->BindValues(['state' => self::STATE_WORKING, 'id' => $data['id']])->execute();
                $sqm = new SimpleQueueMessage();
                $sqm->setAttributes($data);
                $transaction->commit();
                return $sqm;
            }
            $transaction->rollBack();
            return false;
        } catch (\Exception $e) {
            $transaction->rollBack();
            return false;
        }
    }

    public function purge($queue)
    {
        return $this->connection->createCommand('DELETE FROM ' . $this->getTableName() . ' WHERE queue=:queue')->BindValues(['queue' => $queue])->execute();
    }

    public function delete(array $message)
    {
        foreach ($message as $m) {
            if ($this->persistent) {
                echo 'update';
                $command = $this->connection->createCommand('UPDATE ' . $this->getTableName() . ' set state=:state,end=NOW() WHERE id=:id')
                        ->BindValues(['state' => self::STATE_ENDED, 'id' => $m->id]);
            } else {
                $command = $this->connection->createCommand('DELETE FROM ' . $this->getTableName() . ' WHERE id=:id')->BindValues(['id' => $m->id]);
            }
            $command->execute();
        }
    }

    public function release(array $message, $priority = 0, $delay = 0)
    {
        foreach ($message as $m) {
            $this->connection->createCommand('UPDATE ' . $this->getTableName() . ' SET state=:state, priority=:priority,ready=DATE_ADD(NOW(), INTERVAL ' . $delay . ' SECOND) WHERE id=:id')
                    ->BindValues(['state' => self::STATE_READY, 'priority' => $priority, 'id' => $m->id])->execute();
        }
    }

    public function delay(array $message, $priority = 0, $delay = 0)
    {
        echo 'Delay de '.$priority;
        foreach ($message as $m) {
            $this->connection->createCommand('UPDATE ' . $this->getTableName() . ' SET state=:state, priority=:priority,ready=DATE_ADD(NOW(), INTERVAL ' . $delay . ' SECOND) WHERE id=:id')
                    ->BindValues(['state' => self::STATE_DELAYED, 'priority' => $priority, 'id' => $m->id])->execute();
        }
    }

    public function bury(array $message)
    {
        foreach ($message as $m) {
            $this->connection->createCommand('UPDATE ' . $this->getTableName() . ' SET state=:state,end=NOW() WHERE id=:id')
                    ->BindValues(['state' => self::STATE_BURIED, 'id' => $m->id])->execute();
        }
    }

    public function watch($queue)
    {
        $this->queues[] = $queue;
    }
}
