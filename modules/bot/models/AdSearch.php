<?php
namespace app\modules\bot\models;

use Yii;
use yii\db\ActiveRecord;
use app\models\User as GlobalUser;

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
            [['title', 'description', 'location_lat', 'location_lon'], 'string'],
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
        return $this->hasMany(AdOffer::className(), ['id' => 'ad_offer_id'])
            ->viaTable('{{%ad_match}}', ['ad_search_id' => 'id'], function ($query) {
                $query->andWhere(['or', ['type' => 1], ['type' => 2]]);
            });
    }

    public function getAllMatches()
    {
        return $this->hasMany(AdOffer::className(), ['id' => 'ad_offer_id'])
            ->viaTable('{{%ad_match}}', ['ad_search_id' => 'id']);
    }

    public function updateMatches()
    {
        $this->unlinkAll('allMatches', true);

        $adOfferQuery = AdOffer::find()
            ->where(['!=', 'ad_offer.user_id', $this->user_id])
            ->andWhere(['ad_offer.status' => AdOffer::STATUS_ON])
            ->andWhere(['>=', 'ad_offer.renewed_at', time() - AdOffer::LIVE_DAYS * 24 * 60 * 60])
            ->andWhere(['ad_offer.section' => $this->section])
            ->andWhere("ST_Distance_Sphere(POINT($this->location_lat, $this->location_lon), POINT(ad_offer.location_lat, ad_offer.location_lon)) <= 1000 * (ad_offer.delivery_radius + $this->pickup_radius)");

        $adOfferQueryNoKeywords = clone $adOfferQuery;
        $adOfferQueryNoKeywords = $adOfferQueryNoKeywords
            ->andWhere(['not in', 'ad_offer.id', AdOfferKeyword::find()->select('ad_offer_id')]);

        $adOfferQueryKeywords = clone $adOfferQuery;
        $adOfferQueryKeywords = $adOfferQueryKeywords
                ->joinWith(['keywords' => function ($query) {
                    $query
                        ->joinWith('adSearches')
                        ->andWhere(['ad_search.id' => $this->id]);
                }])
                ->groupBy('ad_offer.id');

        if ($this->getKeywords()->count() > 0) {
            foreach ($adOfferQueryKeywords->all() as $adOffer) {
                $this->link('matches', $adOffer, ['type' => 2]);
            }

            foreach ($adOfferQueryNoKeywords->all() as $adOffer) {
                $this->link('matches', $adOffer, ['type' => 0]);
            }
        } else {
            foreach ($adOfferQueryKeywords->all() as $adOffer) {
                $this->link('matches', $adOffer, ['type' => 1]);
            }

            foreach ($adOfferQueryNoKeywords->all() as $adOffer) {
                $this->link('matches', $adOffer, ['type' => 2]);
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

    public function getGlobalUser()
    {
        return $this->hasOne(GlobalUser::className(), ['id' => 'user_id'])
            ->viaTable('{{%bot_user}}', ['id' => 'user_id']);
    }
}
