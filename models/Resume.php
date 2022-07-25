<?php

namespace app\models;

use app\components\helpers\ArrayHelper;
use app\models\events\interfaces\ViewedByUserInterface;
use app\models\events\ViewedByUserEvent;
use app\models\interfaces\MatchesInterface;
use app\models\matchers\ModelLinker;
use app\models\queries\ResumeQuery;
use app\models\scenarios\Resume\UpdateScenario;
use app\modules\bot\components\helpers\LocationParser;
use app\modules\bot\validators\LocationLatValidator;
use app\modules\bot\validators\LocationLonValidator;
use app\modules\bot\validators\RadiusValidator;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\helpers\Html;
use yii\web\JsExpression;

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
 * @property string $location
 * @property int $created_at
 * @property int $processed_at
 *
 * @property Currency $currency
 * @property User $user
 * @property Vacancy[] $matches
 * @property UserLanguage[] $languages
 * @property JobKeyword[] $keywords
 */
class Resume extends ActiveRecord implements ViewedByUserInterface, MatchesInterface
{
    public const STATUS_OFF = 0;
    public const STATUS_ON = 1;

    public const LIVE_DAYS = 7;

    public const REMOTE_OFF = 0;
    public const REMOTE_ON = 1;

    public const EVENT_KEYWORDS_UPDATED = 'keywordsUpdated';

    public $keywordsFromForm = [];

    public static function tableName(): string
    {
        return '{{%resume}}';
    }

    public function init()
    {
        $this->on(self::EVENT_KEYWORDS_UPDATED, [$this, 'clearMatches']);
        $this->on(self::EVENT_VIEWED_BY_USER, [$this, 'markViewedByUser']);

        parent::init();
    }

    public function markViewedByUser(ViewedByUserEvent $event)
    {
        $response = JobResumeResponse::findOrNewResponse($event->user->id, $this->id);
        $response->viewed_at = time();
        $response->save();
    }

    public function rules(): array
    {
        return [
            [['user_id', 'name'], 'required'],
            [['user_id', 'currency_id', 'status', 'created_at', 'processed_at'], 'integer'],
            ['search_radius', RadiusValidator::class],
            ['location_lat', LocationLatValidator::class],
            ['location_lon', LocationLonValidator::class],
            ['location', 'string'],
            ['remote_on', 'boolean'],
            [
                'min_hourly_rate',
                'double',
                'min' => 0,
                'max' => 99999999.99,
            ],
            [
                'currency_id', 'required', 'when' => function (self $model) {
                    return $model->min_hourly_rate != '';
                },
                'whenClient' => new JsExpression("function () {
                       return $('#".Html::getInputId($this, 'min_hourly_rate')."').val() != '';
                }"),
            ],
            [
                [
                    'name',
                ],
                'string',
                'max' => 255,
            ],
            [
                'keywordsFromForm', 'filter', 'filter' => function ($val) {
                    if ($val === '') {
                        return [];
                    }
                    return $val;
                }
            ],
            ['keywordsFromForm', 'each', 'rule' => ['integer']],
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
            'remote_on' => Yii::t('jo', 'Remote work'),
            'name' => Yii::t('app', 'Title'),
            'min_hourly_rate' => Yii::t('bot', 'Min. hourly rate'),
            'search_radius' => Yii::t('bot', 'Search radius'),
            'currency_id' => Yii::t('app', 'Currency'),
            'keywordsFromForm' => Yii::t('app', 'Keywords'),
            'experiences' => Yii::t('app', 'Experiences'),
            'expectations' => Yii::t('app', 'Expectations'),
            'skills' => Yii::t('app', 'Skills'),
            'location_lat' => Yii::t('app', 'Location Lat'),
            'location_lon' => Yii::t('app', 'Location Lon'),
            'location' => Yii::t('app', 'Location'),
            'created_at' => Yii::t('app', 'Created At'),
            'processed_at' => Yii::t('app', 'Processed At'),
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

    public function isActive(): bool
    {
        return $this->status == static::STATUS_ON;
    }

    public function isRemote(): bool
    {
        return (bool)$this->remote_on;
    }

    public function getKeywordsFromForm(): array
    {
        return ArrayHelper::getColumn($this->getKeywords()->asArray()->all(), 'id');
    }

    public function getKeywords(): ActiveQuery
    {
        return $this->hasMany(JobKeyword::class, ['id' => 'job_keyword_id'])
            ->viaTable('{{%job_resume_keyword}}', ['resume_id' => 'id'])
            ->orderBy(['keyword' => SORT_ASC]);
    }

    public function getCurrency(): ActiveQuery
    {
        return $this->hasOne(Currency::class, ['id' => 'currency_id']);
    }

    public function getLanguages(): ActiveQuery
    {
        return $this->hasMany(UserLanguage::class, ['user_id' => 'user_id']);
    }

    public function getUser(): ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    public function getMatches(): ActiveQuery
    {
        return $this->hasMany(Vacancy::class, ['id' => 'vacancy_id'])
            ->viaTable('{{%job_resume_match}}', ['resume_id' => 'id']);
    }

    public function getMatchesCount()
    {
        return $this->hasMany(JobResumeMatch::class, ['resume_id' => 'id'])
            ->count();
    }

    /**
     * @return \yii\db\ActiveQuery
     * @throws \yii\base\InvalidConfigException
     */
    // TODO new matches
    public function getNewMatches(): ActiveQuery
    {
        return $this->hasMany(Vacancy::class, ['id' => 'vacancy_id'])
            ->viaTable('{{%job_resume_match}}', ['resume_id' => 'id']);
    }

    public function getNewMatchesCount()
    {
        return $this->hasMany(JobResumeMatch::class, ['resume_id' => 'id'])
            ->andWhere([
                'not in',
                'vacancy_id',
                JobVacancyResponse::find()
                    ->select('vacancy_id')
                    ->andWhere([
                        'user_id' => Yii::$app->user->id,
                    ])
                    ->andWhere([
                        'is not', 'viewed_at', null,
                    ]),
            ])
            ->count();
    }

    public function isNewMatch()
    {
        return !(bool)JobResumeResponse::find()
            ->andWhere([
                'user_id' => Yii::$app->user->id,
                'resume_id' => $this->id,
            ])
            ->andWhere([
                'is not', 'viewed_at', null,
            ])
            ->one();
    }

    /**
     * @return ActiveQuery
     * @throws \yii\base\InvalidConfigException
     */
    public function getMatchesOrderByRank(): ActiveQuery
    {
        return $this
            ->getMatches()
            ->joinWith('user')
            ->orderBy([
                'user.rating' => SORT_DESC,
                'user.created_at' => SORT_ASC,
            ]);
    }

    public function getCounterMatches(): ActiveQuery
    {
        return $this->hasMany(Vacancy::class, ['id' => 'vacancy_id'])
            ->viaTable('{{%job_vacancy_match}}', ['resume_id' => 'id']);
    }

    public function clearMatches()
    {
        (new ModelLinker($this))->clearMatches();
    }

    public function beforeSave($insert)
    {
        if (!$insert && (new UpdateScenario($this))->run()) {
            $this->processed_at = null;
        }

        return parent::beforeSave($insert);
    }
}
