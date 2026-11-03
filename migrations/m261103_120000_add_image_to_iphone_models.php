<?php

use yii\db\Migration;

/**
 * Adds a photo to each phone. One photo is shared by all storage sizes of a model
 * (set from Admin › Model photos). Stored as a path under web/, e.g. uploads/phones/x.jpg.
 */
class m261103_120000_add_image_to_iphone_models extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%iphone_models}}', 'image', $this->string(255)->null());
    }

    public function safeDown()
    {
        $this->dropColumn('{{%iphone_models}}', 'image');
    }
}
