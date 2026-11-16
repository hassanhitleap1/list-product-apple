<?php

use app\models\TradeInRequest;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var app\models\TradeInRequest $model */

$this->title = 'Request #' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Customer requests', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$isNew = $model->status === TradeInRequest::STATUS_NEW;
$photos = $model->getPhotoList();
$waNumber = ltrim($model->getPhoneIntl(), '+');
?>
<div class="rq rq-view">
    <div class="rq-head">
        <div>
            <h1><?= Html::encode($model->name) ?></h1>
            <p class="rq-meta">
                <?= Html::encode($this->title) ?> · sent <?= Yii::$app->formatter->asDatetime($model->created_at, 'php:d M Y, H:i') ?>
                · <?= $isNew ? '<span class="badge text-bg-primary">New</span>' : '<span class="badge text-bg-secondary">Done</span>' ?>
            </p>
        </div>
        <div class="rq-actions">
            <a class="btn btn-primary" href="tel:<?= Html::encode($model->getPhoneIntl()) ?>">Call <span dir="ltr"><?= Html::encode($model->phone) ?></span></a>
            <a class="btn btn-outline-success" href="https://wa.me/<?= Html::encode($waNumber) ?>" target="_blank" rel="noopener">WhatsApp</a>
            <?= Html::beginForm(['status', 'id' => $model->id], 'post', ['class' => 'd-inline']) ?>
                <button type="submit" class="btn btn-outline-secondary"><?= $isNew ? 'Mark as done' : 'Move back to new' ?></button>
            <?= Html::endForm() ?>
        </div>
    </div>

    <div class="rq-grid">
        <section class="rq-card">
            <h2>Device and offer</h2>
            <dl class="rq-dl">
                <div><dt>Device</dt><dd><?= Html::encode($model->device) ?></dd></div>
                <div><dt>Offer shown</dt><dd><strong><?= $model->getOfferText() ?> JOD</strong></dd></div>
                <div><dt>Phone</dt><dd dir="ltr"><?= Html::encode($model->phone) ?></dd></div>
            </dl>
            <h3>Answers</h3>
            <dl class="rq-dl rq-dl--answers">
                <?php foreach ($model->getAnswerList() as $row): ?>
                    <div><dt><?= Html::encode($row['q'] ?? '') ?></dt><dd><?= Html::encode($row['a'] ?? '') ?></dd></div>
                <?php endforeach; ?>
            </dl>
        </section>

        <section class="rq-card">
            <h2>Photos (<?= count($photos) ?>)</h2>
            <?php if ($photos): ?>
                <ul class="rq-photos">
                    <?php foreach ($photos as $i => $file): $url = Url::to(['photo', 'id' => $model->id, 'file' => $file]); ?>
                        <li>
                            <a href="<?= Html::encode($url) ?>" target="_blank" rel="noopener">
                                <img src="<?= Html::encode($url) ?>" alt="Photo <?= $i + 1 ?> of the customer's phone" loading="lazy">
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <p class="rq-hint">Click a photo to open it full size.</p>
            <?php else: ?>
                <p class="rq-hint">No photos.</p>
            <?php endif; ?>
        </section>
    </div>

    <?= Html::beginForm(['delete', 'id' => $model->id], 'post', ['class' => 'rq-delete', 'onsubmit' => "return confirm('Delete this request and its photos? This cannot be undone.');"]) ?>
        <button type="submit" class="btn btn-link text-danger">Delete request</button>
    <?= Html::endForm() ?>
</div>
