<?php
namespace app\modules\bot\models;

use Yii;
use yii\db\ActiveRecord;

class AdSearch extends ActiveRecord
{
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
            [['user_id', 'section', 'title', 'pickup_radius', 'location_lat', 'location_lon', 'status', 'created_at', 'renewed_at'], 'required'],
            [['title', 'location_lat', 'location_lon'], 'string'],
            [['user_id', 'section', 'currency_id', 'pickup_radius', 'status', 'created_at', 'renewed_at', 'processed_at'], 'integer'],
            [['max_price'], 'number'],
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

        $adOrderQuery = AdOrder::find()
            ->where(['ad_order.status' => AdOrder::STATUS_ON])
            ->andWhere(['>=', 'ad_order.renewed_at', time() - AdOrder::LIVE_DAYS * 24 * 60 * 60])
            ->andWhere(['ad_order.section' => $this->section])
            ->andWhere("st_distance_sphere(POINT($this->location_lat, $this->location_lon), POINT(ad_order.location_lat, ad_order.location_lon)) <= 1000 * (ad_order.delivery_radius + $this->pickup_radius)")
            ->joinWith(['keywords' => function ($query) {
                $query
                    ->joinWith('adSearches')
                    ->andWhere(['ad_search.id' => $this->id]);
            }])
            ->groupBy('id');

        foreach ($adOrderQuery->all() as $adOrder) {
            $this->link('matches', $adOrder);
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
}
