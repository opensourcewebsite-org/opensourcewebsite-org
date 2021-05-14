<?php

namespace app\models;

use app\modules\bot\components\helpers\LocationParser;
use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use app\models\User as GlobalUser;
use app\models\queries\ResumeQuery;
use yii\db\conditions\AndCondition;
use app\models\queries\VacancyQuery;
use yii\behaviors\TimestampBehavior;
use app\modules\bot\validators\RadiusValidator;
use app\modules\bot\validators\LocationLatValidator;
use app\modules\bot\validators\LocationLonValidator;

/**
 * Class Resume
 *
 * @package app\models
 *
 * @property int $id
 * @property int $user_id
 * @property int $status
 * @property int $remote_on
 * @property string $name
 * @property string $experiences
 * @property double $min_hourly_rate
 * @property int $search_radius
 * @property int $currency_id
 * @property string $expectations
 * @property string $skills
 * @property string $location_lat
 * @property string $location_lon
 * @property int $created_at
 * @property int $processed_at
 *
 * @property Currency $currency
 * @property User $user
 * @property Vacancy[] $matches
 * @property UserLanguage[] $userLanguagesRelation
 */
class Resume extends ActiveRecord
{
    public const STATUS_OFF = 0;
    public const STATUS_ON = 1;

    public const LIVE_DAYS = 30;

    public const REMOTE_OFF = 0;
    public const REMOTE_ON = 1;

    public static function tableName(): string
    {
        return '{{%resume}}';
    }

    public function rules(): array
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
            ['location', 'string'],
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

    public static function find(): ResumeQuery
    {
        return new ResumeQuery(get_called_class());
    }

    public function attributeLabels(): array
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'user_id' => Yii::t('app', 'User'),
            'remote_on' => Yii::t('bot', 'Remote work'),
            'name' => Yii::t('app', 'Name'),
            'experiences' => Yii::t('app','Experiences'),
            'min_hourly_rate' => Yii::t('bot', 'Min. hourly rate'),
            'search_radius' => Yii::t('bot', 'Search radius'),
            'currency_id' => Yii::t('app', 'Currency'),
            'expectations' => Yii::t('app', 'Expectations'),
            'skills' => Yii::t('app',' skills'),
            'location_lat' => Yii::t('app', 'location_lat'),
            'location_lon' => Yii::t('app', 'location_lon'),
            'location' => Yii::t('app', 'Location'),
            'created_at' => Yii::t('app', 'created_at'),
            'processed_at' => Yii::t('app', 'processed_at'),
        ];
    }

    public function behaviors(): array
    {
        return [
            'timestamp' => [
                'class' => TimestampBehavior::className(),
                'updatedAtAttribute' => false,
            ],
        ];
    }

    public function setLocation(string $location): self
    {
        [$lat, $lon] = (new LocationParser($location))->parse();
        $this->location_lat = $lat;
        $this->location_lon = $lon;
        return $this;
    }

    public function getLocation(): string
    {
        return ($this->location_lat && $this->location_lon) ?
            implode(',', [$this->location_lat, $this->location_lon]) :
            '';
    }

    public function getCurrency(): ActiveQuery
    {
        return $this->hasOne(Currency::class, ['id' => 'currency_id']);
    }

    public function isActive(): bool
    {
        return $this->status == self::STATUS_ON;
    }

    public function getMatches(): ActiveQuery
    {
        return $this->hasMany(Vacancy::class, ['id' => 'vacancy_id'])
            ->viaTable('{{%job_resume_match}}', ['resume_id' => 'id']);
    }

    public function getMatchedVacancies(): VacancyQuery
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

    public function getCounterMatches(): ActiveQuery
    {
        return $this->hasMany(Vacancy::class, ['id' => 'vacancy_id'])
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

    public function getLanguagesRelation(): ActiveQuery
    {
        return $this->hasMany(Language::class, ['id' => 'language_id'])
            ->viaTable('{{%user_language}}', ['user_id' => 'user_id']);
    }

    public function getUserLanguagesRelation(): ActiveQuery
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

    public function getGlobalUser(): ActiveQuery
    {
        return $this->hasOne(GlobalUser::class, ['id' => 'user_id']);
    }

    public function getKeywordsRelation(): ActiveQuery
    {
        return $this->hasMany(JobKeyword::class, ['id' => 'job_keyword_id'])
            ->viaTable('{{%job_resume_keyword}}', ['resume_id' => 'id']);
    }

    public function afterSave($insert, $changedAttributes)
    {
        if (isset($changedAttributes['status'])) {
            if ($this->status == self::STATUS_OFF) {
                $this->clearMatches();
            }
        }

        parent::afterSave($insert, $changedAttributes);
    }

    public function notPossibleToChangeStatus(): array
    {
        $notFilledFields = [];

        if (!$this->getLanguagesRelation()->count()) {
            $notFilledFields[] = Yii::t('bot', $this->getAttributeLabel('languages'))
                . ' (' . Yii::t('bot', 'in your profile') . ')';
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
