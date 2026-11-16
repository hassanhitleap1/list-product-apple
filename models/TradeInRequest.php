<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\helpers\FileHelper;
use yii\helpers\Json;

/**
 * A customer's trade-in request sent from the calculator (table "trade_in_request").
 *
 * @property int $id
 * @property string $name
 * @property string $phone       Digits, with an optional leading +
 * @property int|null $model_id  iphone_models.id at the time of the request
 * @property string $device      Device label at the time, e.g. "Apple iPhone 15 Pro, 256GB"
 * @property string $answers     JSON list of {q, a} (question and answer as the customer saw them)
 * @property int $offer_low
 * @property int $offer_high
 * @property string $photos      JSON list of file names in data/trade-in-requests/<id>/
 * @property string $status      new | done
 * @property string|null $ip
 * @property int $created_at
 */
class TradeInRequest extends ActiveRecord
{
    const STATUS_NEW = 'new';
    const STATUS_DONE = 'done';

    const MAX_PHOTOS = 10;
    const PHOTO_MAX_BYTES = 8 * 1024 * 1024;
    const PHOTO_TYPES = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];

    public static function tableName()
    {
        return '{{%trade_in_request}}';
    }

    public function rules()
    {
        return [
            [['name', 'phone'], 'filter', 'filter' => function ($v) { return trim(preg_replace('/[\x00-\x1F\x7F]+/u', ' ', (string)$v)); }],
            ['phone', 'filter', 'filter' => [static::class, 'normalizePhone']],
            [['name', 'phone', 'device', 'answers', 'offer_low', 'offer_high', 'photos'], 'required'],
            ['name', 'string', 'min' => 2, 'max' => 100],
            ['phone', 'match', 'pattern' => '/^\+?\d{7,15}$/', 'message' => 'Enter a valid phone number, e.g. 0791234567.'],
            ['status', 'in', 'range' => [self::STATUS_NEW, self::STATUS_DONE]],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'No.',
            'name' => 'Name',
            'phone' => 'Phone',
            'device' => 'Device',
            'offer_high' => 'Offer (JOD)',
            'status' => 'Status',
            'created_at' => 'Sent',
        ];
    }

    public function beforeSave($insert)
    {
        if ($insert && !$this->created_at) {
            $this->created_at = time();
        }
        return parent::beforeSave($insert);
    }

    public function afterDelete()
    {
        parent::afterDelete();
        $dir = $this->getPhotoDir();
        if (is_dir($dir)) {
            FileHelper::removeDirectory($dir);
        }
    }

    /** "079 123 4567" / "+962 79-123-4567" -> "0791234567" / "+962791234567" */
    public static function normalizePhone($v)
    {
        $v = strtr((string)$v, ['٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4', '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9']);
        return preg_replace('/[\s\-().]/', '', $v);
    }

    /**
     * Works out the device, answers and offer on the server from the chosen phone and
     * the answers, the same way the calculator does, so a request can't carry a made-up price.
     *
     * @param int $modelId iphone_models.id
     * @param array $answers step id => option value
     * @param array $checklist config/checklist.php
     * @return string|null error message, or null when the request was filled in
     */
    public function applyChecklist($modelId, array $answers, array $checklist)
    {
        $phone = IphoneModels::find()->where(['id' => (int)$modelId, 'is_active' => 1])->one();
        if (!$phone) {
            return 'Choose your phone again; this model is no longer listed.';
        }

        $given = [];
        foreach ($answers as $k => $v) {
            if (is_string($k) && is_scalar($v)) {
                $given[$k] = (string)$v;
            }
        }

        $asked = [];
        $snapshot = [];
        $share = 0.0;
        foreach ($checklist['steps'] as $step) {
            if (!empty($step['when']) && ($asked[$step['when']['step']] ?? null) !== $step['when']['is']) {
                continue;
            }
            $value = $given[$step['id']] ?? null;
            $option = null;
            foreach ($step['options'] as $o) {
                if ($o['value'] === $value) {
                    $option = $o;
                    break;
                }
            }
            if (!$option) {
                return 'Please answer every question before sending.';
            }
            if (!empty($option['reject'])) {
                return 'This device needs a branch check, so it can\'t be sent online.';
            }
            $asked[$step['id']] = $value;
            $share += (float)($option['deduct'] ?? 0);
            $snapshot[] = ['q' => $step['short'] ?? $step['title'], 'a' => $option['label']];
        }

        $share = round($share * 1000) / 1000; // same rounding as trade-in.js
        if ($share >= 1) {
            return 'The device does not reach a price after the checks.';
        }

        $keep = 1 - $share;
        $min = (float)$phone->price;
        $max = (float)($phone->price_max && $phone->price_max > $phone->price ? $phone->price_max : $phone->price);
        $lowFactor = (float)($checklist['lowFactor'] ?? 0.75);

        $this->model_id = $phone->id;
        $this->device = trim(($phone->brand ?: 'Apple') . ' ' . ($phone->name ?: $phone->model) . ($phone->storage ? ', ' . $phone->storage : ''));
        $this->answers = Json::encode($snapshot);
        $this->offer_high = (int)floor($max * $keep);
        $this->offer_low = (int)floor($min * $keep * $lowFactor);
        return null;
    }

    /** @return array [{q, a}] */
    public function getAnswerList()
    {
        return (array)Json::decode($this->answers ?: '[]');
    }

    /** @return string[] file names */
    public function getPhotoList()
    {
        return (array)Json::decode($this->photos ?: '[]');
    }

    public function getPhotoDir()
    {
        return Yii::getAlias('@app/data/trade-in-requests/' . (int)$this->id);
    }

    /** "450" or "340 – 450" */
    public function getOfferText()
    {
        return $this->offer_low === $this->offer_high
            ? number_format($this->offer_high)
            : number_format($this->offer_low) . ' – ' . number_format($this->offer_high);
    }

    /** Phone number for a tel: link. Local Jordanian 07… numbers get +962. */
    public function getPhoneIntl()
    {
        if (preg_match('/^07\d{8}$/', $this->phone)) {
            return '+962' . substr($this->phone, 1);
        }
        return $this->phone;
    }

    public static function countNew()
    {
        return (int)static::find()->where(['status' => self::STATUS_NEW])->count();
    }
}
