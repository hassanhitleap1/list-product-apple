<?php

namespace tests\unit\models;

use app\models\LoginForm;
use app\models\User;

class LoginFormTest extends \Codeception\Test\Unit
{
    protected function _before()
    {
        \Yii::$app->cache->flush();
        User::deleteAll(['username' => 'unit-tester']);
        $user = new User(['username' => 'unit-tester']);
        $user->setPassword('secret-pass-1');
        $user->save();
    }

    protected function _after()
    {
        \Yii::$app->user->logout();
        User::deleteAll(['username' => 'unit-tester']);
    }

    public function testLoginNoUser()
    {
        $model = new LoginForm(['username' => 'not_existing_username', 'password' => 'x']);
        verify($model->login())->false();
        verify(\Yii::$app->user->isGuest)->true();
    }

    public function testLoginWrongPassword()
    {
        $model = new LoginForm(['username' => 'unit-tester', 'password' => 'wrong_password']);
        verify($model->login())->false();
        verify(\Yii::$app->user->isGuest)->true();
        verify($model->errors)->arrayHasKey('password');
    }

    public function testLoginCorrect()
    {
        $model = new LoginForm(['username' => 'unit-tester', 'password' => 'secret-pass-1']);
        verify($model->login())->true();
        verify(\Yii::$app->user->isGuest)->false();
    }

    public function testTooManyWrongPasswords()
    {
        for ($i = 0; $i < LoginForm::MAX_FAILS; $i++) {
            (new LoginForm(['username' => 'unit-tester', 'password' => 'wrong']))->login();
        }
        $model = new LoginForm(['username' => 'unit-tester', 'password' => 'secret-pass-1']);
        verify($model->login())->false();
        verify($model->getFirstError('password'))->stringContainsString('Too many');
    }
}
