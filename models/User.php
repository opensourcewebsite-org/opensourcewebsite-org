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
 * @property string $name
 */
class User extends ActiveRecord implements IdentityInterface
{
    const STATUS_DELETED = 0;
    const STATUS_ACTIVE = 10;

    const DATE_FORMAT = 'd.m.Y';

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
            ['is_authenticated', 'boolean'],
            ['name', 'string'],
            [['gender_id', 'currency_id'], 'integer'],
            ['email', 'email'],
            [['timezone'], 'default', 'value' => 'UTC'],
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

    public static function createWithRandomPassword()
    {
        $user = new User();
        $user->password = Yii::$app->security->generateRandomString();
        $user->generateAuthKey();
        return $user;
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
            $this->is_authenticated = false;
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
    public function getIssues()
    {
        return $this->hasMany(Issue::className(), ['user_id' => 'id']);
    }

    /**
     * @return integer The number of issues of the user
     */
    public function getIssuesCount()
    {
        return count($this->issues);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSupportGroup()
    {
        return $this->hasMany(SupportGroup::className(), ['user_id' => 'id']);
    }

    /**
     * @return integer The number of support group of the user
     */
    public function getSupportGroupCount()
    {
        return count($this->supportGroup);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSupportGroupMember()
    {
        return $this->hasMany(SupportGroupMember::className(), ['support_group_id' => 'id'])->viaTable('support_group', ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSupportGroupCommand()
    {
        return $this->hasMany(SupportGroupCommand::className(), ['support_group_id' => 'id'])->viaTable('support_group', ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSupportGroupBot()
    {
        return $this->hasMany(SupportGroupBot::className(), ['support_group_id' => 'id'])->viaTable('support_group', ['user_id' => 'id']);
    }

    /**
     * @return integer The number of members of the support group
     */
    public function getSupportGroupMemberCount()
    {
        return count($this->supportGroupMember);
    }

    /**
     * @return integer The number of bots of the support group
     */
    public function getBotsCount()
    {
        return count($this->supportGroupBot);
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
        $setting = Setting::findOne(['key' => 'moqup_quantity_value_per_one_rating']);
        $maxMoqup = ($setting != null) ? $setting->value : 1;

        return $maxMoqup * $this->rating;
    }

    /**
     * @return integer The max ammount of issues the user can have
     */
    public function getMaxIssuesNumber()
    {
        $setting = Setting::findOne(['key' => 'issue_quantity_value_per_one_rating']);
        $maxIssue = ($setting != null) ? $setting->value : 1;

        return $maxIssue * $this->rating;
    }

    /**
     * @return integer The max ammount of support groups the user can have
     */
    public function getMaxSupportGroup()
    {
        $setting = Setting::findOne(['key' => 'support_group_quantity_value_per_one_rating']);
        $settingQty = ($setting != null) ? $setting->value : 1;

        return $settingQty * $this->rating;
    }

    /**
     * @return integer The max ammount of support group members the user can have
     */
    public function getMaxSupportGroupMember()
    {
        $setting = Setting::findOne(['key' => 'support_group_member_quantity_value_per_one_rating']);
        $settingQty = ($setting != null) ? $setting->value : 1;

        return $settingQty * $this->rating;
    }

    /**
     * @return integer The max amount of bots the user can have
     */
    public function getMaxBots()
    {
        $setting = Setting::findOne(['key' => 'support_group_bot_quantity_value_per_one_rating']);
        $settingQty = ($setting != null) ? $setting->value : 1;

        return $settingQty * $this->rating;
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
        $maxLength = $this->maxMoqupsHtmlSize + $this->maxMoqupsCssSize;

        return Converter::byteToMega($maxLength * $this->rating);
    }

    public function getMaxMoqupsHtmlSize()
    {
        $setting = Setting::findOne(['key' => 'moqup_html_field_max_value']);
        return ($setting != null) ? $setting->value : 1;
    }

    public function getMaxMoqupsCssSize()
    {
        $setting = Setting::findOne(['key' => 'moqup_css_field_max_value']);
        return ($setting != null) ? $setting->value : 1;
    }

    /**
     * @return boolean If the user reach the max size
     */
    public function getReachMaxMoqupsSize()
    {
        return $this->totalMoqupsSize >= $this->maxMoqupsSize;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRatings()
    {
        return $this->hasMany(Rating::className(), ['user_id' => 'id']);
    }

    /**
     * @return integer The current rating of the user
     */
    public function getRating()
    {
        $balance = Rating::find()->select(['balance' => 'sum(amount)'])->where(['user_id' => $this->id])->groupBy('user_id')->scalar();

        return ($balance != null) ? $balance : 0;
    }

    /**
     * @param bool $format whether to return formatted percent value or not
     * @return mixed The number in percentage
     */
    public function getOverallRatingPercent($format = true)
    {
        $totalRating = Rating::getTotalRating();
        return Converter::percentage($this->rating, $totalRating, $format);
    }

    /**
     * @return integer The active rating of the user
     */
    public function getActiveRating()
    {
        $setting = Setting::findOne(['key' => 'days_count_to_calculate_active_rating']);
        $daysActiveRating = intval($setting->value);

        $balance = Rating::find()
            ->where(['>', 'created_at', time() - 3600 * 24 * $daysActiveRating])
            ->andWhere(['user_id' => $this->id])
            ->sum('amount');

        return ($balance != null) ? $balance : 0;
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
     * @return \yii\db\ActiveQuery
     */
    public function getReferrals(int $level = 1)
    {
        return User::find()->where([
            'referrer_id' => $this->id,
            'is_authenticated' => true,
        ]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getReferrer()
    {
        return $this->hasOne(User::class, ['id' => 'referrer_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getContact()
    {
        return $this->hasOne(Contact::class, ['link_user_id' => 'id'])
            ->onCondition(['user_id' => Yii::$app->user->id]);
    }

    public function getDisplayName()
    {
        return $this->contact->getContactName();
    }

    public function getCompanies()
    {
        return $this->hasMany(Company::class, ['id' => 'company_id'])
            ->viaTable('company_user', ['user_id' => 'id']);
    }

    public function getGender()
    {
        return $this->hasOne(Gender::class, [ 'id' => 'gender_id' ]);
    }

    public function getCurrency()
    {
        return $this->hasOne(Currency::class, [ 'id' => 'currency_id' ]);
    }

    public function getLanguages()
    {
        return $this->hasMany(UserLanguage::class, [ 'user_id' => 'id' ]);
    }

    public function getCitizenships()
    {
        return $this->hasMany(UserCitizenship::class, [ 'user_id' => 'id' ]);
    }
}
