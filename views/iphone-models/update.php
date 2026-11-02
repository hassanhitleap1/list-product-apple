<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\IphoneModels $model */

$this->title = Yii::t('app', 'Edit: {name}', [
    'name' => $model->model,
]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Phone trade-in prices'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->model, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
?>
<div class="iphone-models-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
