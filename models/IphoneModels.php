<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "iphone_models".
 *
 * @property int $id
 * @property string $model
 * @property float $price
 */
class IphoneModels extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'iphone_models';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['model', 'price'], 'required'],
            [['price'], 'number'],
            [['model'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'model' => Yii::t('app', 'Model'),
            'price' => Yii::t('app', 'Price'),
        ];
    }

    /**
     * {@inheritdoc}
     * @return IphoneModelsQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new IphoneModelsQuery(get_called_class());
    }
}
