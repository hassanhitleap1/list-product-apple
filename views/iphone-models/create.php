<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\IphoneModels $model */

$this->title = Yii::t('app', 'Add phone');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Phone trade-in prices'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="iphone-models-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
