<?php

use app\models\IphoneModels;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\IphoneModels $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="iphone-models-form">

    <?php $form = ActiveForm::begin(); ?>

    <div class="row">
        <div class="col-md-4">
            <?= $form->field($model, 'brand')->dropDownList(IphoneModels::BRANDS) ?>
        </div>
        <div class="col-md-5">
            <?= $form->field($model, 'name')->textInput(['maxlength' => true, 'placeholder' => 'iPhone 16 Pro']) ?>
        </div>
        <div class="col-md-3">
            <?= $form->field($model, 'storage')->textInput(['maxlength' => true, 'placeholder' => '256GB']) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <?= $form->field($model, 'price')->input('number', ['step' => '1', 'min' => 0]) ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'price_max')->input('number', ['step' => '1', 'min' => 0]) ?>
        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'sort_order')->input('number', ['step' => '1']) ?>
        </div>
        <div class="col-md-2 pt-md-4">
            <?= $form->field($model, 'is_active')->checkbox() ?>
        </div>
    </div>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
