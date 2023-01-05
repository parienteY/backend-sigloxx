<?php

namespace app\models;
use mdm\admin\components\UserStatus;

use Yii;

/**
 * This is the model class for table "user".
 *
 * @property int $id
 * @property string $username
 * @property string|null $password_hash
 * @property string|null $password_reset_token
 * @property string $email
 * @property int|null $status
 * @property string $nombres
 * @property string $apellidos
 * @property string|null $picture
 * @property string|null $ci
 * @property string|null $access_token
 * @property string|null $created_at
 * @property string|null $updated_at
 * @property string|null $removed
 * @property string|null $sesion_status
 * @property string|null $auth_key
 * @property string|null $tag_rol
 * @property int|null $id_unidad
 *
 * @property Unidad $unidad
 */
class User extends \yii\db\ActiveRecord implements
\yii\web\IdentityInterface
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['username', 'email', 'nombres', 'apellidos'], 'required'],
            [['status', 'id_unidad'], 'default', 'value' => null],
            [['status', 'id_unidad'], 'integer'],
            [['nombres', 'apellidos', 'picture', 'ci', 'access_token', 'sesion_status', 'tag_rol'], 'string'],
            [['created_at', 'updated_at', 'removed'], 'safe'],
            [['username', 'auth_key'], 'string', 'max' => 32],
            [['password_hash', 'password_reset_token', 'email'], 'string', 'max' => 255],
            [['id_unidad'], 'exist', 'skipOnError' => true, 'targetClass' => Unidad::class, 'targetAttribute' => ['id_unidad' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'username' => 'Username',
            'password_hash' => 'Password Hash',
            'password_reset_token' => 'Password Reset Token',
            'email' => 'Email',
            'status' => 'Status',
            'nombres' => 'Nombres',
            'apellidos' => 'Apellidos',
            'picture' => 'Picture',
            'ci' => 'Ci',
            'access_token' => 'Access Token',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'removed' => 'Removed',
            'sesion_status' => 'Sesion Status',
            'auth_key' => 'Auth Key',
            'tag_rol' => 'Tag Rol',
            'id_unidad' => 'Id Unidad',
        ];
    }

    /**
     * Gets query for [[Unidad]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUnidad()
    {
        return $this->hasOne(Unidad::class, ['id' => 'id_unidad']);
    }

    public static function findIdentity($id) {
        return static::findOne(['id' => $id, 'status' => UserStatus::ACTIVE]);
      }
  
      public static function findIdentityByAccessToken($token, $type = null) {
        $usuario = User::findOne(["access_token" => $token]);
        if (isset($usuario['access_token']) && $usuario['access_token'] === $token) {
          $usuario->access_token = null;
          return new static($usuario);
        }
        return null;
      }
  
  
      /**
       * Finds user by username
       *
       * @param string $username
       * @return static|null
       */
      public static function findByUsername($username) {
        // foreach (self::$users as $user) {
  
        //     if (strcasecmp($user['username'], $username) === 0) {
        //         return $user;
        //     }
        // }
  
        // return null;
      }
  
  
      /**
       * Finds user by password reset token
       *
       * @param string $token password reset token
       * @return static|null
       */
      public static function findByPasswordResetToken($token) {
        if (!static::isPasswordResetTokenValid($token)) {
          return null;
        }
  
        return static::findOne([
          'password_reset_token' => $token,
          'status' => \mdm\admin\components\UserStatus::ACTIVE,
        ]);
      }
  
      /**
       * Finds out if password reset token is valid
       *
       * @param string $token password reset token
       * @return boolean
       */
      public static function isPasswordResetTokenValid($token) {
        if (empty($token)) {
          return false;
        }
        $expire = Yii::$app->params['user.passwordResetTokenExpire'];
        $parts = explode('_', $token);
        $timestamp = (int)end($parts);
        return $timestamp + $expire >= time();
      }
  
      /**
       * @inheritdoc
       */
      public function getId() {
        return $this->getPrimaryKey();
      }
  
      /**
       * @inheritdoc
       */
      public function getAuthKey() {
        return $this->auth_key;
      }
  
      /**
       * @inheritdoc
       */
      public function validateAuthKey($authKey) {
        return $this->getAuthKey() === $authKey;
      }
  
      /**
       * Validates password
       *
       * @param string $password password to validate
       * @return boolean if password provided is valid for current user
       */
      public function validatePassword($password) {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
      }
  
      /**
       * Generates password hash from password and sets it to the model
       *
       * @param string $password
       */
      public function setPassword($password) {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
      }
  
      /**
       * Generates "remember me" authentication key
       */
      public function generateAuthKey() {
        $this->auth_key = Yii::$app->security->generateRandomString();
      }
  
      /**
       * Generates new password reset token
       */
      public function generatePasswordResetToken() {
        $this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
      }
  
      /**
       * Removes password reset token
       */
      public function removePasswordResetToken() {
        $this->password_reset_token = null;
      }
  
      public static function findByEmail($email) {
        return self::findOne(['email' => $email]);
      }
  
      /**
       * Finds user by [[username]]
       *
       * @return User|null
       */
      public function getUser() {
        if ($this->_user === false) {
          $this->_user = User::findByUsername($this->username);
        }
  
        return $this->_user;
      }
  
      public function getAuths() {
        return $this->hasMany(Auth::className(), ['user_id' => 'id']);
      }
  
      public function login() {
        return Yii::$app->user->login($this->getUser(), $this->rememberMe ? 3600 * 24 * 30 : 0);
  
      }
}
