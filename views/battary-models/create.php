<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\BattaryModels $model */

$this->title = Yii::t('app', 'Create Battary Models');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Battary Models'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="battary-models-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
