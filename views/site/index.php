<?php

/** @var yii\web\View $this */
/** @var array $catalog   [{id, brand, name, storage, min, max}] */
/** @var array $checklist steps, reject message, lowFactor (config/checklist.php) */

use app\assets\TradeInAsset;
use yii\helpers\Html;
use yii\helpers\Json;

TradeInAsset::register($this);

$this->title = 'Trade-in price calculator | Action Mobile';

$this->registerJs('window.TRADE_IN = ' . Json::htmlEncode([
    'catalog' => $catalog,
    'steps' => $checklist['steps'],
    'lowFactor' => (float)($checklist['lowFactor'] ?? 0.75),
]) . ';', \yii\web\View::POS_HEAD);
?>

<div class="ti">

    <header class="ti-intro">
        <h1>What's your phone worth?</h1>
        <p class="ti-intro-ar" lang="ar" dir="rtl">كم سعر جهازك عند الاستبدال؟</p>
        <p class="ti-lede">Answer a few quick questions about your phone. We'll show your trade-in offer in JOD at the end.</p>
    </header>

    <div class="ti-grid">

        <section class="ti-wizard" aria-label="Trade-in questions">
            <div class="ti-progress">
                <div class="ti-progress-track" aria-hidden="true"><span id="tiProgressBar"></span></div>
                <p class="ti-progress-text" id="tiProgressText"></p>
            </div>

            <div class="ti-stage" id="tiStage" tabindex="-1"></div>

            <div class="ti-nav">
                <button type="button" class="ti-btn ti-btn--ghost" id="tiBack">
                    <svg aria-hidden="true" viewBox="0 0 20 20" width="18" height="18"><path d="M12.5 4.5L7 10l5.5 5.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    Back <span lang="ar">رجوع</span>
                </button>
                <button type="button" class="ti-btn ti-btn--primary" id="tiNext" hidden>Continue <span lang="ar">التالي</span></button>
            </div>
        </section>

        <aside class="ti-side" id="tiSide" aria-label="Your answers">
            <div class="ti-panel">
                <h2 class="ti-panel-title">Your device <span class="ti-ar" lang="ar">جهازك</span></h2>
                <div class="ti-panel-device">
                    <div id="tiSidePhoto" hidden></div>
                    <p class="ti-panel-phone" id="tiSidePhone">No phone chosen yet</p>
                </div>
                <div class="ti-mini" id="tiSideOffer" hidden>
                    <span class="ti-mini-label">Up to</span>
                    <span class="ti-mini-amount"><span id="tiSideAmount"></span> JOD</span>
                </div>
                <ol class="ti-answers" id="tiAnswers"></ol>
            </div>
            <p class="ti-fineprint">This is an estimate. We confirm the final price after checking the phone in store.
                <span lang="ar" dir="rtl">السعر تقديري ويتم تأكيده بعد فحص الجهاز في المحل.</span></p>
        </aside>
    </div>

    <!-- Mobile: running offer -->
    <div class="ti-bar" id="tiBar" hidden>
        <span class="ti-bar-label">Up to</span>
        <span class="ti-bar-amount"><span id="tiBarAmount">—</span> JOD</span>
        <span class="ti-bar-step" id="tiBarStep"></span>
    </div>

    <!-- Reject: stop the process -->
    <dialog class="ti-dialog ti-dialog--reject" id="tiReject" aria-labelledby="ti-h-reject">
        <div class="ti-reject-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" width="28" height="28"><path d="M12 7v6m0 4h.01" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"/><circle cx="12" cy="12" r="10" fill="none" stroke="currentColor" stroke-width="2"/></svg>
        </div>
        <h2 id="ti-h-reject">We can't price this device online</h2>
        <p class="ti-reject-reason" id="tiRejectReason"></p>
        <p><?= Html::encode($checklist['rejectMessage']) ?></p>
        <p lang="ar" dir="rtl" class="ti-reject-ar"><?= Html::encode($checklist['rejectMessage_ar']) ?></p>
        <div class="ti-dialog-actions">
            <button type="button" class="ti-btn ti-btn--primary" id="tiRejectRestart">Start over <span lang="ar">ابدأ من جديد</span></button>
            <button type="button" class="ti-btn ti-btn--ghost" id="tiRejectBack">Change my answer <span lang="ar">تعديل الإجابة</span></button>
        </div>
    </dialog>

    <!-- Disclaimer (once per browser) -->
    <dialog class="ti-dialog" id="tiDisclaimer" aria-labelledby="ti-h-disc">
        <h2 id="ti-h-disc">Before you start</h2>
        <p>Prices on this page are estimates. Please confirm the details with our team by phone or in store before you rely on them. Action Mobile is not responsible for any inaccurate information on this platform, and any use of it is at your own risk.</p>
        <div lang="ar" dir="rtl">
            <h2>قبل أن تبدأ</h2>
            <p>الأسعار في هذه الصفحة تقديرية. يرجى التحقق من التفاصيل مع فريقنا عبر الهاتف أو في المحل قبل الاعتماد عليها. لا تتحمل شركة Action Mobile مسؤولية أي معلومات غير دقيقة على هذه المنصة، وأي استخدام لها يكون على مسؤولية المستخدم.</p>
        </div>
        <form method="dialog">
            <button type="submit" class="ti-btn ti-btn--primary ti-btn--block">I understand <span lang="ar">موافق</span></button>
        </form>
    </dialog>
</div>
