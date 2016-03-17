<?php

namespace macklus\SimpleQueue\models;

use Yii;

/**
 * This is the model class for table "{{%queueTable}}".
 *
 * @property integer $id
 * @property string $queue
 * @property string $data
 * @property string $state
 * @property integer $priority
 * @property string $ready
 * @property string $start
 * @property string $end
 */
class Queue extends \yii\db\ActiveRecord
{

    const STATUS_WAIT = 'WAIT';
    const STATUS_READY = 'READY';
    const STATUS_WORKING = 'WORKING';
    const STATUS_ENDED = 'ENDED';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%simpleQueue}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['queue'], 'required'],
            [['data'], 'string'],
            [['priority'], 'integer'],
            [['ready', 'start', 'end'], 'safe'],
            [['queue', 'state'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('queue', 'ID'),
            'queue' => Yii::t('queue', 'Queue'),
            'data' => Yii::t('queue', 'Data'),
            'state' => Yii::t('queue', 'State'),
            'priority' => Yii::t('queue', 'Priority'),
            'ready' => Yii::t('queue', 'Ready'),
            'start' => Yii::t('queue', 'Start'),
            'end' => Yii::t('queue', 'End'),
        ];
    }

    public function getMessage()
    {
        $message = new QueueMessage();
        if ($this->id) {
            $message->load(Queue::findOne(['id' => $this->id]));
        }
        return $message;
    }
}
