<?php

namespace app\models;

use Yii;
use app\components\Converter;
use app\models\queries\ContactQuery;
use app\models\queries\UserQuery;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;
use yii\db\Query;

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
 * @property integer $last_activity_at
 * @property string $password write-only password
 * @property string $name
 * @property string $birthday
 * @property integer $timezone
 * @property integer $referrer_id
 * @property integer $gender_id
 * @property integer $currency_id
 * @property integer $sexuality_id
 * @property bool $is_authenticated
 * @property bool $gender
 *
 * @property null|\app\modules\bot\models\User $botUser
 * @property Contact $contact
 * @property Contact[] $contactsFromMe
 * @property Contact[] $contactsToMe
 */
class User extends ActiveRecord implements IdentityInterface
{
    const STATUS_DELETED = 0;
    const STATUS_ACTIVE = 10;

    const DATE_FORMAT = 'Y-m-d';

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
            ['is_authenticated', 'boolean'],
            [['gender_id', 'sexuality_id', 'currency_id', 'rating'], 'integer'],
            [['created_at', 'updated_at', 'last_activity_at'], 'integer'],
            [['created_at', 'updated_at', 'last_activity_at'], 'default', 'value' => time()],
            ['birthday', 'date'],
            [['timezone'], 'default', 'value' => 0],
            [['timezone'], 'integer', 'min' => -720, 'max' => 840],
            ['status',
                'default',
                'value' => self::STATUS_ACTIVE],
            ['status',
                'in',
                'range' => [self::STATUS_ACTIVE, self::STATUS_DELETED]],

            ['email', 'email'],
            ['email',
                'unique',
                'message' => 'Email must be unique.'
            ],
            ['username', 'trim'],
            ['username',
                'match',
                'pattern' => '/^[a-zA-Z0-9_]+$/i',
                'message' => 'Username can contain only letters, numbers and \'_\' symbols'
            ],
            ['username', 'validateUsernameUnique'],
            ['username', 'default', 'value' => null],

