<?php

namespace app\models;

use app\components\Converter;
use app\models\queries\ContactQuery;
use app\models\queries\DebtRedistributionQuery;
use app\models\queries\UserQuery;
use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\Query;
use yii\web\IdentityInterface;

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
 * @property bool $basic_income_on
 * @property integer $basic_income_activated_at
 * @property integer $basic_income_processed_at
 *
 * @property Company[] $companies
 * @property app\modules\bot\models\User $botUser
 * @property Contact $contact
 * @property Contact[] $contacts
 * @property Contact[] $counterContacts
 * @property UserLanguage[] $languages
 */
class User extends ActiveRecord implements IdentityInterface
{
    public const RESET_PASSWORD_REQUEST_LIFETIME = 24 * 60 * 60; // seconds
    public const AUTH_LINK_LIFETIME = 5 * 60; // seconds

    public const STATUS_DELETED = 0;
    public const STATUS_PENDING = 5;
    public const STATUS_ACTIVE = 10;

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
            [['created_at', 'updated_at', 'last_activity_at', 'basic_income_activated_at', 'basic_income_processed_at'], 'integer'],
            [['created_at', 'updated_at', 'last_activity_at'], 'default', 'value' => time()],
            ['basic_income_on', 'boolean'],
            ['basic_income_on', 'default', 'value' => 1],
            ['birthday', 'date'],
            [['timezone'], 'default', 'value' => 0],
            [['timezone'], 'integer', 'min' => -720, 'max' => 840],
            ['status', 'default', 'value' => self::STATUS_ACTIVE],
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
            'rating' => Yii::t('app', 'Social Rating'),
            'username' => Yii::t('app', 'Username'),
            'name' => Yii::t('app', 'Name'),
            'currency_id' => Yii::t('app', 'Currency'),
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
    // TODO remove old
    public function getEmail()
    {
        return $this->hasOne(UserEmail::class, ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserEmail()
    {
        return $this->hasOne(UserEmail::class, ['user_id' => 'id']);
    }

    public function getNewUserEmail(): UserEmail
    {
        $model = new UserEmail();
        $model->user_id = Yii::$app->user->id;

        return $model;
    }

    public function isEmailConfirmed()
    {
        return $this->email && $this->email->isConfirmed();
    }

    public function isStellarConfirmed()
    {
        return $this->stellar && $this->stellar->isConfirmed();
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
        $userEmail = $this->userEmail;

        if ($this->isEmailConfirmed()) {
            return false;
        }

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
     * Confirm email
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

    public function authByHash(int $time, string $hash)
    {
        if ($hash == md5($this->id . $this->auth_key . $time)) {
            return true;
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
        $daysActiveRating = Yii::$app->settings->days_count_to_calculate_active_rating;

        $activeRating = Rating::find()
            ->where(['>', 'created_at', time() - 24 * 60 * 60 * $daysActiveRating])
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
           ->from(self::tableName())
           ->andWhere([
               'status' => self::STATUS_ACTIVE,
           ]);

        $query = (new Query())
            ->select([
                'rank',
            ])
            ->from([
                'ranks' => $subQuery,
            ])
            ->andWhere([
                'id' => $this->id,
            ]);

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

    public function updateBasicIncomeActivatedAt()
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

    public function resetBasicIncomeProcessedAt()
    {
        if ($this->basic_income_processed_at) {
            $this->basic_income_processed_at = null;
            $this->save(false);
        }
    }

    public function confirmBasicIncomeActivatedAt()
    {
        if (!$this->basic_income_activated_at) {
            $this->basic_income_activated_at = time();
            $this->save(false);
        }
    }

    public function resetBasicIncomeActivatedAt()
    {
        if ($this->basic_income_activated_at) {
            $this->basic_income_activated_at = null;
            $this->save(false);
        }
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
        // TODO level
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
    }

    public function getNewContact(): Contact
    {
        if (Yii::$app->user->id == $this->id) {
            return false;
        }

        $model = new Contact();
        $model->user_id = Yii::$app->user->id;
        $model->userIdOrName = $this->id;
        $model->link_user_id = $this->id;

        return $model;
    }

    public function getContacts(): ContactQuery
    {
        return $this->hasMany(Contact::class, ['user_id' => 'id']);
    }

    public function getCounterContacts(): ContactQuery
    {
        return $this->hasMany(Contact::class, ['link_user_id' => 'id']);
    }

    public function getDebtRedistributions(): DebtRedistributionQuery
    {
        return $this->hasMany(DebtRedistribution::class, ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSettingValueVotes()
    {
        return $this->hasMany(SettingValueVote::class, ['user_id' => 'id']);
    }

    public function getRealConfirmations()
    {
        return $this->getCounterContacts()
            ->where([
                'is_real' => 1,
            ])
            ->count();
    }

    public function getDisplayName()
    {
        if ($this->contact) {
            return $this->contact->getContactName();
        } else {
            return !empty($this->username) ? '@' . $this->username : '#' . $this->id;
        }
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

    public function getVacancyMatchesCount()
    {
        return $this->hasMany(JobVacancyMatch::class, ['vacancy_id' => 'id'])
            ->viaTable(Vacancy::tableName(), ['user_id' => 'id'])
            ->andWhere([
                'not in',
                JobVacancyMatch::tableName() . '.resume_id',
                JobResumeResponse::find()
                    ->select('resume_id')
                    ->andWhere([
                        'user_id' => $this->id,
                    ])
                    ->andWhere([
                        'is not', 'viewed_at', null,
                    ]),
            ])
            ->count();
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getResumes()
    {
        return $this->hasMany(Resume::class, ['user_id' => 'id']);
    }

    public function getResumeMatchesCount()
    {
        return $this->hasMany(JobResumeMatch::class, ['resume_id' => 'id'])
            ->viaTable(Resume::tableName(), ['user_id' => 'id'])
            ->andWhere([
                'not in',
                JobResumeMatch::tableName() . '.vacancy_id',
                JobVacancyResponse::find()
                    ->select('vacancy_id')
                    ->andWhere([
                        'user_id' => $this->id,
                    ])
                    ->andWhere([
                        'is not', 'viewed_at', null,
                    ]),
            ])
            ->count();
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAdSearches()
    {
        return $this->hasMany(AdSearch::class, ['user_id' => 'id']);
    }

    public function getAdSearchMatchesCount()
    {
        return $this->hasMany(AdSearchMatch::class, ['ad_search_id' => 'id'])
            ->viaTable(AdSearch::tableName(), ['user_id' => 'id'])
            ->andWhere([
                'not in',
                AdSearchMatch::tableName() . '.ad_offer_id',
                AdOfferResponse::find()
                    ->select('ad_offer_id')
                    ->andWhere([
                        'user_id' => $this->id,
                    ])
                    ->andWhere([
                        'is not', 'viewed_at', null,
                    ]),
            ])
            ->count();
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAdOffers()
    {
        return $this->hasMany(AdOffer::class, ['user_id' => 'id']);
    }

    public function getAdOfferMatchesCount()
    {
        return $this->hasMany(AdOfferMatch::class, ['ad_offer_id' => 'id'])
            ->viaTable(AdOffer::tableName(), ['user_id' => 'id'])
            ->andWhere([
                'not in',
                AdOfferMatch::tableName() . '.ad_search_id',
                AdSearchResponse::find()
                    ->select('ad_search_id')
                    ->andWhere([
                        'user_id' => $this->id,
                    ])
                    ->andWhere([
                        'is not', 'viewed_at', null,
                    ]),
            ])
            ->count();
    }

    /**
     * @return \yii\db\ActiveQuery
     *
     * Get user's contact groups
     */
    public function getContactGroups()
    {
        return $this->hasMany(ContactGroup::className(), ['user_id' => 'id'])
            ->orderBy('name');
    }

    public function getBotUser(): ActiveQuery
    {
        return $this->hasOne(\app\modules\bot\models\User::class, ['user_id' => 'id']);
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
        $this->last_activity_at = time();
        $this->save(false);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCurrencyExchangeOrders()
    {
        return $this->hasMany(CurrencyExchangeOrder::class, ['user_id' => 'id']);
    }

    public function getCurrencyExchangeOrderMatchesCount()
    {
        return $this->hasMany(CurrencyExchangeOrderMatch::class, ['order_id' => 'id'])
            ->viaTable(CurrencyExchangeOrder::tableName(), ['user_id' => 'id'])
            ->andWhere([
                'not in',
                CurrencyExchangeOrderMatch::tableName() . '.match_order_id',
                CurrencyExchangeOrderResponse::find()
                    ->select('order_id')
                    ->andWhere([
                        'user_id' => $this->id,
                    ])
                    ->andWhere([
                        'is not', 'viewed_at', null,
                    ]),
            ])
            ->count();
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    // TODO remove old
    public function getStellar()
    {
        return $this->hasOne(UserStellar::class, ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserStellar()
    {
        return $this->hasOne(UserStellar::class, ['user_id' => 'id']);
    }

    public function getPendingDebts()
    {
        $query = Debt::find()
            ->andWhere([
                'or',
                ['from_user_id' => $this->id],
                ['to_user_id' => $this->id],
            ])
            ->andWhere([
                'status' => Debt::STATUS_PENDING,
            ])
            ->orderBy([
                'created_at' => SORT_DESC,
            ]);

        $query->multiple = true;

        return $query;
    }

    public function getDepositDebts()
    {
        return $this->hasMany(Debt::class, ['to_user_id' => 'id']);
    }

    public function getCreditDebts()
    {
        return $this->hasMany(Debt::class, ['from_user_id' => 'id']);
    }

    public function getDepositDebtBalance(int $currencyId, int $counterUserId = null)
    {
        $query = DebtBalance::find()
            ->andWhere([
                'to_user_id' => $this->id,
                'currency_id' => $currencyId,
            ])
            ->andWhere(['>', 'amount', 0]);

        if ($counterUserId) {
            $query->andWhere([
                'from_user_id' => $counterUserId,
            ]);

            $amount = $query->select('amount')->scalar();
        } else {
            $amount = $query->sum('amount');
        }

        return $amount;
    }

    public function getCreditDebtBalance(int $currencyId, int $counterUserId = null)
    {
        $query = DebtBalance::find()
            ->andWhere([
                'from_user_id' => $this->id,
                'currency_id' => $currencyId,
            ])
            ->andWhere(['>', 'amount', 0]);

        if ($counterUserId) {
            $query->andWhere([
                'to_user_id' => $counterUserId,
            ]);

            $amount = $query->select('amount')->scalar();
        } else {
            $amount = $query->sum('amount');
        }

        return $amount;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserLocation()
    {
        return $this->hasOne(UserLocation::class, ['user_id' => 'id']);
    }

    public function getNewUserLocation(): UserLocation
    {
        $model = new UserLocation();
        $model->user_id = Yii::$app->user->id;

        return $model;
    }

    public function getLocation(): ?string
    {
        return $this->userLocation ? $this->userLocation->location : null;
    }

    public function isBasicIncomeOn()
    {
        return (bool)$this->basic_income_on;
    }

    public function getBasicIncomePositiveVotesCount()
    {
        return $this->getCounterContacts()
                ->where([
                    'is_basic_income_candidate' => 1,
                ])
                ->count();
    }

    /**
     * @param int|null $userId
     *
     * @return bool
     */
    public function getBasicIncomeVoteByUserId($userId = null)
    {
        if (!$userId) {
            $userId = Yii::$app->user->id;
        }

        $contact = Contact::find()
            ->where([
                'user_id' => $userId,
                'link_user_id' => $this->id,
            ])
            ->one();

        return $contact ? $contact->is_basic_income_candidate : 0;
    }

    public function isBasicIncomeParticipant()
    {
        return (bool)$this->basic_income_on && (bool)$this->basic_income_activated_at && (bool)($this->stellar && $this->stellar->confirmed_at);
    }

    public function isBasicIncomeActivated()
    {
        return (bool)$this->basic_income_activated_at;
    }

    public function getAuthLinkTimeLimit()
    {
        return (int)(self::AUTH_LINK_LIFETIME / 60);
    }

    public function getAuthLink()
    {
        $time = time();
        $link = Yii::$app->urlManager->createAbsoluteUrl([
            'site/login-by-auth-link',
            'id' => $this->id,
            'time' => $time,
            'hash' => md5($this->id . $this->auth_key . $time),
        ]);

        return $link;
    }
}
