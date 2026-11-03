<?php

/** @var yii\web\View $this */
/** @var string $content */

use app\assets\AppAsset;
use app\widgets\Alert;
use yii\bootstrap5\Breadcrumbs;
use yii\bootstrap5\Html;
use yii\helpers\Url;

AppAsset::register($this);

$this->registerCsrfMetaTags();
$this->registerMetaTag(['charset' => Yii::$app->charset], 'charset');
$this->registerMetaTag(['name' => 'viewport', 'content' => 'width=device-width, initial-scale=1, shrink-to-fit=no']);
$this->registerMetaTag(['name' => 'theme-color', 'content' => '#ffffff']);
$this->registerMetaTag(['name' => 'description', 'content' => $this->params['meta_description'] ?? 'Action Mobile trade-in price calculator']);
$this->registerMetaTag(['name' => 'keywords', 'content' => $this->params['meta_keywords'] ?? '']);
$this->registerLinkTag(['rel' => 'icon', 'type' => 'image/x-icon', 'href' => Yii::getAlias('@web/favicon.ico')]);
$this->registerLinkTag(['rel' => 'apple-touch-icon', 'href' => Yii::getAlias('@web/apple-touch-icon.png')]);

$controllerId = Yii::$app->controller->id;
$isAdmin = in_array($controllerId, ['iphone-models', 'battary-models'], true);

// Links shown in the navbar. Admin links only appear on the admin pages.
$navItems = $isAdmin
    ? [
        ['label' => 'Phones & prices', 'url' => ['/iphone-models/index'], 'active' => $controllerId === 'iphone-models' && Yii::$app->controller->action->id !== 'photos'],
        ['label' => 'Model photos', 'url' => ['/iphone-models/photos'], 'active' => $controllerId === 'iphone-models' && Yii::$app->controller->action->id === 'photos'],
        ['label' => 'Calculator', 'url' => ['/site/index'], 'active' => false],
    ]
    : [
        ['label' => 'Trade-in calculator', 'label_ar' => 'حاسبة الاستبدال', 'url' => ['/site/index'], 'active' => $controllerId === 'site' && Yii::$app->controller->action->id === 'index'],
    ];
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>" class="h-100">
<head>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body class="d-flex flex-column h-100">
<?php $this->beginBody() ?>

<header class="am-nav" id="header">
    <div class="container am-nav-inner">
        <a class="am-brand" href="<?= Url::home() ?>" aria-label="Action Mobile home">
            <?= Html::img('@web/image/logo-nav.png', ['alt' => 'Action Mobile', 'width' => 236, 'height' => 40]) ?>
        </a>
        <nav aria-label="Main">
            <ul class="am-links">
                <?php foreach ($navItems as $item): ?>
                    <li>
                        <a href="<?= Url::to($item['url']) ?>" class="am-link<?= $item['active'] ? ' is-active' : '' ?>"<?= $item['active'] ? ' aria-current="page"' : '' ?>>
                            <?= Html::encode($item['label']) ?>
                            <?php if (!empty($item['label_ar'])): ?><span lang="ar"><?= Html::encode($item['label_ar']) ?></span><?php endif; ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </nav>
    </div>
</header>

<main id="main" class="flex-shrink-0" role="main">
    <div class="container">
        <?php if (!empty($this->params['breadcrumbs'])): ?>
            <?= Breadcrumbs::widget(['links' => $this->params['breadcrumbs']]) ?>
        <?php endif ?>
        <?= Alert::widget() ?>
        <?= $content ?>
    </div>
</main>

<footer class="am-footer mt-auto" id="footer">
    <div class="container am-footer-inner">
        <span>&copy; <?= date('Y') ?> Action Mobile</span>
        <span lang="ar" dir="rtl">جميع الحقوق محفوظة</span>
    </div>
</footer>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
