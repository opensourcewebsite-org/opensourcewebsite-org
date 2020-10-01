<?php

namespace app\models;

use Yii;
use app\models\queries\ResumeQuery;
use app\models\User as GlobalUser;
use app\modules\bot\validators\RadiusValidator;
use app\modules\bot\validators\LocationLatValidator;
use app\modules\bot\validators\LocationLonValidator;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\conditions\AndCondition;

/**
 * Class Resume
 *
 * @package app\models
 */
class Resume extends ActiveRecord
{
    public const STATUS_OFF = 0;
    public const STATUS_ON = 1;

    public const LIVE_DAYS = 30;

    public const REMOTE_OFF = 0;
    public const REMOTE_ON = 1;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%resume}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [
                [
                    'user_id',
                    'currency_id',
                    'name',
                ],
                'required',
            ],
            [
                [
                    'user_id',
                    'currency_id',
                    'status',
                    'created_at',
                    'processed_at',
                ],
                'integer',
            ],
            [
                'search_radius',
                RadiusValidator::class,
            ],
            [
                'location_lat',
                LocationLatValidator::class,
            ],
            [
                'location_lon',
                LocationLonValidator::class,
            ],
            [
                [
                    'location_lat',
                    'location_lon',
                ],
                'double',
            ],
            [
                'min_hourly_rate',
                'double',
                'min' => 0,
                'max' => 99999999.99,
            ],
            [
                [
                    'name',
                ],
                'string',
                'max' => 255,
            ],
            [
                [
                    'experiences',
                    'expectations',
                    'skills',
                ],
                'string',
                'max' => 10000,
            ],
        ];
    }

    /**
     * @return ResumeQuery
     */
    public static function find()
    {
        return new ResumeQuery(get_called_class());
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'min_hourly_rate' => Yii::t('bot', 'Min. hourly rate'),
            'remote_on' => Yii::t('bot', 'Remote work'),
            'search_radius' => Yii::t('bot', 'Search radius'),
        ];
    }

    /** @inheritDoc */
    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => TimestampBehavior::className(),
                'updatedAtAttribute' => false,
            ],
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCurrency()
    {
        return $this->hasOne(Currency::class, ['id' => 'currency_id']);
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->status == self::STATUS_ON;
    }

    /**
     * @return \yii\db\ActiveQuery
     * @throws \yii\base\InvalidConfigException
     */
    public function getMatches()
    {
        return $this->hasMany(Vacancy::className(), ['id' => 'vacancy_id'])
            ->viaTable('{{%job_resume_match}}', ['resume_id' => 'id']);
    }

    /**
     * @return queries\VacancyQuery
     */
    public function getMatchedVacancies()
    {
        $query = Vacancy::find()
            ->live()
            ->matchLanguages($this)
            ->matchRadius($this)
            ->andWhere([
                '!=', Vacancy::tableName() . '.user_id', $this->user_id,
            ]);

        return $query;
    }

    /**
     * @return \yii\db\ActiveQuery
     * @throws \yii\base\InvalidConfigException
     */
    public function getCounterMatches()
    {
        return $this->hasMany(Vacancy::className(), ['id' => 'vacancy_id'])
            ->viaTable('{{%job_vacancy_match}}', ['resume_id' => 'id']);
    }

    public function updateMatches()
    {
        $this->unlinkAll('matches', true);
        $this->unlinkAll('counterMatches', true);

        $vacanciesQuery = $this->getMatchedVacancies();

        $vacanciesQueryNoRateQuery = clone $vacanciesQuery;
        $vacanciesQueryRateQuery = clone $vacanciesQuery;

        if ($this->min_hourly_rate) {
            $vacanciesQueryRateQuery->andWhere(new AndCondition([
                ['IS NOT', Vacancy::tableName() . '.max_hourly_rate', null],
                ['>=', Vacancy::tableName() . '.max_hourly_rate', $this->min_hourly_rate],
                [Vacancy::tableName() . '.currency_id' => $this->currency_id],
            ]));
            $vacanciesQueryNoRateQuery->andWhere(
                new AndCondition([
                    ['<', Vacancy::tableName() . '.max_hourly_rate', $this->min_hourly_rate],
                    ['<>', Vacancy::tableName() . '.currency_id', $this->currency_id],
                ])
            );

            foreach ($vacanciesQueryRateQuery->all() as $vacancy) {
                $this->link('matches', $vacancy);
                $this->link('counterMatches', $vacancy);
            }

            foreach ($vacanciesQueryNoRateQuery->all() as $vacancy) {
                $this->link('counterMatches', $vacancy);
            }
        } else {
            foreach ($vacanciesQueryRateQuery->all() as $vacancy) {
                $this->link('matches', $vacancy);
            }
        }
    }

    /**
     * @return \yii\db\ActiveQuery
     * @throws \yii\base\InvalidConfigException
     */
    public function getLanguagesRelation()
    {
        return $this->hasMany(Language::className(), ['id' => 'language_id'])
            ->viaTable('{{%user_language}}', ['user_id' => 'user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     * @throws \yii\base\InvalidConfigException
     */
    public function getUserLanguagesRelation()
    {
        return $this->hasMany(UserLanguage::class, ['user_id' => 'user_id']);
    }

    public function clearMatches()
    {
        if ($this->processed_at !== null) {
            $this->unlinkAll('matches', true);
            $this->unlinkAll('counterMatches', true);

            $this->setAttributes([
                'processed_at' => null,
            ]);

            $this->save();
        }
    }

    /**
     * @return \yii\db\ActiveQuery
     * @throws \yii\base\InvalidConfigException
     */
    public function getGlobalUser()
    {
        return $this->hasOne(GlobalUser::className(), ['id' => 'user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     * @throws \yii\base\InvalidConfigException
     */
    public function getKeywordsRelation()
    {
        return $this->hasMany(JobKeyword::className(), ['id' => 'job_keyword_id'])
            ->viaTable('{{%job_resume_keyword}}', ['resume_id' => 'id']);
    }

    /**
     * {@inheritdoc}
     */
    public function afterSave($insert, $changedAttributes)
    {
        if (isset($changedAttributes['status'])) {
            if ($this->status == self::STATUS_OFF) {
                $this->clearMatches();
            }
        }

        parent::afterSave($insert, $changedAttributes);
    }

    /**
     * @return array
     */
    public function notPossibleToChangeStatus()
    {
        $notFilledFields = [];

        if (!$this->getLanguagesRelation()->count()) {
            $notFilledFields[] = Yii::t('bot', $this->getAttributeLabel('languages')) . ' (' . Yii::t('bot', 'in your profile') . ')';
        }
        if ($this->remote_on == self::REMOTE_OFF) {
            if (!($this->location_lon && $this->location_lat)) {
                $notFilledFields[] = Yii::t('bot', $this->getAttributeLabel('location'));
            }
            if (!$this->search_radius) {
                $notFilledFields[] = Yii::t('bot', $this->getAttributeLabel('search_radius'));
            }
        }

        return $notFilledFields;
    }
}
