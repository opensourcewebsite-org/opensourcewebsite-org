<?php
namespace app\modules\bot\models;

use Yii;
use yii\db\ActiveRecord;
use yii\db\Query;
use app\models\User as GlobalUser;

class AdOffer extends ActiveRecord
{
    public const STATUS_OFF = 0;
    public const STATUS_ON = 1;

    public const LIVE_DAYS = 14;
    public const MAX_RADIUS = 1000;

    public static function tableName()
    {
        return 'ad_offer';
    }

    public function rules()
    {
        return [
            [['user_id', 'section', 'title', 'location_lat', 'location_lon', 'delivery_radius', 'status', 'created_at', 'renewed_at'], 'required'],
            [['title', 'description', 'location_lat', 'location_lon'], 'string'],
            [['user_id', 'currency_id', 'delivery_radius', 'section', 'status', 'created_at', 'renewed_at', 'processed_at'], 'integer'],
            [['price'], 'number'],
        ];
    }

    public function behaviors()
    {
        return [
            // TimestampBehavior::className(),
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
        return $this->status == self::STATUS_ON && (time() - $this->renewed_at) <= self::LIVE_DAYS * 24 * 60 * 60;
    }

    public function getMatches()
    {
        return $this->hasMany(AdSearch::className(), ['id' => 'ad_search_id'])
            ->viaTable('{{%ad_match}}', ['ad_offer_id' => 'id'], function ($query) {
                $query->andWhere(['or', ['type' => 0], ['type' => 2]]);
            });
    }

    public function getAllMatches()
    {
        return $this->hasMany(AdSearch::className(), ['id' => 'ad_search_id'])
            ->viaTable('{{%ad_match}}', ['ad_offer_id' => 'id']);
    }

    public function updateMatches()
    {
        $this->unlinkAll('allMatches', true);

        $adSearchQuery = AdSearch::find()
            // ->where(['!=', 'ad_search.user_id', $this->user_id])
            ->andWhere(['ad_search.status' => AdSearch::STATUS_ON])
            ->andWhere(['>=', 'ad_search.renewed_at', time() - AdSearch::LIVE_DAYS * 24 * 60 * 60])
            ->andWhere(['ad_search.section' => $this->section])
            ->andWhere("ST_Distance_Sphere(POINT($this->location_lat, $this->location_lon), POINT(ad_search.location_lat, ad_search.location_lon)) <= 1000 * (ad_search.pickup_radius + $this->delivery_radius)");

        $adSearchQueryNoKeywords = clone $adSearchQuery;
        $adSearchQueryNoKeywords = $adSearchQueryNoKeywords
            ->andWhere(['not in', 'ad_search.id', AdSearchKeyword::find()->select('ad_search_id')]);

        $adSearchQueryKeywords = clone $adSearchQuery;
        $adSearchQueryKeywords = $adSearchQuery
                ->joinWith(['keywords' => function ($query) {
                    $query
                        ->joinWith('adOffers')
                        ->andWhere(['ad_offer.id' => $this->id]);
                }])
                ->groupBy('ad_search.id');

        if ($this->getKeywords()->count() > 0) {
            foreach ($adSearchQueryKeywords->all() as $adSearch) {
                $this->link('matches', $adSearch, ['type' => 2]);
            }

            foreach ($adSearchQueryNoKeywords->all() as $adSearch) {
                $this->link('matches', $adSearch, ['type' => 1]);
            }
        } else {
            foreach ($adSearchQueryKeywords->all() as $adSearch) {
                $this->link('matches', $adSearch, ['type' => 0]);
            }

            foreach ($adSearchQueryNoKeywords->all() as $adSearch) {
                $this->link('matches', $adSearch, ['type' => 2]);
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
        return $this->hasOne(GlobalUser::className(), ['id' => 'user_id'])
            ->viaTable('{{%bot_user}}', ['id' => 'user_id']);
    }
}
