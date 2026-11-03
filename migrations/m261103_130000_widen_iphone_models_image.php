<?php

use yii\db\Migration;

/**
 * Pasted image links can be long, so the photo column holds up to 1000 characters.
 */
class m261103_130000_widen_iphone_models_image extends Migration
{
    public function safeUp()
    {
        if ($this->db->driverName === 'sqlite') {
            return; // SQLite does not limit text length; nothing to change.
        }
        $this->alterColumn('{{%iphone_models}}', 'image', $this->string(1000)->null());
    }

    public function safeDown()
    {
        if ($this->db->driverName === 'sqlite') {
            return;
        }
        $this->alterColumn('{{%iphone_models}}', 'image', $this->string(255)->null());
    }
}
