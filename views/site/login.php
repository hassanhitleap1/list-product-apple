<?php

/** @var yii\web\View $this */
/** @var app\models\LoginForm $model */

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;

$this->title = 'Staff sign in | Action Mobile';
?>
<div class="am-login">
    <div class="am-login-card">
        <h1>Staff sign in</h1>
        <p class="am-login-ar" lang="ar" dir="rtl">تسجيل دخول الموظفين</p>
        <p class="am-login-lede">Sign in to manage phones, prices and photos.</p>

        <?php $form = ActiveForm::begin(['id' => 'login-form']); ?>

        <?= $form->field($model, 'username')->textInput(['autofocus' => true, 'autocomplete' => 'username', 'autocapitalize' => 'none']) ?>

        <?= $form->field($model, 'password')->passwordInput(['autocomplete' => 'current-password']) ?>

        <?= $form->field($model, 'rememberMe')->checkbox()->label('Keep me signed in on this device') ?>

        <?= Html::submitButton('Sign in', ['class' => 'am-login-btn', 'name' => 'login-button']) ?>

        <?php ActiveForm::end(); ?>
    </div>
</div>
<?php
$this->registerCss(<<<CSS
.am-login { display: grid; place-items: center; padding: 2rem 0 3rem; }
.am-login-card { width: min(26rem, 100%); background: #fff; border: 1px solid var(--am-line); border-radius: 1.5rem; padding: 2rem; box-shadow: 0 12px 32px rgba(22, 32, 58, .06); }
.am-login-card h1 { font-size: 1.625rem; font-weight: 600; margin: 0; }
.am-login-ar { color: var(--am-blue-ink); margin: .25rem 0 0; text-align: left; }
.am-login-lede { color: var(--am-muted); margin: .5rem 0 1.5rem; }
.am-login-card .form-control { min-height: 2.875rem; border-radius: .875rem; border: 1.5px solid var(--am-line); }
.am-login-card .form-control:focus { border-color: var(--am-navy); box-shadow: 0 0 0 3px rgba(0, 164, 255, .35); }
.am-login-card .form-label { font-weight: 500; }
.am-login-btn { width: 100%; min-height: 3rem; border: 0; border-radius: 999px; background: var(--am-navy); color: #fff; font: inherit; font-weight: 500; margin-top: .5rem; }
.am-login-btn:hover { background: #24304f; }
.am-login-btn:focus-visible { outline: 3px solid var(--am-blue); outline-offset: 2px; }
CSS);
