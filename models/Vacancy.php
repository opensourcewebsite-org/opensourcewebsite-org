<?php
declare(strict_types=1);

namespace app\models;

use app\components\helpers\ArrayHelper;
use app\models\events\interfaces\ViewedByUserInterface;
use app\models\events\ViewedByUserEvent;
use app\models\interfaces\ModelWithLocationInterface;
use app\models\matchers\ModelLinker;
use app\models\scenarios\Vacancy\UpdateScenario;
use app\modules\bot\components\helpers\LocationParser;
use Yii;
use app\models\queries\VacancyQuery;
use app\models\User as GlobalUser;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use app\modules\bot\validators\LocationLatValidator;
use app\modules\bot\validators\LocationLonValidator;
use yii\helpers\Html;
use yii\web\JsExpression;

/**
 * Class Vacancy
 * @package app\models
 *
 * @property int $id
 * @property int $user_id
 * @property int $company_id
 * @property ?int $currency_id
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
 * @property string $location
 *
 * @property Company $company
 * @property Currency $currency
 * @property Gender $gender
 * @property Resume[] $matches
 * @property Resume[] $matchedResumes
 * @property Resume[] $counterMatches
 * @property VacancyLanguage[] $languagesWithLevels
 * @property User $user
 * @property JobKeyword[] $keywords
 *
 */
class Vacancy extends ActiveRecord implements ModelWithLocationInterface, ViewedByUserInterface
{
    public const STATUS_OFF = 0;
    public const STATUS_ON = 1;

    public const LIVE_DAYS = 30;

    public const REMOTE_OFF = 0;
    public const REMOTE_ON = 1;

    public const EVENT_KEYWORDS_CHANGED = 'keywordsChanged';
    public const EVENT_LANGUAGES_CHANGED = 'languagesChanged';

    public $keywordsFromForm = [];

    public function init()
    {
        $this->on(self::EVENT_KEYWORDS_CHANGED, [$this, 'clearMatches']);
        $this->on(self::EVENT_LANGUAGES_CHANGED, [$this, 'clearMatches']);
        $this->on(self::EVENT_VIEWED_BY_USER, [$this, 'markViewedByUser']);

        parent::init();
    }

    public function markViewedByUser(ViewedByUserEvent $event)
    {
        (new JobVacancyResponse(['user_id' => $event->user->id, 'vacancy_id' => $this->id]))->save();
    }

    public static function tableName(): string
    {
        return '{{%vacancy}}';
    }

    public function getTableName(): string
    {
        return static::tableName();
    }

    public function rules(): array
    {
        return [
            [
                [
                    'user_id',
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
                'remote_on', 'boolean'
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
                'currency_id', 'required', 'when' => function (self $model) {
                        return $model->max_hourly_rate != '';
                },
                'whenClient' => new JsExpression("function () {
                       return $('#".Html::getInputId($this, 'max_hourly_rate')."').val() != '';
                }"),
            ],
            [
                'keywordsFromForm', 'filter', 'filter' => function($val) {
                    if ($val === '')  {
                        return [];
                    }
                    return $val;
                }
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
        return $this->getUser();
    }

    public function getUser(): ActiveQuery
    {
        return $this->hasOne(GlobalUser::class, ['id' => 'user_id']);
    }

    public function getKeywords(): ActiveQuery
    {
        return $this->hasMany(JobKeyword::class, ['id' => 'job_keyword_id'])
            ->viaTable('{{%job_vacancy_keyword}}', ['vacancy_id' => 'id'])
            ->orderBy(['keyword' => SORT_ASC]);
    }

    public function clearMatches()
    {
        (new ModelLinker($this))->clearMatches();
    }

    public function notPossibleToChangeStatus(): array
    {
       return [];
    }

    public function beforeSave($insert)
    {
        if ((new UpdateScenario($this))->run()) {
            $this->processed_at = null;
        }

        return parent::beforeSave($insert);
    }
}
