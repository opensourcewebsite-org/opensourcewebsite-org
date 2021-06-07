<?php
declare(strict_types=1);

namespace app\models;

use app\components\helpers\ArrayHelper;
use app\modules\bot\components\helpers\LocationParser;
use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use app\modules\bot\validators\RadiusValidator;
use app\modules\bot\validators\LocationLatValidator;
use app\modules\bot\validators\LocationLonValidator;

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
 *
 * @property AdKeyword[] $keywords
 * @property AdPhoto[] $photos
 * @property AdSearch[] $matches
 * @property AdSearch[] $counterMatches
 *
 */
class AdOffer extends ActiveRecord
{
    public const STATUS_OFF = 0;
    public const STATUS_ON = 1;

    public const LIVE_DAYS = 30;

    public const EVENT_KEYWORDS_UPDATED = 'keywordsUpdated';

    public $keywordsFromForm = [];

    public function init()
    {
        $this->on(self::EVENT_KEYWORDS_UPDATED, [$this, 'clearMatches']);
        parent::init();
    }

    public static function tableName(): string
    {
        return 'ad_offer';
    }

    public function rules(): array
    {
        return [
            [
                [
                    'user_id',
                    'section',
                    'title',
                    'location_lat',
                    'location_lon',
                    'delivery_radius',
                ],
                'required',
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
                'delivery_radius',
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
                'location', 'string'
            ],
            [
                [
                    'user_id',
                    'currency_id',
                    'delivery_radius',
                    'section',
                    'status',
                    'created_at',
                    'processed_at',
                ],
                'integer',
            ],
            [
                'price',
                'double',
                'min' => 0,
                'max' => 9999999999999.99,
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

    public function isActive(): bool
    {
        return (int)$this->status === self::STATUS_ON;
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
            ->viaTable('{{%ad_offer_keyword}}', ['ad_offer_id' => 'id']);
    }

    public function getPhotos(): ActiveQuery
    {
        return $this->hasMany(AdPhoto::class, ['ad_offer_id' => 'id']);
    }

    public function getUser(): ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    public function getGlobalUser(): ActiveQuery
    {
        return $this->getUser();
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
        return $this->hasMany(AdSearch::class, ['id' => 'ad_search_id'])
            ->viaTable('{{%ad_offer_match}}', ['ad_offer_id' => 'id']);
    }

    public function getCounterMatches(): ActiveQuery
    {
        return $this->hasMany(AdSearch::class, ['id' => 'ad_search_id'])
            ->viaTable('{{%ad_search_match}}', ['ad_offer_id' => 'id']);
    }

    public function updateMatches()
    {
        $this->unlinkAll('matches', true);
        $this->unlinkAll('counterMatches', true);

        $adSearchQuery = AdSearch::find()
            ->where(['!=', AdSearch::tableName() . '.user_id', $this->user_id])
            ->andWhere([AdSearch::tableName() . '.status' => AdSearch::STATUS_ON])
            ->joinWith('user')
            ->andWhere(['>=', 'user.last_activity_at', time() - AdSearch::LIVE_DAYS * 24 * 60 * 60])
            ->andWhere([AdSearch::tableName() . '.section' => $this->section])
            ->andWhere("ST_Distance_Sphere(POINT($this->location_lon, $this->location_lat), POINT(ad_search.location_lon, ad_search.location_lat)) <= 1000 * (ad_search.pickup_radius + $this->delivery_radius)");

        $adSearchQueryNoKeywords = clone $adSearchQuery;
        $adSearchQueryNoKeywords = $adSearchQueryNoKeywords
            ->andWhere(['not in', AdSearch::tableName() . '.id', AdSearchKeyword::find()->select('ad_search_id')]);

        $adSearchQueryKeywords = clone $adSearchQuery;
        $adSearchQueryKeywords = $adSearchQueryKeywords
                ->joinWith(['keywords' => function ($query) {
                    $query
                        ->joinWith('adOffers')
                        ->andWhere([AdOffer::tableName() . '.id' => $this->id]);
                }])
                ->groupBy(AdSearch::tableName() . '.id');

        if ($this->getKeywords()->count() > 0) {
            foreach ($adSearchQueryKeywords->all() as $adSearch) {
                $this->link('matches', $adSearch);
                $this->link('counterMatches', $adSearch);
            }

            foreach ($adSearchQueryNoKeywords->all() as $adSearch) {
                $this->link('counterMatches', $adSearch);
            }
        } else {
            foreach ($adSearchQueryKeywords->all() as $adSearch) {
                $this->link('matches', $adSearch);
            }

            foreach ($adSearchQueryNoKeywords->all() as $adSearch) {
                $this->link('matches', $adSearch);
                $this->link('counterMatches', $adSearch);
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
        if (isset($changedAttributes['status']) && $this->status == self::STATUS_OFF) {
            $this->clearMatches();
        }

        parent::afterSave($insert, $changedAttributes);
    }
}
