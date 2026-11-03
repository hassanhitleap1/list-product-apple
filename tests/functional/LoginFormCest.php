<?php

use app\models\User;

class LoginFormCest
{
    public function _before(\FunctionalTester $I)
    {
        \Yii::$app->cache->flush();
        User::deleteAll(['username' => 'func-tester']);
        $user = new User(['username' => 'func-tester']);
        $user->setPassword('secret-pass-1');
        $user->save();
        $I->amOnRoute('site/login');
    }

    public function _after(\FunctionalTester $I)
    {
        User::deleteAll(['username' => 'func-tester']);
    }

    public function openLoginPage(\FunctionalTester $I)
    {
        $I->see('Staff sign in', 'h1');
        $I->dontSee('admin/admin');
    }

    public function guestSeesNoMenuAndCannotOpenAdmin(\FunctionalTester $I)
    {
        $I->amOnPage('/');
        $I->dontSeeElement('#amMenu');
        $I->amOnRoute('iphone-models/index');
        $I->see('Staff sign in', 'h1');
    }

    public function loginWithEmptyCredentials(\FunctionalTester $I)
    {
        $I->submitForm('#login-form', []);
        $I->see('Username cannot be blank.');
        $I->see('Password cannot be blank.');
    }

    public function loginWithWrongCredentials(\FunctionalTester $I)
    {
        $I->submitForm('#login-form', [
            'LoginForm[username]' => 'func-tester',
            'LoginForm[password]' => 'wrong',
        ]);
        $I->see('Incorrect username or password.');
    }

    public function loginSuccessfully(\FunctionalTester $I)
    {
        $I->submitForm('#login-form', [
            'LoginForm[username]' => 'func-tester',
            'LoginForm[password]' => 'secret-pass-1',
        ]);
        $I->seeElement('#amMenu');
        $I->see('Log out');
        $I->see('Phone trade-in prices', 'h1');
        $I->dontSeeElement('form#login-form');
    }
}
