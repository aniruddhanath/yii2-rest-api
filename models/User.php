<?php

namespace app\models;

use \Yii;

class User extends \yii\db\ActiveRecord implements \yii\web\IdentityInterface
{
    const INACTIVE = 0;
    const ACTIVE = 10;

    const ADMIN = 'admin';
    const CLIENT = 'client';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['status', 'default', 'value' => self::ACTIVE],
            ['status', 'in', 'range' => [self::ACTIVE, self::INACTIVE]],

            ['role', 'default', 'value' => self::CLIENT],
            ['role', 'in', 'range' => [self::ADMIN, self::CLIENT]],

            ['username', 'required'],

            ['email', 'filter', 'filter' => 'trim'],
            ['email', 'required'],
            ['email', 'email'],
            ['email', 'unique', 'message' => 'This email address has already been taken'],

            ['phone', 'filter', 'filter' => 'trim'],
            ['phone', 'required'],
            ['phone', 'integer'],
            ['phone', 'unique', 'message' => 'This phone number has already been taken'],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function findIdentity($id)
    {
        return static::find()->where(
            'id = :id and status = :status', [
                ':id' => $id,
                ':status' => self::ACTIVE
            ])->select(['id', 'username', 'email', 'phone', 'role', 'access_token', 'profile_picture'])->asArray()->one();
    }

    /**
     * Finds user by email
     *
     * @param string $email
     * @return static|null
     */
    public static function findByEmail($email)
    {
        return static::find()->where(
            'email = :email and status = :status', [
                ':email' => $email,
                ':status' => self::ACTIVE
            ])->one();
    }

    /**
     * Finds user by phone
     *
     * @param string $phone
     * @return static|null
     */
    public static function findByPhone($phone)
    {
        return static::find()->where(
            'phone = :phone and status = :status', [
                ':phone' => $phone,
                ':status' => self::ACTIVE
            ])->one();
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }

    /**
     * @inheritdoc
     */
    public function getAccessToken()
    {
        return $this->access_token;
    }

    /**
     * @inheritdoc
     */
    public function validateAccessToken($accessToken)
    {
        return $this->getAccessToken() === $accessToken;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return boolean if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        $user = static::findOne(['access_token' => $token]);

        if ($user && $user->token_expiry > time()) {
            // TBD : update access_token
            return $user;
        }

        return null;
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            $time = time();
            $this->access_token = Yii::$app->security->generateRandomString();
            $this->token_expiry = $time + Yii::$app->params['token_expiry'];

            if ($this->isNewRecord) {
                $this->created_at = $time;
            }

            $this->updated_at = $time;

            return true;
        }

        return false;
    }
}
