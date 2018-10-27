<?php

namespace app\models;

use app\components\Converter;
use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

/**
 * User model
 *
 * @property integer $id
 * @property string $username
 * @property string $password_hash
 * @property string $password_reset_token
 * @property string $email
 * @property string $auth_key
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 * @property string $password write-only password
 */
class User extends ActiveRecord implements IdentityInterface
{
    const STATUS_DELETED = 0;
    const STATUS_ACTIVE = 10;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%user}}';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['status', 'default', 'value' => self::STATUS_ACTIVE],
            ['status', 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_DELETED]],
            ['is_email_confirmed', 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        throw new NotSupportedException('"findIdentityByAccessToken" is not implemented.');
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * {@inheritdoc}
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * Finds user by username
     *
     * @param string $username
     *
     * @return static|null
     */
    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * Finds user by email
     *
     * @param string $email
     *
     * @return static|null
     */
    public static function findByEmail($email)
    {
        return static::findOne(['email' => $email, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * Finds user by password reset token
     *
     * @param string $token password reset token
     *
     * @return static|null
     */
    public static function findByPasswordResetToken($token)
    {
        if (!static::isPasswordResetTokenValid($token)) {
            return null;
        }

        return static::findOne([
            'password_reset_token' => $token,
            'status' => self::STATUS_ACTIVE,
        ]);
    }

    /**
     * Finds out if password reset token is valid
     *
     * @param string $token password reset token
     *
     * @return bool
     */
    public static function isPasswordResetTokenValid($token)
    {
        if (empty($token)) {
            return false;
        }

        $timestamp = (int) substr($token, strrpos($token, '_') + 1);
        $expireTime = Yii::$app->params['user.passwordResetTokenExpire'];

        return $timestamp + $expireTime >= time();
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     *
     * @return bool if password provided is valid for current user
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
     * Generates "remember me" authentication key
     */
    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    /**
     * Generates new password reset token
     */
    public function generatePasswordResetToken()
    {
        $this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Removes password reset token
     */
    public function removePasswordResetToken()
    {
        $this->password_reset_token = null;
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'email' => 'Email',
            'rating' => 'Social Rating',
        ];
    }

    public function beforeSave($insert)
    {
        if ($insert) {
            $this->is_email_confirmed = false;
        }
        return parent::beforeSave($insert);
    }

    /**
     * Sends an email with a link, for confirming the registration.
     *
     * @return bool whether the email was send
     */
    public function sendConfirmationEmail($user)
    {
        $link = Yii::$app->urlManager->createAbsoluteUrl(['site/confirm', 'id' => $user->id, 'auth_key' => $user->auth_key]);

        return Yii::$app
            ->mailer
            ->compose('register', [
                'user' => $user,
                'link' => $link,
            ])
            ->setFrom([Yii::$app->params['adminEmail'] => Yii::$app->name . ' Robot'])
            ->setTo($user->email)
            ->setSubject('Register for ' . Yii::$app->name)
            ->send();
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMoqups()
    {
        return $this->hasMany(Moqup::className(), ['user_id' => 'id']);
    }

    /**
     * @return integer The number of moqups of the user
     */
    public function getMoqupsCount()
    {
        return count($this->moqups);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFollowedMoqups()
    {
        return $this->hasMany(Moqup::className(), ['id' => 'moqup_id'])->viaTable('user_moqup_follow', ['user_id' => 'id']);
    }

    /**
     * Get a list of id of the moqups beign followed by the user
     * @return array the list of moqups id
     */
    public function getFollowedMoqupsId()
    {
        $ids = [];

        if (!empty($this->followedMoqups)) {
            $ids = array_merge($ids, \yii\helpers\ArrayHelper::getColumn($this->followedMoqups, 'id'));
        }

        return $ids;
    }

    /**
     * @return integer The max ammount of moqups the user can have
     */
    public function getMaxMoqupsNumber()
    {
        $setting = Setting::findOne(['key' => 'moqup_entries_limit']);
        $maxMoqup = ($setting != null) ? $setting->value : 1;

        return $maxMoqup * $this->rating;
    }

    /**
     * @return boolean If the user reach its moqups limit
     */
    public function getReachMaxMoqupsNumber()
    {
        return $this->moqupsCount >= $this->maxMoqupsNumber;
    }

    /**
     * @return integer The total amount of moqups size in bytes
     */
    public function getTotalMoqupsSize()
    {
        $size = 0;

        if (!empty($this->moqups)) {
            foreach ($this->moqups as $moq) {
                $size += strlen($moq->html);

                if ($moq->css != null) {
                    $size += strlen($moq->css->css);
                }
            }
        }

        return Converter::byteToMega($size);
    }

    /**
     * @return integer The max size that the user can have between moqups
     */
    public function getMaxMoqupsSize()
    {
        $setting = Setting::findOne(['key' => 'moqup_bytes_limit']);
        $maxLength = ($setting != null) ? $setting->value : 1;

        return Converter::byteToMega($maxLength * $this->rating);
    }

    /**
     * @return boolean If the user reach the max size
     */
    public function getReachMaxMoqupsSize()
    {
        return $this->totalMoqupsSize >= $this->maxMoqupsSize;
    }

    /**
     * @return integer The current rating of the user
     */
    public function getRating()
    {
        $rating = Rating::find()->where(['user_id' => $this->id])->orderBy('id DESC')->one();

        return ($rating != null) ? $rating->balance : 0;
    }

    /**
     * Add user rating
     *
     * @param int $ratingType integer value for rating type constants defined in Rating model
     * @param int $ratingAmount rating amount to be added
     * @param bool $existMultiple, false: given $ratingType can exist only once for a user
     *
     * @return bool true|false
     */
    public function addRating($ratingType = Rating::CONFIRM_EMAIL, $ratingAmount = 1, $existMultiple = true)
    {
        $id = $this->id;
        $balance = $this->rating + $ratingAmount;

        $commit = false;
        $rating = null;

        //If a rating can exist only once
        if (!$existMultiple) {
            $rating = Rating::findOne([
                'user_id' => $id,
                'type' => $ratingType,
            ]);
        }

        if ($rating == null) {
            $rating = new Rating([
                'user_id' => $id,
                'balance' => $balance,
                'amount' => $ratingAmount,
                'type' => $ratingType,
            ]);

            if ($rating->save()) {
                $commit = true;
            }
        }
        return $commit;
    }

    /**
     * Add rating for user's referrer if exists
     *
     * @return bool true|false
     */
    public function addReferrerBonus()
    {
        $user = $this->getReferrer()->one();

        if ($user != null) {
            return $user->addRating(Rating::REFERRAL_BONUS, 1);
        }
        return false;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getReferrals(int $level = 1)
    {
        return $this->hasMany(User::class, ['referrer_id' => 'id']);

    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getReferrer()
    {
        return $this->hasOne(User::class, ['id' => 'referrer_id']);
    }


    /**
     * Add user rating
     *
     * @return bool true|false
     */
    public function addRating()
    {
        $signupRating = 1;
        $id = $this->id;
        $balance = $this->rating + $signupRating;

        $commit = false;

        $rating = Rating::findOne([
            'user_id' => $id,
            'type' => Rating::CONFIRM_EMAIL,
        ]);
        if ($rating == null) {
            $rating = new Rating([
                'user_id' => $id,
                'balance' => $balance,
                'amount' => $signupRating,
                'type' => Rating::CONFIRM_EMAIL,
            ]);

            if ($rating->save()) {
                $commit = true;
            }
        }
        return $commit;
    }
}
