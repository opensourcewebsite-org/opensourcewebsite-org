<?php

namespace app\models;

use yii\behaviors\TimestampBehavior;
use app\components\helpers\ArrayHelper;
use app\modules\bot\validators\RadiusValidator;
use yii\db\ActiveRecord;
use app\models\User as GlobalUser;

/**
 * Class AdOffer
 *
 * @package app\modules\bot\models
 */
class AdOffer extends ActiveRecord
{
    public const STATUS_OFF = 0;
    public const STATUS_ON = 1;

    public const LIVE_DAYS = 30;

    public static function tableName()
    {
        return 'ad_offer';
    }

    public function rules()
    {
        return [
            [['user_id', 'section', 'title', 'location_lat', 'location_lon', 'delivery_radius', 'status'], 'required'],
            [['title', 'description', 'location_lat', 'location_lon'], 'string'],
            ['delivery_radius', RadiusValidator::class],
            [['user_id', 'currency_id', 'delivery_radius', 'section', 'status', 'created_at', 'processed_at'], 'integer'],
            [['price'], 'number'],
        ];
    }

    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => TimestampBehavior::className(),
                'updatedAtAttribute' => false,
            ],
        ];
    }

    public function getKeywords()
    {
        return $this->hasMany(AdKeyword::className(), ['id' => 'ad_keyword_id'])
            ->viaTable('{{%ad_offer_keyword}}', ['ad_offer_id' => 'id']);
    }

    public function getPhotos()
    {
        return $this->hasMany(AdPhoto::className(), ['ad_offer_id' => 'id']);
    }

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
        return $this->hasMany(AdSearch::className(), ['id' => 'ad_search_id'])
            ->viaTable('{{%ad_offer_match}}', ['ad_offer_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     * @throws \yii\base\InvalidConfigException
     */
    public function getCounterMatches()
    {
        return $this->hasMany(AdSearch::className(), ['id' => 'ad_search_id'])
            ->viaTable('{{%ad_search_match}}', ['ad_offer_id' => 'id']);
    }

    public function updateMatches()
    {
        $this->unlinkAll('matches', true);
        $this->unlinkAll('counterMatches', true);

        $adSearchQuery = AdSearch::find()
            ->where(['!=', AdSearch::tableName() .'.user_id', $this->user_id])
            ->andWhere([AdSearch::tableName() . '.status' => AdSearch::STATUS_ON])
            ->joinWith('globalUser')
            ->andWhere(['>=', 'user.last_activity_at', time() - AdSearch::LIVE_DAYS * 24 * 60 * 60])
            ->andWhere([AdSearch::tableName() . '.section' => $this->section])
            ->andWhere("ST_Distance_Sphere(POINT($this->location_lon, $this->location_lat), POINT(ad_search.location_lon, ad_search.location_lat)) <= 1000 * (ad_search.pickup_radius + $this->delivery_radius)");

        $adSearchQueryNoKeywords = clone $adSearchQuery;
        $adSearchQueryNoKeywords = $adSearchQueryNoKeywords
            ->andWhere(['not in', AdSearch::tableName() . '.id', AdSearchKeyword::find()->select('ad_search_id')]);

        $adSearchQueryKeywords = clone $adSearchQuery;
        $adSearchQueryKeywords = $adSearchQuery
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

    public static function validatePrice($price)
    {
        return is_numeric($price) && round($price, 2) == $price && $price >= 0;
    }

    public static function validateLocation($location)
    {
        $slices = self::getLocationSlices(self::removeExtraChars($location));

        if (!isset($slices)) {
            return false;
        }

        $latitude = $slices[0];
        $longitude = $slices[1];

        return is_numeric($latitude) && is_numeric($longitude)
            && doubleval($latitude) >= -90 && doubleval($latitude) <= 90
            && doubleval($longitude) >= -180 && doubleval($longitude) <= 180;
    }

    public static function getLatitudeFromText($location)
    {
        $slices = self::getLocationSlices(self::removeExtraChars($location));

        return isset($slices) ? $slices[0] : null;
    }

    public static function getLongitudeFromText($location)
    {
        $slices = self::getLocationSlices(self::removeExtraChars($location));

        return isset($slices) ? $slices[1] : null;
    }

    public static function validateRadius($radius)
    {
        return is_numeric($radius) && $radius >= 0;
    }

    private static function removeExtraChars($str)
    {
        return preg_replace('/[^\d\.\- ]/', '', $str);
    }

    private static function getLocationSlices($location)
    {
        $slices = explode(' ', $location);

        if (count($slices) != 2) {
            return null;
        } else {
            return $slices;
        }
    }

    public function getGlobalUser()
    {
        return $this->hasOne(GlobalUser::className(), ['id' => 'user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCurrencyRelation()
    {
        return $this->hasOne(Currency::class, ['id' => 'currency_id']);
    }

    /** @inheritDoc */
    public function attributeLabels()
    {
        return ArrayHelper::merge(
            parent::attributeLabels(),
            [
                'delivery_radius' => 'Delivery radius',
            ]
        );
    }

    /** @inheritDoc */
    public function afterSave($insert, $changedAttributes)
    {
        if (isset($changedAttributes['status']) && $this->status == self::STATUS_OFF) {
            $this->unlinkAll('matches', true);
        }
        parent::afterSave($insert, $changedAttributes);
    }
}
