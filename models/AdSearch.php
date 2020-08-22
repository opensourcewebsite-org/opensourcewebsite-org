<?php

namespace app\models;

use app\behaviors\CreatedByBehavior;
use yii\behaviors\TimestampBehavior;
use app\components\helpers\ArrayHelper;
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

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'ad_search';
    }

    /**
     * {@inheritdoc}
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
            [
                'pickup_radius',
                RadiusValidator::class,
            ],
            [
                [
                    'title',
                    'description',
                ],
                'string',
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
                [
                    'max_price',
                    'location_lat',
                    'location_lon',
                ],
                'double',
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
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

    /**
     * {@inheritdoc}
     */
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

    /**
     * @return \yii\db\ActiveQuery
     * @throws \yii\base\InvalidConfigException
     */
    public function getMatches()
    {
        return $this->hasMany(AdOffer::className(), ['id' => 'ad_offer_id'])
            ->viaTable('{{%ad_search_match}}', ['ad_search_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     * @throws \yii\base\InvalidConfigException
     */
    public function getCounterMatches()
    {
        return $this->hasMany(AdOffer::className(), ['id' => 'ad_offer_id'])
            ->viaTable('{{%ad_offer_match}}', ['ad_search_id' => 'id']);
    }

    public function updateMatches()
    {
        $this->unlinkAll('matches', true);
        $this->unlinkAll('counterMatches', true);

        $adOfferQuery = AdOffer::find()
            ->where(['!=', AdOffer::tableName() . '.user_id', $this->user_id])
            ->andWhere([AdOffer::tableName() . '.status' => AdOffer::STATUS_ON])
            ->joinWith('globalUser')
            ->andWhere(['>=', 'user.last_activity_at', time() - AdOffer::LIVE_DAYS * 24 * 60 * 60])
            ->andWhere([AdOffer::tableName() . '.section' => $this->section])
            ->andWhere(
                "ST_Distance_Sphere(POINT($this->location_lon, $this->location_lat), POINT(ad_offer.location_lon, ad_offer.location_lat)) <= 1000 * (ad_offer.delivery_radius + $this->pickup_radius)"
            );

        $adOfferQueryNoKeywords = clone $adOfferQuery;
        $adOfferQueryNoKeywords = $adOfferQueryNoKeywords
            ->andWhere(['not in', AdOffer::tableName() . '.id', AdOfferKeyword::find()->select('ad_offer_id')]);

        $adOfferQueryKeywords = clone $adOfferQuery;
        $adOfferQueryKeywords = $adOfferQueryKeywords
            ->joinWith(
                [
                    'keywords' => function ($query) {
                        $query
                            ->joinWith('adSearches')
                            ->andWhere([AdSearch::tableName() . '.id' => $this->id]);
                    },
                ]
            )
            ->groupBy(AdOffer::tableName() . '.id');

        if ($this->getKeywords()->count() > 0) {
            foreach ($adOfferQueryKeywords->all() as $adOffer) {
                $this->link('matches', $adOffer);
                $this->link('counterMatches', $adOffer);
            }

            foreach ($adOfferQueryNoKeywords->all() as $adOffer) {
                $this->link('matches', $adOffer);
            }
        } else {
            foreach ($adOfferQueryKeywords->all() as $adOffer) {
                $this->link('counterMatches', $adOffer);
            }

            foreach ($adOfferQueryNoKeywords->all() as $adOffer) {
                $this->link('matches', $adOffer);
                $this->link('counterMatches', $adOffer);
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

    public function getGlobalUser()
    {
        return $this->hasOne(GlobalUser::className(), ['id' => 'user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCurrency()
    {
        return $this->hasOne(Currency::class, ['id' => 'currency_id']);
    }

    /**
     * {@inheritdoc}
     */
    public function afterSave($insert, $changedAttributes)
    {
        if (isset($changedAttributes['status']) && $this->status == self::STATUS_OFF) {
            $this->clearMatches();
        }

        parent::afterSave($insert, $changedAttributes);
    }
}
