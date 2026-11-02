<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "battary_models".
 *
 * @property int $id
 * @property string $value
 * @property float $price
 */
class BattaryModels extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'battary_models';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['value', 'price'], 'required'],
            [['price'], 'number'],
            [['value'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'value' => Yii::t('app', 'Value'),
            'price' => Yii::t('app', 'Price'),
        ];
    }

    /**
     * {@inheritdoc}
     * @return BattaryModelsQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new BattaryModelsQuery(get_called_class());
    }
}
