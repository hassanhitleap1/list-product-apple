<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

/**
 * Back-office user (table "user"). Passwords are stored as bcrypt hashes.
 * Create or change accounts from the console: php yii user/create <username>
 *
 * @property int $id
 * @property string $username
 * @property string $password_hash
 * @property string $auth_key
 * @property int $created_at
 * @property int $updated_at
 */
class User extends ActiveRecord implements IdentityInterface
{
    const MIN_PASSWORD = 8;

    public static function tableName()
    {
        return '{{%user}}';
    }

    public function rules()
    {
        return [
            [['username'], 'trim'],
            [['username', 'password_hash', 'auth_key'], 'required'],
            [['username'], 'string', 'min' => 3, 'max' => 64],
            [['username'], 'match', 'pattern' => '/^[A-Za-z0-9_.\-@]+$/', 'message' => 'Use letters, numbers, dot, dash, underscore or @ only.'],
            [['username'], 'unique'],
        ];
    }

    public function beforeSave($insert)
    {
        $now = time();
        if ($insert) {
            $this->created_at = $now;
        }
        $this->updated_at = $now;
        return parent::beforeSave($insert);
    }

    /** Sets a new password (stored hashed) and signs out other sessions. */
    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
        $this->auth_key = Yii::$app->security->generateRandomString(32);
    }

    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword((string)$password, $this->password_hash);
    }

    public static function findByUsername($username)
    {
        return static::findOne(['username' => (string)$username]);
    }

    /* ---------- IdentityInterface ---------- */

    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id]);
    }

    public static function findIdentityByAccessToken($token, $type = null)
    {
        return null; // No API tokens.
    }

    public function getId()
    {
        return $this->id;
    }

    public function getAuthKey()
    {
        return $this->auth_key;
    }

    public function validateAuthKey($authKey)
    {
        return is_string($authKey) && $this->auth_key !== null && hash_equals($this->auth_key, $authKey);
    }
}
