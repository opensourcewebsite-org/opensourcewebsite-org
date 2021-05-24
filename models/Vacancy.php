<?php
declare(strict_types=1);

namespace app\models;

use app\components\helpers\ArrayHelper;
use Yii;
use app\models\queries\VacancyQuery;
use app\models\User as GlobalUser;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\conditions\AndCondition;
use app\modules\bot\validators\LocationLatValidator;
use app\modules\bot\validators\LocationLonValidator;

/**
 * Class Vacancy
 * @package app\models
 *
 * @property int $id
 * @property int $user_id
 * @property int $company_id
 * @property int $currency_id
 * @property int $gender_id
 * @property int $status
 * @property bool $remote_on
 * @property string $name
 * @property string $requirements
 * @property double $max_hourly_rate
 * @property string $conditions
 * @property string $responsibilities
 * @property string $location_lat
 * @property string $location_lon
 * @property int $created_at
 * @property int $processed_at
 *
 * @property Company $company
 * @property Currency $currency
 * @property Gender $gender
 * @property Resume[] $matches
 * @property Resume[] $matchedResumes
 * @property Resume[] $counterMatches
 * @property VacancyLanguage[] $vacancyLanguagesWithLevels
 * @property User $globalUser
 * @property JobKeyword[] $keywords
 *
 */
class Vacancy extends ActiveRecord
{
    public const STATUS_OFF = 0;
    public const STATUS_ON = 1;

    public const LIVE_DAYS = 30;

    public const REMOTE_OFF = 0;
    public const REMOTE_ON = 1;

    public array $keywordsFromForm = [];

    public static function tableName(): string
    {
        return '{{%vacancy}}';
    }

    public function rules(): array
    {
        return [
            [
                [
                    'user_id',
                    'currency_id',
                    'name',
                    'requirements',
                    'conditions',
                    'responsibilities',
                ],
                'required',
            ],
            [
                [
                    'user_id',
                    'company_id',
                    'currency_id',
                    'status',
                    'gender_id',
                    'created_at',
                    'processed_at',
                ],
                'integer',
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
                'max_hourly_rate',
                'double',
                'min' => 0,
                'max' => 99999999.99,
            ],
            [
                'keywordsFromForm', 'each', 'rule' => ['integer']
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
                    'requirements',
                    'conditions',
                    'responsibilities',
                ],
                'string',
                'max' => 10000,
            ],
        ];
    }

    public static function find(): VacancyQuery
    {
        return new VacancyQuery(get_called_class());
    }

    public function attributeLabels(): array
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'max_hourly_rate' => Yii::t('bot', 'Max. hourly rate'),
            'remote_on' => Yii::t('bot', 'Remote work'),
            'company_id' => Yii::t('app', 'Company'),
            'status' => Yii::t('app', 'Status'),
            'name' => Yii::t('app', 'Name'),
            'requirements' => Yii::t('app','Requirements'),
            'currency_id' => Yii::t('app', 'Currency'),
            'conditions' => Yii::t('app','Conditions'),
            'responsibilities' => Yii::t('app','Responsibilities'),
            'keywordsFromForm' => Yii::t('app', 'Keywords'),
            'gender_id' => Yii::t('app','Gender'),
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
                'class' => TimestampBehavior::class,
                'updatedAtAttribute' => false,
            ],
        ];
    }

    public function getCompany(): ActiveQuery
    {
        return $this->hasOne(Company::class, ['id' => 'company_id']);
    }

    public function getCurrency(): ActiveQuery
    {
        return $this->hasOne(Currency::class, ['id' => 'currency_id']);
    }

    public function getGender(): ActiveQuery
    {
        return $this->hasOne(Gender::class, ['id' => 'gender_id']);
    }

    public function isActive(): bool
    {
        return (int)$this->status === static::STATUS_ON;
    }

    public function setActive(): self
    {
        $this->status = static::STATUS_ON;

        return $this;
    }

    public function setInactive(): self
    {
        $this->status = static::STATUS_OFF;

        return $this;
    }

    public function isRemote(): bool
    {
        return $this->remote_on == static::REMOTE_ON;
    }

    public function getKeywordsFromForm(): array
    {
        return ArrayHelper::getColumn($this->getKeywords()->asArray()->all(), 'id');
    }

    public function getMatches(): ActiveQuery
    {
        return $this->hasMany(Resume::class, ['id' => 'resume_id'])
            ->viaTable('{{%job_vacancy_match}}', ['vacancy_id' => 'id']);
    }


    public function getMatchedResumes(): ActiveQuery
    {
        return Resume::find()
            ->live()
            ->matchLanguages($this)
            ->matchRadius($this)
            ->andWhere([
                '!=', Resume::tableName() . '.user_id', $this->user_id,
            ])
            ->groupBy(Resume::tableName() . '.id');
    }

    public function getCounterMatches(): ActiveQuery
    {
        return $this->hasMany(Resume::class, ['id' => 'resume_id'])
            ->viaTable('{{%job_resume_match}}', ['vacancy_id' => 'id']);
    }

    public function getLanguagesWithLevels(): ActiveQuery
    {
        return $this->hasMany(VacancyLanguage::class, ['vacancy_id' => 'id']);
    }

    public function getGlobalUser(): ActiveQuery
    {
        return $this->hasOne(GlobalUser::class, ['id' => 'user_id']);
    }

    public function getKeywords(): ActiveQuery
    {
        return $this->hasMany(JobKeyword::class, ['id' => 'job_keyword_id'])
            ->viaTable('{{%job_vacancy_keyword}}', ['vacancy_id' => 'id']);
    }

    public function updateMatches()
    {
        $this->unlinkAll('matches', true);
        $this->unlinkAll('counterMatches', true);

        $resumesQuery = $this->getMatchedResumes();
        $resumesQueryNoRateQuery = clone $resumesQuery;
        $resumesQueryRateQuery = clone $resumesQuery;

        if ($this->max_hourly_rate) {
            $resumesQueryRateQuery->andWhere(new AndCondition([
                ['IS NOT', Resume::tableName() . '.min_hourly_rate', null],
                ['<=', Resume::tableName() . '.min_hourly_rate', $this->max_hourly_rate],
                [Resume::tableName() . '.currency_id' => $this->currency_id],
            ]));
            $resumesQueryNoRateQuery->andWhere(
                new AndCondition([
                    ['>', Resume::tableName() . '.min_hourly_rate', $this->max_hourly_rate],
                    ['<>', Resume::tableName() . '.currency_id', $this->currency_id],
                ])
            );

            foreach ($resumesQueryRateQuery->all() as $resume) {
                $this->link('matches', $resume);
                $this->link('counterMatches', $resume);
            }

            foreach ($resumesQueryNoRateQuery->all() as $resume) {
                $this->link('counterMatches', $resume);
            }
        } else {
            foreach ($resumesQueryRateQuery->all() as $resume) {
                $this->link('matches', $resume);
            }
        }
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
       return [];
    }
}
