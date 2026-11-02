<?php

/** @var yii\web\View $this */
/** @var array $catalog   [{id, brand, name, storage, min, max}] */
/** @var array $batteries [{id, label, deduct}] */
/** @var array $damages   [{id, label, label_ar, deduct}] */
/** @var float $lowFactor */

use app\assets\TradeInAsset;
use yii\helpers\Html;
use yii\helpers\Json;

TradeInAsset::register($this);

$this->title = 'Trade-in price calculator | Action Mobile';

$brands = array_values(array_unique(array_column($catalog, 'brand')));
$pct = function ($share) {
    return '−' . rtrim(rtrim(number_format($share * 100, 1), '0'), '.') . '%';
};

$this->registerJs('window.TRADE_IN = ' . Json::htmlEncode([
    'catalog' => $catalog,
    'batteries' => $batteries,
    'damages' => $damages,
    'lowFactor' => $lowFactor,
]) . ';', \yii\web\View::POS_HEAD);
?>

<div class="ti">

    <header class="ti-intro">
        <h1>What's your phone worth?</h1>
        <p class="ti-intro-ar" lang="ar" dir="rtl">كم سعر جهازك عند الاستبدال؟</p>
        <p class="ti-lede">Choose your phone and tell us its condition. Your trade-in offer in JOD updates as you go.</p>
    </header>

    <div class="ti-grid">

        <form class="ti-steps" id="tiForm" autocomplete="off" onsubmit="return false">

            <!-- 1. Phone -->
            <section class="ti-step" aria-labelledby="ti-h-phone">
                <h2 id="ti-h-phone"><span class="ti-num" aria-hidden="true">1</span>Your phone <span class="ti-ar" lang="ar">جهازك</span></h2>

                <fieldset class="ti-field">
                    <legend class="visually-hidden">Brand</legend>
                    <div class="ti-pills ti-pills--brand">
                        <?php foreach ($brands as $i => $brand): ?>
                            <label class="ti-pill">
                                <input type="radio" name="brand" value="<?= Html::encode($brand) ?>" <?= $i === 0 ? 'checked' : '' ?>>
                                <span><?= Html::encode($brand) ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </fieldset>

                <div class="ti-search">
                    <label for="tiSearch" class="visually-hidden">Search models</label>
                    <svg aria-hidden="true" viewBox="0 0 20 20" width="18" height="18"><circle cx="8.5" cy="8.5" r="6" fill="none" stroke="currentColor" stroke-width="2"/><path d="M13 13l5 5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                    <input type="search" id="tiSearch" placeholder="Search, e.g. 15 Pro or S24" inputmode="search">
                </div>

                <fieldset class="ti-field">
                    <legend class="ti-label">Model <span class="ti-ar" lang="ar">الموديل</span></legend>
                    <div class="ti-pills" id="tiModels"></div>
                    <p class="ti-empty" id="tiModelsEmpty" hidden>No phone matches that search. Try a shorter name, like “14” or “Fold”.</p>
                </fieldset>

                <fieldset class="ti-field" id="tiStorageField" hidden>
                    <legend class="ti-label">Storage <span class="ti-ar" lang="ar">السعة</span></legend>
                    <div class="ti-pills" id="tiStorage"></div>
                </fieldset>
            </section>

            <!-- 2. Condition -->
            <section class="ti-step" aria-labelledby="ti-h-cond">
                <h2 id="ti-h-cond"><span class="ti-num" aria-hidden="true">2</span>Condition <span class="ti-ar" lang="ar">حالة الجهاز</span></h2>

                <fieldset class="ti-field">
                    <legend class="visually-hidden">Does the phone have any damage or scratches?</legend>
                    <div class="ti-pills ti-pills--stack">
                        <label class="ti-pill">
                            <input type="radio" name="condition" value="good">
                            <span>Like new, no scratches or damage <small lang="ar">ممتاز، بدون خدوش أو كسور</small></span>
                        </label>
                        <label class="ti-pill">
                            <input type="radio" name="condition" value="damaged">
                            <span>Has scratches or damage <small lang="ar">فيه خدوش أو كسور</small></span>
                        </label>
                    </div>
                </fieldset>

                <fieldset class="ti-field" id="tiDamageField" hidden>
                    <legend class="ti-label">What's damaged? Choose all that apply.</legend>
                    <div class="ti-checks">
                        <?php foreach ($damages as $d): ?>
                            <label class="ti-check">
                                <input type="checkbox" name="damage[]" value="<?= Html::encode($d['id']) ?>">
                                <span class="ti-check-box" aria-hidden="true"></span>
                                <span class="ti-check-text">
                                    <?= Html::encode($d['label']) ?>
                                    <?php if (!empty($d['label_ar'])): ?><small lang="ar"><?= Html::encode($d['label_ar']) ?></small><?php endif; ?>
                                </span>
                                <span class="ti-deduct"><?= $pct($d['deduct']) ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </fieldset>
            </section>

            <!-- 3. Battery -->
            <?php if ($batteries): ?>
            <section class="ti-step" aria-labelledby="ti-h-batt">
                <h2 id="ti-h-batt"><span class="ti-num" aria-hidden="true">3</span>Battery health <span class="ti-ar" lang="ar">صحة البطارية</span></h2>
                <p class="ti-help">On iPhone: Settings › Battery › Battery Health. On Samsung: Settings › Battery › Battery information.</p>
                <fieldset class="ti-field">
                    <legend class="visually-hidden">Battery health</legend>
                    <div class="ti-pills">
                        <?php foreach ($batteries as $b): ?>
                            <label class="ti-pill">
                                <input type="radio" name="battery" value="<?= (int)$b['id'] ?>">
                                <span><?= Html::encode($b['label']) ?><?php if ($b['deduct'] > 0): ?> <em class="ti-pill-deduct"><?= $pct($b['deduct']) ?></em><?php endif; ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </fieldset>
            </section>
            <?php endif; ?>
        </form>

        <!-- Result -->
        <aside class="ti-result" id="tiResult" aria-labelledby="ti-h-result">
            <div class="ti-tag">
                <h2 id="ti-h-result" class="ti-tag-title">Your trade-in offer <span class="ti-ar" lang="ar">عرض الاستبدال</span></h2>
                <p class="ti-tag-phone" id="tiPhone">No phone chosen yet</p>

                <div class="ti-capsule" aria-live="polite" aria-atomic="true">
                    <span class="ti-amount" id="tiAmount">—</span>
                    <span class="ti-currency">JOD</span>
                </div>
                <p class="ti-status" id="tiStatus">Choose your phone to see its price.</p>

                <dl class="ti-breakdown" id="tiBreakdown" hidden>
                    <div><dt>Grade A price</dt><dd id="tiBase"></dd></div>
                    <div id="tiDeductRows"></div>
                    <div class="ti-breakdown-total"><dt>Total deduction</dt><dd id="tiDeductTotal"></dd></div>
                </dl>

                <button type="button" class="ti-reset" id="tiReset">Start over</button>
            </div>
            <p class="ti-fineprint">This is an estimate. We confirm the final price after checking the phone in store.
                <span lang="ar" dir="rtl">السعر تقديري ويتم تأكيده بعد فحص الجهاز في المحل.</span></p>
        </aside>
    </div>

    <!-- Mobile summary bar -->
    <a href="#tiResult" class="ti-bar" id="tiBar" hidden>
        <span class="ti-bar-label">Your offer</span>
        <span class="ti-bar-amount"><span id="tiBarAmount">—</span> JOD</span>
        <span class="ti-bar-link">Details</span>
    </a>

    <!-- Disclaimer -->
    <dialog class="ti-dialog" id="tiDisclaimer" aria-labelledby="ti-h-disc">
        <h2 id="ti-h-disc">Before you start</h2>
        <p>Prices on this page are estimates. Please confirm the details with our team by phone or in store before you rely on them. Action Mobile is not responsible for any inaccurate information on this platform, and any use of it is at your own risk.</p>
        <div lang="ar" dir="rtl">
            <h2>قبل أن تبدأ</h2>
            <p>الأسعار في هذه الصفحة تقديرية. يرجى التحقق من التفاصيل مع فريقنا عبر الهاتف أو في المحل قبل الاعتماد عليها. لا تتحمل شركة Action Mobile مسؤولية أي معلومات غير دقيقة على هذه المنصة، وأي استخدام لها يكون على مسؤولية المستخدم.</p>
        </div>
        <form method="dialog">
            <button type="submit" class="ti-accept" id="tiAccept">I understand <span lang="ar">موافق</span></button>
        </form>
    </dialog>
</div>
