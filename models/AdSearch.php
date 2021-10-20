<?php

namespace app\models;

use app\components\helpers\ArrayHelper;
use app\models\events\interfaces\ViewedByUserInterface;
use app\models\events\ViewedByUserEvent;
use app\models\matchers\ModelLinker;
use app\models\queries\AdSearchQuery;
use app\models\scenarios\AdSearch\UpdateScenario;
use app\modules\bot\components\helpers\LocationParser;
use Yii;
use yii\behaviors\TimestampBehavior;
use app\modules\bot\validators\RadiusValidator;
use app\modules\bot\validators\LocationLatValidator;
use app\modules\bot\validators\LocationLonValidator;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\helpers\Html;
use yii\web\JsExpression;

/**
 * Class AdSearch
 *
 * @property int $id
 * @property int $user_id
 * @property int $section
 * @property string $title
 * @property string $description
 * @property int $currency_id
 * @property double $max_price
 * @property int $pickup_radius
 * @property string $location_lat
 * @property string $location_lon
 * @property int $status
 * @property int $created_at
 * @property int $processed_at
 *
 * @property string $location
 * @property User $user
 * @property Currency $currency
 * @property string $sectionName
 * @property AdKeyword[] $keywords
 * @property AdOffer[] $matches
 * @property AdOffer[] $counterMatches
 */
class AdSearch extends ActiveRecord implements ViewedByUserInterface
{
    public const STATUS_OFF = 0;
    public const STATUS_ON = 1;

    public const LIVE_DAYS = 30;

    public const EVENT_KEYWORDS_UPDATED = 'keywordsUpdated';

    public $keywordsFromForm = [];

    public function init()
    {
        $this->on(self::EVENT_KEYWORDS_UPDATED, [$this, 'clearMatches']);
        $this->on(self::EVENT_VIEWED_BY_USER, [$this, 'markViewedByUser']);

        parent::init();
    }

    public static function tableName(): string
    {
        return 'ad_search';
    }

    public function markViewedByUser(ViewedByUserEvent $event)
    {
        $response = AdSearchResponse::findOrNewResponse($event->user->id, $this->id);
        $response->viewed_at = time();
        $response->save();
    }

    public function rules(): array
    {
        return [
            [
                [
                    'title',
                    'location',
                    'location_lat',
                    'location_lon',
                ],
                'required',
            ],
            [
                'pickup_radius',
                RadiusValidator::class,
            ],
            [
                'pickup_radius',
                'default',
                'value' => 0,
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
                'location', 'string'
            ],
            [
                'title',
                'string',
                'max' => 255,
            ],
            [
                'description',
                'string',
                'max' => 10000,
            ],
            [
                [
                    'user_id',
                    'section',
                    'currency_id',
                    'pickup_radius',
                    'status',
                    'created_at',
                    'processed_at',
                ],
                'integer',
            ],
            [
                'max_price',
                'double',
                'min' => 0,
                'max' => 9999999999999.99,
            ],
            [
                'currency_id', 'required', 'when' => function (self $model) {
                    return $model->max_price != '';
                },
                'whenClient' => new JsExpression("function () {
                       return $('#".Html::getInputId($this, 'max_price')."').val() != '';
                }"),
            ],
            [
                'keywordsFromForm', 'filter', 'filter' => function ($val) {
                    if ($val === '') {
                        return [];
                    }
                    return $val;
                }
            ],
            [
                'keywordsFromForm', 'each', 'rule' => ['integer']
            ],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'user_id' => Yii::t('app', 'User'),
            'section' => Yii::t('app', 'Section'),
            'title' => Yii::t('app', 'Title'),
            'description' => Yii::t('app', 'Description'),
            'currency_id' => Yii::t('app', 'Currency'),
            'max_price' => Yii::t('app', 'Max. price'),
            'pickup_radius' => Yii::t('app', 'Pickup radius'),
            'location' => Yii::t('app', 'Location'),
            'location_lat' => Yii::t('app', 'Location Lat'),
            'location_lon' => Yii::t('app', 'Location Lon'),
            'status' => Yii::t('app', 'Status'),
            'created_at' => Yii::t('app', 'Created At'),
            'processed_at' => Yii::t('app', 'Processed At')
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

    public static function find(): AdSearchQuery
    {
        return new AdSearchQuery(get_called_class());
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

    public function getKeywordsFromForm(): array
    {
        return ArrayHelper::getColumn($this->getKeywords()->asArray()->all(), 'id');
    }

    public function getKeywords(): ActiveQuery
    {
        return $this->hasMany(AdKeyword::class, ['id' => 'ad_keyword_id'])
            ->viaTable('{{%ad_search_keyword}}', ['ad_search_id' => 'id']);
    }

    public function getUser(): ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    public function getCurrency(): ActiveQuery
    {
        return $this->hasOne(Currency::class, ['id' => 'currency_id']);
    }

    public function getSectionName(): string
    {
        return AdSection::getAdSearchName($this->section);
    }

    public function getMatches(): ActiveQuery
    {
        return $this->hasMany(AdOffer::class, ['id' => 'ad_offer_id'])
            ->viaTable('{{%ad_search_match}}', ['ad_search_id' => 'id']);
    }

    public function getMatchesCount()
    {
        return $this->hasMany(AdSearchMatch::class, ['ad_search_id' => 'id'])
            ->count();
    }

    /**
     * @return \yii\db\ActiveQuery
     * @throws \yii\base\InvalidConfigException
     */
    // TODO new matches
    public function getNewMatches(): ActiveQuery
    {
        return $this->hasMany(AdOffer::class, ['id' => 'ad_offer_id'])
            ->viaTable('{{%ad_search_match}}', ['ad_search_id' => 'id']);
    }

    public function getNewMatchesCount()
    {
        return $this->hasMany(AdSearchMatch::class, ['ad_search_id' => 'id'])
            ->andWhere([
                'not in',
                'ad_offer_id',
                AdOfferResponse::find()
                    ->select('ad_offer_id')
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
        return !(bool)AdSearchResponse::find()
            ->andWhere([
                'user_id' => Yii::$app->user->id,
                'ad_search_id' => $this->id,
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
    public function getMatchesOrderedByUserRating(): ActiveQuery
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
        return $this->hasMany(AdOffer::class, ['id' => 'ad_offer_id'])
            ->viaTable('{{%ad_offer_match}}', ['ad_search_id' => 'id']);
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
