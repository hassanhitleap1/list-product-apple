<?php

namespace app\models;

use Yii;
use yii\base\Model;

class DynamicForm extends Model
{
    public $model;
    public $damage;
    public $scratched;
    public $batteryModel;
    public $additionalDamage;

    public function rules()
    {
        return [
            [['model', 'damage'], 'required'],
            [['scratched'], 'required', 'when' => function($model) {
                return $model->damage === 'yes';
            }, 'whenClient' => "function (attribute, value) {
                return $('input[name=\"damage\"]:checked').val() === 'yes';
            }"],
            [['batteryModel', 'additionalDamage'], 'safe'],
        ];
    }
}
