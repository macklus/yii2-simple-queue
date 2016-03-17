<?php

use yii\db\Migration;

class m160316_070322_create_queue_table extends Migration
{

    public function up()
    {
        $this->createTable('{{%simpleQueue}}', [
            'id' => $this->primaryKey(),
            'queue' => $this->string()->notNull(),
            'data' => $this->text(),
            'state' => $this->string()->notNull()->defaultValue('READY'),
            'priority' => $this->integer()->defaultValue(0),
            'ready' => $this->timestamp(),
            'start' => $this->timestamp(),
            'end' => $this->timestamp(),
        ]);
    }

    public function down()
    {
        $this->dropTable('{{%simpleQueue}}');
    }
}
