<?php

declare(strict_types=1);

namespace app\models;

use app\components\helpers\ArrayHelper;
use app\models\events\interfaces\ViewedByUserInterface;
use app\models\interfaces\MatchesInterface;
use app\models\matchers\ModelLinker;
use app\models\queries\AdOfferQuery;
use app\models\scenarios\AdOffer\UpdateScenario;
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
 * Class AdOffer
 *
 * @property int $id
 * @property int $user_id
 * @property int $section
 * @property string $title
 * @property string $description
 * @property int $currency_id
 * @property double $price
 * @property int $delivery_radius
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
 * @property AdPhoto[] $photos
 * @property AdOfferMatch[] $matches
 * @property AdSearch[] $matchModels
 * @property AdSearchMatch[] $counterMatches
 * @property AdSearch[] $counterMatchModels
 */
class AdOffer extends ActiveRecord implements ViewedByUserInterface, MatchesInterface
{
    public const STATUS_OFF = 0;
    public const STATUS_ON = 1;

    public const LIVE_DAYS = 7;

    public const EVENT_KEYWORDS_UPDATED = 'keywordsUpdated';

    public $keywordsFromForm = [];

    public function init()
    {
        $this->on(self::EVENT_KEYWORDS_UPDATED, [$this, 'clearMatches']);

        parent::init();
    }

    public static function tableName(): string
    {
        return '{{%ad_offer}}';
    }

    public function markViewedByUserId(int $userId)
    {
        $response = AdOfferResponse::findOrNewResponse($userId, $this->id);
        $response->viewed_at = time();
        $response->save();
    }

    public function rules(): array
    {
        return [
            [['user_id', 'section', 'title', 'location'], 'required'],
            [['user_id', 'currency_id', 'delivery_radius', 'section', 'status', 'created_at', 'processed_at'], 'integer'],
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
            ['delivery_radius', RadiusValidator::class],
            [
                'delivery_radius',
                'default',
                'value' => 0,
            ],
            ['location_lat', LocationLatValidator::class, 'skipOnEmpty' => false],
            ['location_lon', LocationLonValidator::class, 'skipOnEmpty' => false],
            ['location', 'string'],
            [
                'price',
                'double',
                'min' => 0,
                'max' => 9999999999999.99,
            ],
            [
                'currency_id', 'required', 'when' => function (self $model) {
                    return $model->price != '';
                },
                'whenClient' => new JsExpression("function () {
                       return $('#".Html::getInputId($this, 'price')."').val() != '';
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
            ['keywordsFromForm', 'each', 'rule' => ['integer']],
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
            'price' => Yii::t('app', 'Price'),
            'delivery_radius' => Yii::t('app', 'Delivery radius'),
            'location' => Yii::t('app', 'Location'),
            'location_lat' => Yii::t('app', 'Location Lat'),
            'location_lon' => Yii::t('app', 'Location Lon'),
            'status' => Yii::t('app', 'Status'),
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

    public static function find(): AdOfferQuery
    {
        return new AdOfferQuery(get_called_class());
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
            ->viaTable(AdOfferKeyword::tableName(), ['ad_offer_id' => 'id'])
            ->orderBy(['keyword' => SORT_ASC]);
    }

    public function getKeywordsAsArray(): array
    {
        return ArrayHelper::getColumn($this->getKeywords()->asArray()->all(), 'keyword');
    }

    public function getPhotos(): ActiveQuery
    {
        return $this->hasMany(AdPhoto::class, ['ad_offer_id' => 'id']);
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
        return AdSection::getAdOfferName($this->section);
    }

    public function getMatches(): ActiveQuery
    {
        return $this->hasMany(AdOfferMatch::class, ['ad_offer_id' => 'id']);
    }

    public function getMatchModels(): ActiveQuery
    {
        return $this->hasMany(AdSearch::class, ['id' => 'ad_search_id'])
            ->viaTable('{{%ad_offer_match}}', ['ad_offer_id' => 'id']);
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function getNewMatches(): ActiveQuery
    {
        return $this->hasMany(AdOfferMatch::class, ['ad_offer_id' => 'id'])
            ->andWhere([
                'not in',
                AdOfferMatch::tableName() . '.ad_search_id',
                AdSearchResponse::find()
                    ->select('ad_search_id')
                    ->andWhere([
                        'user_id' => $this->user_id,
                    ])
                    ->andWhere([
                        'is not', 'viewed_at', null,
                    ]),
            ]);
    }

    public function isNewMatch()
    {
        return !AdOfferResponse::find()
            ->andWhere([
                'user_id' => Yii::$app->user->id,
                'ad_offer_id' => $this->id,
            ])
            ->andWhere([
                'is not', 'viewed_at', null,
            ])
            ->exists();
    }

    public function getCounterMatches(): ActiveQuery
    {
        return $this->hasMany(AdSearchMatch::class, ['ad_offer_id' => 'id']);
    }

    public function getCounterMatchModels(): ActiveQuery
    {
        return $this->hasMany(AdSearch::class, ['id' => 'ad_search_id'])
            ->viaTable('{{%ad_search_match}}', ['ad_offer_id' => 'id']);
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
