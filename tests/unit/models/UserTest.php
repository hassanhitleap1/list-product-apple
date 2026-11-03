<?php

namespace tests\unit\models;

use app\models\User;

class UserTest extends \Codeception\Test\Unit
{
    private $user;

    protected function _before()
    {
        User::deleteAll(['username' => 'unit-tester']);
        $this->user = new User(['username' => 'unit-tester']);
        $this->user->setPassword('secret-pass-1');
        verify($this->user->save())->true();
    }

    protected function _after()
    {
        User::deleteAll(['username' => 'unit-tester']);
    }

    public function testFindUser()
    {
        verify(User::findIdentity($this->user->id)->username)->equals('unit-tester');
        verify(User::findByUsername('unit-tester'))->notEmpty();
        verify(User::findByUsername('not-a-user'))->empty();
        verify(User::findIdentityByAccessToken('anything'))->empty();
    }

    public function testPasswordIsHashed()
    {
        verify($this->user->password_hash)->notEquals('secret-pass-1');
        verify($this->user->validatePassword('secret-pass-1'))->true();
        verify($this->user->validatePassword('wrong'))->false();
    }

    public function testAuthKey()
    {
        verify($this->user->validateAuthKey($this->user->auth_key))->true();
        verify($this->user->validateAuthKey('wrong-key'))->false();
    }

    public function testNoDemoAccounts()
    {
        verify(User::findByUsername('demo'))->empty();
    }
}
