<?php

namespace app\models;

use app\models\queries\VacancyQuery;
use app\models\User as GlobalUser;
use app\modules\bot\models\JobKeyword;
use Yii;
use app\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * Class Vacancy
 *
 * @package app\models
 */
class Vacancy extends ActiveRecord
{
    public const STATUS_OFF = 0;
    public const STATUS_ON = 1;

    public const LIVE_DAYS = 14;

    const REMOTE_OFF = 0;
    const REMOTE_ON = 1;

    public static function tableName()
    {
        return '{{%vacancy}}';
    }

    public function rules()
    {
        return [
            [
                [
                    'user_id',
                    'company_id',
                    'currency_id',
                    'status',
                    'gender_id',
                    'created_at',
                    'renewed_at',
                    'processed_at',
                ],
                'integer',
            ],
            [
                [
                    'location_lat',
                    'location_lon',
                    'max_hourly_rate',
                ],
                'double',
            ],
            [
                [
                    'name',
                ],
                'string',
                'max' => 256,
            ],
            [
                [
                    'requirements',
                    'conditions',
                    'responsibilities',
                ],
                'string',
            ],
            [
                [
                    'currency_id',
                    'name',
                    'requirements',
                    'conditions',
                    'responsibilities',
                ],
                'required',
            ],
        ];
    }

    /**
     * @return VacancyQuery|\yii\db\ActiveQuery
     */
    public static function find()
    {
        return new VacancyQuery(get_called_class());
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'max_hourly_rate' => Yii::t('app', 'Max. hourly rate'),
            'remote_on' => Yii::t('bot', 'Remote work'),
        ];
    }

    /** @inheritDoc */
    public function behaviors()
    {
        return [
            'TimestampBehavior' => [
                'class' => TimestampBehavior::class,
            ],
        ];
    }

    public function getCompany()
    {
        return $this->hasOne(Company::class, ['id' => 'company_id']);
    }

    public function getCurrency()
    {
        return $this->hasOne(Currency::class, ['id' => 'currency_id']);
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->status == self::STATUS_ON && (time() - $this->renewed_at) <= self::LIVE_DAYS * 24 * 60 * 60;
    }

    /**
     * @return \yii\db\ActiveQuery
     * @throws \yii\base\InvalidConfigException
     */
    public function getMatches()
    {
        if (!YII_DEBUG) {
            return $this->hasMany(Resume::className(), ['id' => 'resume_id'])
                ->viaTable('{{%job_match}}', ['vacancy_id' => 'id'], function ($query) {
                    $query->andWhere(['or', ['type' => 0], ['type' => 2]]);
                });
        }

        return $this->getMatchedResumes();
    }

    /**
     * @return queries\ResumeQuery
     */
    public function getMatchedResumes()
    {
        $query = Resume::find()->active();
        if ($this->max_hourly_rate) {
            $query->andWhere(['<=', 'min_hourly_rate', $this->max_hourly_rate])
                ->andWhere(['IS NOT', 'min_hourly_rate', null]);
        }
        if (!YII_DEBUG) {
            $query->andWhere(['!=', Resume::tableName() . '.user_id', $this->user_id]);
        }

        return $query->groupBy(Resume::tableName() . '.id');
    }

    /**
     * @return \yii\db\ActiveQuery
     * @throws \yii\base\InvalidConfigException
     */
    public function getAllMatches()
    {
        return $this->hasMany(Resume::className(), ['id' => 'resume_id'])
            ->viaTable('{{%job_match}}', ['vacancy_id' => 'id']);
    }

    public function updateMatches()
    {
        $this->unlinkAll('allMatches', true);
        $resumes = $this->getMatchedResumes()->all();
        foreach ($resumes as $resume) {
            $this->link('matches', $resume, ['type' => 2]);
        }
    }

    public function markToUpdateMatches()
    {
        if ($this->processed_at !== null) {
            $this->unlinkAll('matches', true);

            $this->setAttributes([
                'processed_at' => null,
            ]);
            $this->save();
        }
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCurrencyRelation()
    {
        return $this->hasOne(Currency::class, ['id' => 'currency_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCompanyRelation()
    {
        return $this->hasOne(Company::class, ['id' => 'company_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     * @throws \yii\base\InvalidConfigException
     */
    public function getLanguagesRelation()
    {
        return $this->hasMany(Language::className(), ['id' => 'language_id'])
            ->viaTable('{{%vacancy_language}}', ['vacancy_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVacancyLanguagesRelation()
    {
        return $this->hasMany(VacancyLanguage::class, ['vacancy_id' => 'id']);
    }

    /**
     * @return string
     */
    public function getCurrencyCode()
    {
        $currency = $this->currencyRelation;
        if ($currency) {
            $currencyCode = $currency->code;
        } else {
            $currencyCode = '';
        }

        return $currencyCode;
    }

    /**
     * @return \yii\db\ActiveQuery
     * @throws \yii\base\InvalidConfigException
     */
    public function getGlobalUser()
    {
        return $this->hasOne(GlobalUser::className(), ['id' => 'user_id'])
            ->viaTable('{{%bot_user}}', ['id' => 'user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     * @throws \yii\base\InvalidConfigException
     */
    public function getKeywordsRelation()
    {
        return $this->hasMany(JobKeyword::className(), ['id' => 'job_keyword_id'])
            ->viaTable('{{%job_vacancy_keyword}}', ['vacancy_id' => 'id']);
    }
}
