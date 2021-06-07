<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use app\modules\bot\validators\RadiusValidator;
use app\modules\bot\validators\LocationLatValidator;
use app\modules\bot\validators\LocationLonValidator;
use yii\db\ActiveRecord;
use app\models\User as GlobalUser;


class AdOffer extends ActiveRecord
{
    public const STATUS_OFF = 0;
    public const STATUS_ON = 1;

    public const LIVE_DAYS = 30;

    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'ad_offer';
    }

    /**
     * {@inheritdoc}
     */
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
        ];
    }

    public function attributeLabels()
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
            ->where(['!=', AdSearch::tableName() . '.user_id', $this->user_id])
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

    /**
     * {@inheritdoc}
     */
    public function getSectionName()
    {
        return AdSection::getAdOfferName($this->section);
    }
}