            ['name', 'string'],
            ['name', 'trim'],
            ['name', 'validateNameString'],
        ];
    }

    /*
     * Username validation
     */
    public function validateUsernameUnique()
    {
        $oldUsername = $this->getOldAttribute('username');
        if (is_numeric($this->username)) {
            $this->addError('username', 'User name can\'t be number');
        }

        if (strcasecmp($oldUsername, $this->username) !== 0) {
            $isUserInDB = User::findOne(['username' => $this->username]);
            if ($isUserInDB) {
                $this->addError('username', 'User name must be unique.');
            }
        }
    }

    /*
     * Name validation
     */
    public function validateNameString()
    {
        $oldName = $this->getOldAttribute('name');
        if ($this->name == $oldName) {
            return;
        }

        if (is_numeric($this->name)) {
            $this->addError('name', 'Name can\'t be number');
        }
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

    public function setActive(): void
    {
        $this->is_authenticated = true;
        $this->status           = self::STATUS_ACTIVE;
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
            'username' => 'Username (optional)',
            'name' => 'Name (optional)',
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
        $link = Yii::$app->urlManager->createAbsoluteUrl(['site/confirm', 'id' => $user->id, 'authKey' =>
            $user->auth_key]);

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
        $maxMoqup = Setting::getValue('moqup_quantity_value_per_one_rating');

        return $maxMoqup * $this->rating;
    }

    /**
     * @return integer The max ammount of issues the user can have
     */
    public function getMaxIssuesNumber()
    {
        $maxIssue = Setting::getValue('issue_quantity_value_per_one_rating');

        return $maxIssue * $this->rating;
    }

    /**
     * @return integer The max ammount of support groups the user can have
     */
    public function getMaxSupportGroup()
    {
        $settingQty = Setting::getValue('support_group_quantity_value_per_one_rating');

        return $settingQty * $this->rating;
    }

    /**
     * @return integer The max ammount of support group members the user can have
     */
    public function getMaxSupportGroupMember()
    {
        $settingQty = Setting::getValue('support_group_member_quantity_value_per_one_rating');

        return $settingQty * $this->rating;
    }

    /**
     * @return integer The max amount of bots the user can have
     */
    public function getMaxBots()
    {
        $settingQty = Setting::getValue('support_group_bot_quantity_value_per_one_rating');

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
        return Setting::getValue('moqup_html_field_max_value');
    }

    public function getMaxMoqupsCssSize()
    {
        return Setting::getValue('moqup_css_field_max_value');
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
        return $this->rating;
    }

    /**
     * @param bool $format whether to return formatted percent value or not
     * @return mixed The number in percentage
     */
    public function getOverallRatingPercent($format = true)
    {
        $totalRating = self::getTotalRating();

        return Converter::percentage($this->rating, $totalRating, $format);
    }

    /**
     * @return integer The active rating of the user
     */
    public function getActiveRating()
    {
        $daysActiveRating = intval(Setting::getValue('days_count_to_calculate_active_rating'));

        $activeRating = Rating::find()
            ->where(['>', 'created_at', time() - 3600 * 24 * $daysActiveRating])
            ->andWhere(['user_id' => $this->id])
            ->sum('amount');

        return $activeRating ?: 0;
    }

    public function getRank()
    {
        $subQuery = (new Query)
           ->select([
               'ROW_NUMBER() OVER(ORDER BY rating DESC, created_at ASC) `rank`',
               'id',
           ])
           ->from(self::tableName());

         $query = (new Query)
            ->select([
                'rank',
            ])
            ->from(['ranks' => $subQuery])
            ->where(['id' => $this->id]);

        $rank = $query->scalar();

        return $rank ?: 0;
    }

    public function updateRating()
    {
        $totalRating = Rating::find()
            ->where(['user_id' => $this->id])
            ->sum('amount');

        if ($this->rating != $totalRating) {
            $this->rating = $totalRating;
            $this->save(false);
        }

        return true;
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
        $commit = false;
        $rating = null;

        //If a rating can exist only once
        if (!$existMultiple) {
            $rating = Rating::findOne([
                'user_id' => $this->id,
                'type' => $ratingType,
            ]);

            if ($rating !== null) {
                $commit = true;
            }
        }
        if ($rating == null) {
            $rating = new Rating([
                'user_id' => $this->id,
                'amount' => $ratingAmount,
                'type' => $ratingType,
            ]);

            if ($rating->save()) {
                $this->updateRating();
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
     * @return UserQuery
     */
    public function getReferrer()
    {
        return $this->hasOne(User::class, ['id' => 'referrer_id']);
    }

    public function getContact(): ContactQuery
    {
        return $this->hasOne(Contact::class, ['link_user_id' => 'id'])
            ->onCondition(['user_id' => Yii::$app->user->id]);
        //TODO [ref] it is very bad way. NEVER set default conditions for whole app.
        //  there are exist very-very rare cases, when it is really necessary to do.
        //  Why: this condition useful, only when user with role 'User' is logged on.
        //       but what if user with role 'Admin' is logged on?
        //       Btw in console app `Yii::$app->user` is not exist at all!
    }

    public function getContactsFromMe(): ContactQuery
    {
        return $this->hasMany(Contact::class, ['user_id' => 'id']);
    }

    public function getContactsToMe(): ContactQuery
    {
        return $this->hasMany(Contact::class, ['link_user_id' => 'id']);
    }

    public function getDisplayName()
    {
        return $this->contact->getContactName();
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getGender()
    {
        return $this->hasOne(Gender::class, [ 'id' => 'gender_id' ]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSexuality()
    {
        return $this->hasOne(Sexuality::class, [ 'id' => 'sexuality_id' ]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCurrency()
    {
        return $this->hasOne(Currency::class, [ 'id' => 'currency_id' ]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLanguages()
    {
        return $this->hasMany(UserLanguage::class, [ 'user_id' => 'id' ]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCitizenships()
    {
        return $this->hasMany(UserCitizenship::class, ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCompanies()
    {
        return $this->hasMany(Company::class, ['id' => 'company_id'])
            ->viaTable('company_user', ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVacancies()
    {
        return $this->hasMany(Vacancy::class, ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getResumes()
    {
        return $this->hasMany(Resume::class, ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAdSearches()
    {
        return $this->hasMany(AdSearch::class, ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAdOffers()
    {
        return $this->hasMany(AdOffer::class, ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     *
     * Get user's contact groups
     */
    public function getContactGroups()
    {
        return $this->hasMany(ContactGroup::className(), ['user_id' => 'id']);
    }

    public function getBotUser(): ActiveQuery
    {
        return $this->hasOne(\app\modules\bot\models\User::class, ['user_id' => 'id']);
    }

    /**
     * @return boolean
     *
     * Checks if there is empty group
     */
    public function hasEmptyContactGroup()
    {
        $groups = $this->getContactGroups()->all();
        $hasEmptyGroup = false;

        if (!empty($groups)) {
            foreach ($groups as $group) {
                $groupId = $group->id;
                $countGroupContacts = ContactHasGroup::find()
                    ->where([
                        'contact_group_id' => $groupId,
                    ])
                    ->count();

                if ($countGroupContacts == 0) {
                    $hasEmptyGroup = true;
                    break;
                }
            }
        }
        return $hasEmptyGroup;
    }

    /**
     * {@inheritdoc}
     * @return UserQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new UserQuery(get_called_class());
    }

    /**
     * @return integer The total rating in user table
     */
    public static function getTotalRating()
    {
        $result = static::find()->sum('rating');

        return $result ?: 0;
    }

    /**
     * @return integer The total rank in user table
     */
    public static function getTotalRank()
    {
        $result = static::find()
            ->where([
                '>', 'rating', 0,
                ])
            ->count();

        return $result ?: 0;
    }

    /**
     * @throws \yii\db\Exception
     */
    public function updateLastActivity()
    {
        Yii::$app->db->createCommand()
            ->update('{{%user}}', [
                'last_activity_at' => time(),
            ],
            [
                'id' => $this->id,
            ])
            ->execute();
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getJobMatches()
    {
        return $this->hasMany(JobMatch::class, ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getJobResumeMatches()
    {
        return $this->hasMany(JobMatch::class, ['resume_id' => 'id'])
            ->viaTable('{{%resume}}', ['user_id' => 'id'])
            ->andWhere(['or', ['type' => 0], ['type' => 2]]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getJobVacancyMatches()
    {
        return $this->hasMany(JobMatch::class, ['vacancy_id' => 'id'])
            ->viaTable('{{%vacancy}}', ['user_id' => 'id'])
            ->andWhere(['or', ['type' => 0], ['type' => 2]]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCurrencyExchangeOrders()
    {
        return $this->hasMany(CurrencyExchangeOrder::class, ['user_id' => 'id']);
    }
}
