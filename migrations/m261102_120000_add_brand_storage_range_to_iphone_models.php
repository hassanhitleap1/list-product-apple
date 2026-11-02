<?php

use yii\db\Migration;

/**
 * Adds brand, model name, storage, a max price (for price ranges),
 * an active flag and a sort order to the phone price table.
 *
 * `model` stays as the full display label (e.g. "iPhone 15 Pro 256GB")
 * and `price` stays as the base (minimum) Grade A price, so older code keeps working.
 */
class m261102_120000_add_brand_storage_range_to_iphone_models extends Migration
{
    public function safeUp()
    {
        $table = '{{%iphone_models}}';
        $this->addColumn($table, 'brand', $this->string(50)->notNull()->defaultValue('Apple'));
        $this->addColumn($table, 'name', $this->string(100)->null());
        $this->addColumn($table, 'storage', $this->string(20)->null());
        $this->addColumn($table, 'price_max', $this->double()->null());
        $this->addColumn($table, 'is_active', $this->boolean()->notNull()->defaultValue(true));
        $this->addColumn($table, 'sort_order', $this->integer()->notNull()->defaultValue(0));

        // Existing rows: use the old label as the model name.
        $this->update($table, ['name' => new \yii\db\Expression('model')]);

        $this->createIndex('idx-iphone_models-brand-active', $table, ['brand', 'is_active']);
    }

    public function safeDown()
    {
        $table = '{{%iphone_models}}';
        $this->dropIndex('idx-iphone_models-brand-active', $table);
        $this->dropColumn($table, 'sort_order');
        $this->dropColumn($table, 'is_active');
        $this->dropColumn($table, 'price_max');
        $this->dropColumn($table, 'storage');
        $this->dropColumn($table, 'name');
        $this->dropColumn($table, 'brand');
    }
}
