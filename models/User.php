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
 * @property bool $gender
 *
 * @property Company[] $companies
 * @property null|\app\modules\bot\models\User $botUser
 * @property Contact $contact
 * @property Contact[] $contactsFromMe
 * @property Contact[] $contactsToMe
 * @property UserLanguage[] $languages
 */
class User extends ActiveRecord implements IdentityInterface
{
    public const RESET_PASSWORD_REQUEST_LIFETIME = 24 * 60 * 60; // seconds

    public const STATUS_DELETED = 0;
    public const STATUS_PENDING = 5;
    public const STATUS_ACTIVE = 10;

    public const DATE_FORMAT = 'Y-m-d';

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
            [['gender_id', 'sexuality_id', 'currency_id', 'rating'], 'integer'],
            [['created_at', 'updated_at', 'last_activity_at'], 'integer'],
            [['created_at', 'updated_at', 'last_activity_at'], 'default', 'value' => time()],
            ['birthday', 'date'],
            [['timezone'], 'default', 'value' => 0],
            [['timezone'], 'integer', 'min' => -720, 'max' => 840],
            [
                'status',
                'default',
                'value' => self::STATUS_ACTIVE
            ],
            [
                'status',
                'in',
                'range' => [
                    self::STATUS_ACTIVE,
                    self::STATUS_DELETED,
                ],
            ],
            ['username', 'string'],
            ['username', 'trim'],
            [['username'], 'string', 'length' => [2, 255]],
            [
                'username',
                'match',
                'pattern' => '/(?:^(?:[A-Za-z0-9][_]{0,1})*[A-Za-z0-9]$)/i',
                'message' => 'Username can contain only letters, numbers and _ symbols.',
            ],
            ['username', 'validateUsernameUnique'],
            ['username', 'default', 'value' => null],
            ['name', 'string'],
            ['name', 'trim'],
            [['name'], 'string', 'length' => [1, 255]],
            ['name', 'validateNameString'],
            ['rating', 'integer'],
            ['rating', 'default', 'value' => Rating::DEFAULT],
        ];
    }

    public function validateUsernameUnique()
    {
        $oldValue = $this->getOldAttribute('username');

        if (is_numeric($this->username)) {
            $this->addError('username', 'Username can\'t be number.');
        }

        if (strcasecmp($oldValue, $this->username) === 0) {
            return;
        }

        $existUsername = User::find()
            ->where([
                'username' => $this->username,
            ])
            ->exists();

        if ($existUsername) {
            $this->addError('username', 'Username must be unique.');
        }
    }

    public function validateNameString()
    {
        $oldValue = $this->getOldAttribute('name');

        if (strcasecmp($oldValue, $this->name) === 0) {
            return;
        }

        if (is_numeric($this->name)) {
            $this->addError('name', 'Name can\'t be number.');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'rating' => 'Social Rating',
            'username' => 'Username (optional)',
            'name' => 'Name (optional)',
        ];
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
        $this->status = self::STATUS_ACTIVE;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEmail()
    {
        return $this->hasOne(UserEmail::class, ['user_id' => 'id']);
    }

    public function isEmailConfirmed()
    {
        return $this->email && $this->email->isConfirmed();
    }

    // Compatible with IdentityInterface
    public static function findIdentity($id)
    {
        return static::findById($id);
    }

    /**
     * Finds user by ID
     *
     * @param string $id
     *
     * @return static|null
     */
    public static function findById($id): ?User
    {
        return static::findOne([
            'id' => $id,
            'status' => self::STATUS_ACTIVE,
        ]);
    }

    /**
     * Finds user by username
     *
     * @param string $username
     *
     * @return static|null
     */
    public static function findByUsername($username): ?User
    {
        return static::findOne([
            'username' => $username,
            'status' => self::STATUS_ACTIVE,
        ]);
    }

    /**
     * Finds user by email
     *
     * @param string $email
     *
     * @return static|null
     */
    public static function findByEmail($email): ?User
    {
        return static::find()
            ->where([self::tableName() . '.status' => self::STATUS_ACTIVE])
            ->joinWith('email')
            ->andWhere([UserEmail::tableName() . '.email' => $email])
            ->andWhere(['not', [UserEmail::tableName() . '.confirmed_at' => null]])
            ->one();
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
     * Sends an email with a confirmation link.
     *
     * @return bool whether the email was send
     */
    public function sendConfirmationEmail()
    {
        $time = time();
        $userEmail = $this->email;
        $link = Yii::$app->urlManager->createAbsoluteUrl([
            'user/confirm-email',
            'id' => $this->id,
            'time' => $time,
            'hash' => md5($userEmail->email . $this->auth_key . $time),
        ]);

        return Yii::$app->mailer
            ->compose(
                [
                    'html' => 'change-email-html',
                    'text' => 'change-email-text',
                ],
                [
                    'user' => $this,
                    'link' => $link,
                ]
            )
            ->setFrom([Yii::$app->params['adminEmail'] => Yii::$app->name . ' Robot'])
            ->setTo($userEmail->email)
            ->setSubject('Confirm email for ' . Yii::$app->name)
            ->send();
    }

    /**
     * Confirm email.
     *
     * @param int $id user id
     * @param int $time
     * @param string $hash
     *
     * @return boolean
     */
    public function confirmEmail(int $id, int $time, string $hash)
    {
        if ($this->isEmailConfirmed()) {
            return true;
        }

        if ($userEmail = $this->email) {
            if ($hash == md5($userEmail->email . $this->auth_key . $time)) {
                $userEmail->confirm();

                return true;
            }
        }

        return false;
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
     *
     * @return mixed The number in percentage
     */
    public function getRatingPercent($format = true)
    {
        $totalRating = self::getTotalRating();

        return Converter::percentage($this->rating, $totalRating, $format);
    }

    /**
     * @return integer The active rating of the user
     */
    public function getActiveRating()
    {
        $daysActiveRating = intval(Yii::$app->settings->days_count_to_calculate_active_rating);

        $activeRating = Rating::find()
            ->where(['>', 'created_at', time() - 3600 * 24 * $daysActiveRating])
            ->andWhere(['user_id' => $this->id])
            ->sum('amount');

        return $activeRating ?: 0;
    }

    public function getRank()
    {
        $subQuery = (new Query())
           ->select([
               'ROW_NUMBER() OVER(ORDER BY rating DESC, created_at ASC) `rank`',
               'id',
           ])
           ->from(self::tableName());

        $query = (new Query())
            ->select([
                'rank',
            ])
            ->from(['ranks' => $subQuery])
            ->where(['id' => $this->id]);

        $rank = $query->scalar();

        return $rank ?: 0;
    }
    // TODO сохранять ли дефолт
    public function updateRating()
    {
        $totalRating = Rating::find()
            ->where([
                'user_id' => $this->id,
            ])
            ->sum('amount');

        if ($this->rating != ($totalRating + Rating::DEFAULT)) {
            $this->rating = $totalRating + Rating::DEFAULT;
            $this->save(false);
        }

        return true;
    }

    /**
     * Add user rating
     *
     * @param int $type integer value for rating type constants defined in Rating model
     * @param int $amount rating amount to be added
     *
     * @return bool true|false
     */
    public function addRating($type, $amount = 1)
    {
        $rating = new Rating([
            'user_id' => $this->id,
            'amount' => $amount,
            'type' => $type,
        ]);

        if ($rating->save()) {
            $this->updateRating();
        }

        return true;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getReferrals(int $level = 1)
    {
        return User::find()
            ->where([
                'referrer_id' => $this->id,
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
        return $this->hasOne(Gender::class, ['id' => 'gender_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSexuality()
    {
        return $this->hasOne(Sexuality::class, ['id' => 'sexuality_id']);
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
        return $this->hasMany(UserLanguage::class, ['user_id' => 'id']);
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
            ->update(
                '{{%user}}',
                [
                'last_activity_at' => time(),
            ],
                [
                'id' => $this->id,
            ]
            )
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

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStellar()
    {
        return $this->hasOne(UserStellar::class, ['user_id' => 'id']);
    }
}
