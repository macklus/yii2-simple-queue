<?php

namespace macklus\SimpleQueue;

use Yii;

/**
 * This is the model class for table "{{%queueTable}}".
 *
 * @property integer $id
 * @property string $queue
 * @property string $data
 * @property string $state
 * @property integer $pri
 * @property string $ready
 * @property string $start
 * @property string $end
 */
class Queue extends \yii\db\ActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%queueTable}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['queue'], 'required'],
            [['data'], 'string'],
            [['pri'], 'integer'],
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
            'pri' => Yii::t('queue', 'Pri'),
            'ready' => Yii::t('queue', 'Ready'),
            'start' => Yii::t('queue', 'Start'),
            'end' => Yii::t('queue', 'End'),
        ];
    }
}
