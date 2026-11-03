<?php

use app\models\IphoneModels;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;

/** @var yii\web\View $this */
/** @var app\models\IphoneModelsSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = Yii::t('app', 'Phone trade-in prices');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="iphone-models-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a(Yii::t('app', 'Add phone'), ['create'], ['class' => 'btn btn-success']) ?>
        <?= Html::a(Yii::t('app', 'Model photos'), ['photos'], ['class' => 'btn btn-outline-primary']) ?>
        <?= Html::a(Yii::t('app', 'Open calculator'), ['/site/index'], ['class' => 'btn btn-outline-secondary']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            [
                'attribute' => 'image',
                'label' => '',
                'format' => 'raw',
                'filter' => false,
                'enableSorting' => false,
                'contentOptions' => ['style' => 'width:56px'],
                'value' => function (IphoneModels $m) {
                    return $m->getImageUrl()
                        ? Html::img($m->getImageUrl(), ['alt' => '', 'style' => 'width:44px;height:44px;object-fit:contain', 'loading' => 'lazy'])
                        : '';
                },
            ],
            [
                'attribute' => 'brand',
                'filter' => IphoneModels::BRANDS,
            ],
            'name',
            [
                'attribute' => 'storage',
                'value' => function (IphoneModels $m) { return $m->storage ?: Yii::t('app', 'All sizes'); },
            ],
            [
                'attribute' => 'price',
                'label' => Yii::t('app', 'Grade A price (JOD)'),
                'value' => function (IphoneModels $m) { return $m->getPriceText(); },
            ],
            [
                'attribute' => 'is_active',
                'format' => 'boolean',
                'filter' => [1 => Yii::t('app', 'Yes'), 0 => Yii::t('app', 'No')],
            ],
            'sort_order',
            [
                'class' => ActionColumn::className(),
                'urlCreator' => function ($action, IphoneModels $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'id' => $model->id]);
                }
            ],
        ],
    ]); ?>

</div>
