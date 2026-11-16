<?php

use app\models\TradeInRequest;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var string $status new | done | all */
/** @var string $q */
/** @var array $counts ['new' => int, 'done' => int] */

$this->title = 'Customer requests';
$this->params['breadcrumbs'][] = $this->title;

$tabs = [
    'new' => 'New (' . $counts['new'] . ')',
    'done' => 'Done (' . $counts['done'] . ')',
    'all' => 'All',
];
?>
<div class="rq">
    <h1><?= Html::encode($this->title) ?></h1>
    <p class="rq-lede">Requests customers sent from the calculator, with their name, phone number and photos of the device. Open one to see the photos and answers.</p>

    <div class="rq-bar">
        <ul class="nav nav-pills rq-tabs">
            <?php foreach ($tabs as $key => $label): ?>
                <li class="nav-item">
                    <a class="nav-link<?= $status === $key ? ' active' : '' ?>"<?= $status === $key ? ' aria-current="page"' : '' ?> href="<?= Url::to(['index', 'status' => $key, 'q' => $q ?: null]) ?>"><?= Html::encode($label) ?></a>
                </li>
            <?php endforeach; ?>
        </ul>
        <form method="get" action="<?= Url::to(['index']) ?>" class="rq-search" role="search">
            <input type="hidden" name="status" value="<?= Html::encode($status) ?>">
            <input type="search" name="q" value="<?= Html::encode($q) ?>" class="form-control" placeholder="Name, phone or model" aria-label="Search requests">
            <button type="submit" class="btn btn-outline-secondary">Search</button>
        </form>
    </div>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'tableOptions' => ['class' => 'table table-hover align-middle rq-table'],
        'emptyText' => $status === 'new' ? 'No new requests.' : 'No requests found.',
        'rowOptions' => function (TradeInRequest $m) {
            return ['class' => $m->status === TradeInRequest::STATUS_NEW ? 'is-new' : ''];
        },
        'columns' => [
            [
                'attribute' => 'id',
                'format' => 'raw',
                'value' => function (TradeInRequest $m) {
                    return Html::a('#' . $m->id, ['view', 'id' => $m->id], ['class' => 'rq-open']);
                },
            ],
            [
                'attribute' => 'created_at',
                'value' => function (TradeInRequest $m) {
                    return Yii::$app->formatter->asDatetime($m->created_at, 'php:d M Y, H:i');
                },
            ],
            [
                'attribute' => 'name',
                'format' => 'raw',
                'value' => function (TradeInRequest $m) {
                    return Html::a(Html::encode($m->name), ['view', 'id' => $m->id]);
                },
            ],
            [
                'attribute' => 'phone',
                'format' => 'raw',
                'value' => function (TradeInRequest $m) {
                    return Html::a(Html::encode($m->phone), 'tel:' . $m->getPhoneIntl(), ['dir' => 'ltr']);
                },
            ],
            'device',
            [
                'attribute' => 'offer_high',
                'value' => function (TradeInRequest $m) { return $m->getOfferText(); },
                'contentOptions' => ['class' => 'text-nowrap'],
            ],
            [
                'label' => 'Photos',
                'value' => function (TradeInRequest $m) { return count($m->getPhotoList()); },
            ],
            [
                'attribute' => 'status',
                'format' => 'raw',
                'value' => function (TradeInRequest $m) {
                    return $m->status === TradeInRequest::STATUS_NEW
                        ? '<span class="badge text-bg-primary">New</span>'
                        : '<span class="badge text-bg-secondary">Done</span>';
                },
            ],
        ],
    ]) ?>
</div>
