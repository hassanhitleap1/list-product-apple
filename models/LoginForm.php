<?php

namespace app\models;

use Yii;
use yii\base\Model;

/**
 * LoginForm is the model behind the login form.
 *
 * @property-read User|null $user
 *
 */
class LoginForm extends Model
{
    const MAX_FAILS = 5;
    const LOCK_SECONDS = 900;

    public $username;
    public $password;
    public $rememberMe = true;

    private $_user = false;


    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            // username and password are both required
            [['username', 'password'], 'required'],
            // rememberMe must be a boolean value
            ['rememberMe', 'boolean'],
            // password is validated by validatePassword()
            ['password', 'validatePassword'],
        ];
    }

    /**
     * Validates the password.
     * This method serves as the inline validation for password.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validatePassword($attribute, $params)
    {
        if ($this->hasErrors()) {
            return;
        }
        // Slow down password guessing: 5 wrong tries per IP + username, then wait 15 minutes.
        $cache = Yii::$app->cache ?: new \yii\caching\ArrayCache();
        $ip = Yii::$app->request instanceof \yii\web\Request ? (string)Yii::$app->request->userIP : 'cli';
        $key = ['login-fail', $ip, mb_strtolower((string)$this->username)];
        $fails = (int)$cache->get($key);
        if ($fails >= self::MAX_FAILS) {
            $this->addError($attribute, 'Too many wrong attempts. Try again in 15 minutes.');
            return;
        }

        $user = $this->getUser();
        if (!$user || !$user->validatePassword($this->password)) {
            $cache->set($key, $fails + 1, self::LOCK_SECONDS);
            $this->addError($attribute, 'Incorrect username or password.');
            return;
        }
        $cache->delete($key);
    }

    /**
     * Logs in a user using the provided username and password.
     * @return bool whether the user is logged in successfully
     */
    public function login()
    {
        if ($this->validate()) {
            return Yii::$app->user->login($this->getUser(), $this->rememberMe ? 3600*24*30 : 0);
        }
        return false;
    }

    /**
     * Finds user by [[username]]
     *
     * @return User|null
     */
    public function getUser()
    {
        if ($this->_user === false) {
            $this->_user = User::findByUsername($this->username);
        }

        return $this->_user;
    }
}
