<?php

use yii\db\Migration;

/**
 * Requests sent by customers from the calculator: name, phone number,
 * the phone they chose, their answers, the offer and their photos.
 * Photos are stored outside web/ (data/trade-in-requests/<id>/) and only staff can open them.
 */
class m261115_120000_create_trade_in_request_table extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }
        $this->createTable('{{%trade_in_request}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(100)->notNull(),
            'phone' => $this->string(20)->notNull(),
            'model_id' => $this->integer()->null(),
            'device' => $this->string(255)->notNull(),
            'answers' => $this->text()->notNull(),
            'offer_low' => $this->integer()->notNull(),
            'offer_high' => $this->integer()->notNull(),
            'photos' => $this->text()->notNull(),
            'status' => $this->string(16)->notNull()->defaultValue('new'),
            'ip' => $this->string(45)->null(),
            'created_at' => $this->integer()->notNull(),
        ], $tableOptions);
        $this->createIndex('idx_trade_in_request_status', '{{%trade_in_request}}', ['status', 'created_at']);
        $this->createIndex('idx_trade_in_request_phone', '{{%trade_in_request}}', 'phone');
    }

    public function safeDown()
    {
        $this->dropTable('{{%trade_in_request}}');
    }
}
