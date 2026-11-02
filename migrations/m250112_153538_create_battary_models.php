<?php

use yii\db\Migration;

/**
 * Class m250112_153538_create_battary_models
 */
class m250112_153538_create_battary_models extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {

        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }
        $this->createTable('{{%battary_models}}', [
            'id' => $this->primaryKey(),
            'value' => $this->string()->notNull(),
            'price' => $this->double()->notNull(),
        ], $tableOptions);

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m250112_153538_create_battary_models cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m250112_153538_create_battary_models cannot be reverted.\n";

        return false;
    }
    */
}
