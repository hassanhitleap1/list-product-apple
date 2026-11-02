<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "iphone_models" (all trade-in phones, any brand).
 *
 * @property int $id
 * @property string $model      Full display label, e.g. "iPhone 15 Pro 256GB"
 * @property float $price       Base Grade A price (lowest, JOD)
 * @property string $brand      Apple, Samsung, ...
 * @property string|null $name  Model name without storage, e.g. "iPhone 15 Pro"
 * @property string|null $storage e.g. "256GB", "1TB"
 * @property float|null $price_max Highest Grade A price when the price is a range
 * @property int $is_active
 * @property int $sort_order
 */
class IphoneModels extends \yii\db\ActiveRecord
{
    const BRANDS = ['Apple' => 'Apple', 'Samsung' => 'Samsung'];

    public static function tableName()
    {
        return 'iphone_models';
    }

    public function rules()
    {
        return [
            [['brand', 'name', 'price'], 'required'],
            [['price', 'price_max'], 'number', 'min' => 0],
            ['price_max', 'compare', 'compareAttribute' => 'price', 'operator' => '>=', 'type' => 'number',
                'skipOnEmpty' => true, 'message' => Yii::t('app', 'Max price must be greater than or equal to the min price.')],
            [['is_active'], 'boolean'],
            [['sort_order'], 'integer'],
            [['is_active'], 'default', 'value' => 1],
            [['sort_order'], 'default', 'value' => 0],
            [['brand'], 'string', 'max' => 50],
            [['name'], 'string', 'max' => 100],
            [['storage'], 'string', 'max' => 20],
            [['model'], 'string', 'max' => 255],
            [['brand', 'name', 'storage'], 'trim'],
            [['price_max', 'storage'], 'default', 'value' => null],
        ];
    }

    public function beforeSave($insert)
    {
        // Keep the display label in sync with name + storage.
        $this->model = $this->storage ? $this->name . ' ' . $this->storage : $this->name;
        if ($this->price_max !== null && $this->price_max !== '' && (float)$this->price_max <= (float)$this->price) {
            $this->price_max = null;
        }
        return parent::beforeSave($insert);
    }

    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'model' => Yii::t('app', 'Label'),
            'brand' => Yii::t('app', 'Brand'),
            'name' => Yii::t('app', 'Model'),
            'storage' => Yii::t('app', 'Storage'),
            'price' => Yii::t('app', 'Min price (JOD)'),
            'price_max' => Yii::t('app', 'Max price (JOD)'),
            'is_active' => Yii::t('app', 'Shown on site'),
            'sort_order' => Yii::t('app', 'Sort order'),
        ];
    }

    public function attributeHints()
    {
        return [
            'storage' => Yii::t('app', 'e.g. 128GB, 256GB, 1TB. Leave empty if the price is the same for all storage sizes.'),
            'price' => Yii::t('app', 'Grade A trade-in price. If there is only one price, put it here.'),
            'price_max' => Yii::t('app', 'Optional. Fill only when the price is a range (e.g. 450–460).'),
        ];
    }

    /** "450" or "450 – 460" */
    public function getPriceText()
    {
        $min = Yii::$app->formatter->asDecimal($this->price, 0);
        if ($this->price_max && $this->price_max > $this->price) {
            return $min . ' – ' . Yii::$app->formatter->asDecimal($this->price_max, 0);
        }
        return $min;
    }

    /**
     * Active phones as plain arrays for the calculator page.
     * @return array
     */
    public static function catalog()
    {
        $rows = static::find()
            ->where(['is_active' => 1])
            ->orderBy(['brand' => SORT_ASC, 'sort_order' => SORT_ASC, 'id' => SORT_ASC])
            ->all();

        $out = [];
        foreach ($rows as $r) {
            $out[] = [
                'id' => (int)$r->id,
                'brand' => $r->brand ?: 'Apple',
                'name' => $r->name ?: $r->model,
                'storage' => $r->storage,
                'min' => (float)$r->price,
                'max' => (float)($r->price_max && $r->price_max > $r->price ? $r->price_max : $r->price),
            ];
        }
        return $out;
    }

    public static function find()
    {
        return new IphoneModelsQuery(get_called_class());
    }
}
