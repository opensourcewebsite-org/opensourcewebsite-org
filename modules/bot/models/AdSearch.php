<?php

namespace app\modules\bot\models;

use app\behaviors\CreatedByBehavior;
use yii\behaviors\TimestampBehavior;
use app\components\helpers\ArrayHelper;
use app\models\Currency;
use app\modules\bot\validators\RadiusValidator;
use yii\db\ActiveRecord;
use app\models\User as GlobalUser;

/**
 * Class AdSearch
 *
 * @package app\modules\bot\models
 */
class AdSearch extends ActiveRecord
{
    public const STATUS_OFF = 0;
    public const STATUS_ON = 1;

    public const LIVE_DAYS = 30;

    public static function tableName()
    {
        return 'ad_search';
    }

    /**
     * @return array|array[]
     */
    public function rules()
    {
        return [
            [
                [
                    'title',
                    'pickup_radius',
                    'location_lat',
                    'location_lon',
                    'status',
                ],
                'required',
            ],
            ['pickup_radius', RadiusValidator::class],
            [['title', 'description', 'location_lat', 'location_lon'], 'string'],
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
            [['max_price'], 'number'],
        ];
    }

    /** @inheritDoc */
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
            ->viaTable('{{%ad_search_keyword}}', ['ad_search_id' => 'id']);
    }

    public function isActive()
    {
        return $this->status == self::STATUS_ON;
    }

    public function getMatches()
    {
        return $this->hasMany(AdOffer::className(), ['id' => 'ad_offer_id'])
            ->viaTable(
                '{{%ad_match}}',
                ['ad_search_id' => 'id'],
                function ($query) {
                    $query->andWhere(['or', ['type' => 1], ['type' => 2]]);
                }
            );
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
            ->joinWith('globalUser')
            ->andWhere(['>=', 'user.last_activity_at', time() - AdOffer::LIVE_DAYS * 24 * 60 * 60])
            ->andWhere(['ad_offer.section' => $this->section])
            ->andWhere(
                "ST_Distance_Sphere(POINT($this->location_lon, $this->location_lat), POINT(ad_offer.location_lon, ad_offer.location_lat)) <= 1000 * (ad_offer.delivery_radius + $this->pickup_radius)"
            );

        $adOfferQueryNoKeywords = clone $adOfferQuery;
        $adOfferQueryNoKeywords = $adOfferQueryNoKeywords
            ->andWhere(['not in', 'ad_offer.id', AdOfferKeyword::find()->select('ad_offer_id')]);

        $adOfferQueryKeywords = clone $adOfferQuery;
        $adOfferQueryKeywords = $adOfferQueryKeywords
            ->joinWith(
                [
                    'keywords' => function ($query) {
                        $query
                            ->joinWith('adSearches')
                            ->andWhere(['ad_search.id' => $this->id]);
                    },
                ]
            )
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

            $this->setAttributes(
                [
                    'processed_at' => null,
                ]
            );

            $this->save();
        }
    }

    /** @inheritDoc */
    public function attributeLabels()
    {
        return ArrayHelper::merge(
            parent::attributeLabels(),
            [
                'pickup_radius' => 'Pickup radius',
                'max_price' => 'Max price',
            ]
        );
    }

    public function getGlobalUser()
    {
        return $this->hasOne(GlobalUser::className(), ['id' => 'user_id'])
            ->viaTable('{{%bot_user}}', ['id' => 'user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCurrencyRelation()
    {
        return $this->hasOne(Currency::class, ['id' => 'currency_id']);
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
