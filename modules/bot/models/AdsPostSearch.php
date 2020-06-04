<?php
namespace app\modules\bot\models;

use Yii;
use yii\db\ActiveRecord;

class AdsPostSearch extends ActiveRecord
{
    private const EARTH_RADIUS = 6372.795;
    public const STATUS_ACTIVATED = 'activated';
    public const STATUS_NOT_ACTIVATED = 'not_activated';

    public const LIVE_DAYS = 14;

    public static function tableName()
    {
        return 'ads_post_search';
    }

    public function rules()
    {
        return [
            [['user_id', 'category_id', 'radius', 'location_latitude', 'location_longitude', 'updated_at', 'status'], 'required'],
            [['location_latitude', 'location_longitude', 'status'], 'string'],
            [['id', 'category_id', 'user_id', 'radius', 'currency_id', 'max_price', 'updated_at'], 'integer'],
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
        return $this->hasMany(AdKeyword::className(), ['id' => 'keyword_id'])
            ->viaTable('{{%ads_post_search_keyword}}', ['ads_post_search_id' => 'id']);
    }

    public function isActive()
    {
        return $this->status == self::STATUS_ACTIVATED && (time() - $this->updated_at) <= self::LIVE_DAYS * 24 * 60 * 60;
    }

    public function matches($adsPost)
    {
        if ($this->matchesKeywords($adsPost)) {
            Yii::warning("MATCH!");
        }

        return $this->matchesKeywords($adsPost)
            && $this->distance($adsPost) <= $this->radius + $adsPost->delivery_km;
    }

    private function matchesKeywords($adsPost)
    {
        $adsPostSearchKeywords = array_map(function ($adKeyword) {
            return $adKeyword->id;
        }, $this->getKeywords()->all());

        $adsPostKeywords = array_map(function ($adKeyword) {
            return $adKeyword->id;
        }, $adsPost->getKeywords()->all());

        return !empty(array_intersect($adsPostSearchKeywords, $adsPostKeywords));
    }

    private function distance($adsPost)
    {
        $lat1 = doubleval($this->location_latitude) * M_PI / 180;
        $lon1 = doubleval($this->location_longitude) * M_PI / 180;

        $lat2 = doubleval($adsPost->location_lat) * M_PI / 180;
        $lon2 = doubleval($adsPost->location_lon) * M_PI / 180;

        $cl1 = cos($lat1);
        $cl2 = cos($lat2);
        $sl1 = sin($lat1);
        $sl2 = sin($lat2);
        $delta = $lon2 - $lon1;
        $cdelta = cos($delta);
        $sdelta = sin($delta);

        $y = sqrt(pow($cl2 * $sdelta, 2) + pow($cl1 * $sl2 - $sl1 * $cl2 * $cdelta, 2));
        $x = $sl1 * $sl2 + $cl1 * $cl2 * $cdelta;

        $ad = atan2($y, $x);
        $dist = $ad * self::EARTH_RADIUS;

        return $dist;
    }
}
