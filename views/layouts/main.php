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
$actionId = Yii::$app->controller->action->id ?? '';
$isGuest = Yii::$app->user->isGuest;

// The menu is only for signed-in staff. Guests see the logo and nothing else.
$navItems = $isGuest ? [] : [
    ['label' => 'Phones & prices', 'url' => ['/iphone-models/index'], 'active' => $controllerId === 'iphone-models' && $actionId !== 'photos'],
    ['label' => 'Model photos', 'url' => ['/iphone-models/photos'], 'active' => $controllerId === 'iphone-models' && $actionId === 'photos'],
    ['label' => 'Calculator', 'url' => ['/site/index'], 'active' => $controllerId === 'site' && $actionId === 'index'],
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
        <?php if (!$isGuest): ?>
        <button type="button" class="am-menu-toggle" aria-expanded="false" aria-controls="amMenu">
            <svg aria-hidden="true" viewBox="0 0 20 20" width="20" height="20"><path d="M3 6h14M3 10h14M3 14h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
            Menu
        </button>
        <nav aria-label="Main" class="am-menu" id="amMenu">
            <ul class="am-links">
                <?php foreach ($navItems as $item): ?>
                    <li>
                        <a href="<?= Url::to($item['url']) ?>" class="am-link<?= $item['active'] ? ' is-active' : '' ?>"<?= $item['active'] ? ' aria-current="page"' : '' ?>><?= Html::encode($item['label']) ?></a>
                    </li>
                <?php endforeach; ?>
            </ul>
            <?= Html::beginForm(['/site/logout'], 'post', ['class' => 'am-logout']) ?>
                <span class="am-user"><?= Html::encode(Yii::$app->user->identity->username) ?></span>
                <button type="submit" class="am-link am-link--out">Log out</button>
            <?= Html::endForm() ?>
        </nav>
        <?php endif; ?>
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

<?php if (!$isGuest): ?>
<script>
(function () {
    var t = document.querySelector('.am-menu-toggle'), m = document.getElementById('amMenu');
    if (!t || !m) return;
    t.addEventListener('click', function () {
        var open = t.getAttribute('aria-expanded') !== 'true';
        t.setAttribute('aria-expanded', String(open));
        m.classList.toggle('is-open', open);
    });
})();
</script>
<?php endif; ?>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
