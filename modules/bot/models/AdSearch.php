<?php
namespace app\modules\bot\models;

use Yii;
use yii\db\ActiveRecord;

class AdSearch extends ActiveRecord
{
    private const EARTH_RADIUS = 6372.795;
    public const STATUS_OFF = 0;
    public const STATUS_ON = 1;

    public const LIVE_DAYS = 14;

    public static function tableName()
    {
        return 'ad_search';
    }

    public function rules()
    {
        return [
            [['user_id', 'category_id', 'pickup_radius', 'location_latitude', 'location_longitude', 'status', 'created_at', 'renewed_at'], 'required'],
            [['location_latitude', 'location_longitude', 'status'], 'string'],
            [['user_id', 'category_id', 'currency_id', 'max_price', 'pickup_radius', 'created_at', 'renewed_at', 'edited_at'], 'integer'],
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
            ->viaTable('{{%ad_search_keyword}}', ['ad_search_id' => 'id']);
    }

    public function isActive()
    {
        return $this->status == self::STATUS_ON && (time() - $this->renewed_at) <= self::LIVE_DAYS * 24 * 60 * 60;
    }

    public function matches($adOrder)
    {
        return $this->matchesKeywords($adOrder)
            && $this->distance($adOrder) <= $this->pickup_radius + $adOrder->delivery_radius
            && $this->category_id == $adOrder->category_id;
    }

    private function matchesKeywords($adOrder)
    {
        $adSearchKeywords = array_map(function ($adKeyword) {
            return $adKeyword->id;
        }, $this->getKeywords()->all());

        $adOrderKeywords = array_map(function ($adKeyword) {
            return $adKeyword->id;
        }, $adOrder->getKeywords()->all());

        return !empty(array_intersect($adSearchKeywords, $adOrderKeywords));
    }

    private function distance($adOrder)
    {
        $lat1 = doubleval($this->location_latitude) * M_PI / 180;
        $lon1 = doubleval($this->location_longitude) * M_PI / 180;

        $lat2 = doubleval($adOrder->location_latitude) * M_PI / 180;
        $lon2 = doubleval($adOrder->location_longitude) * M_PI / 180;

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

    public function getMatches()
    {
        return $this->hasMany(AdOrder::className(), ['id' => 'ad_order_id'])
            ->viaTable('{{%ad_matches}}', ['ad_search_id' => 'id']);
    }

    public function updateMatches()
    {
        $this->unlinkAll('matches', true);

        if (!$this->isActive()) {
            return;
        }

        foreach ($this->getKeywords()->all() as $adKeyword) {
            foreach (AdOrderKeyword::find()->where([
                'ad_keyword_id' => $adKeyword->id,
            ])->all() as $adOrderKeyword) {
                $adOrder = AdOrder::findOne($adOrderKeyword->ad_order_id);

                $adOrderMatches = $this->getMatches()->where([
                    'id' => $adOrder->id
                ])->one();

                if ($this->matches($adOrder) && !isset($adOrderMatches) && $adOrder->isActive()) {
                    $this->link('matches', $adOrder);
                }
            }
        }
    }

    public function markToUpdateMatches()
    {
        if ($this->edited_at === null) {
            $this->setAttributes([
                'edited_at' => time(),
            ]);

            $this->save();
        }
    }
}
