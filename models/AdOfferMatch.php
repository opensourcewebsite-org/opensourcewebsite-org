<?php

namespace app\models;

use app\models\queries\AdOfferMatchQuery;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 *
 * @property int $id
 * @property int $ad_offer_id
 * @property int $ad_search_id
 *
 * @property AdOffer $adOffer
 * @property AdSearch $adSearch
 */
class AdOfferMatch extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%ad_offer_match}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['ad_offer_id', 'ad_search_id'], 'required'],
            [['ad_offer_id', 'ad_search_id'], 'integer'],
            [
                ['ad_offer_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => AdOffer::class,
                'targetAttribute' => ['ad_offer_id' => 'id']
            ],
            [
                ['ad_search_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => AdSearch::class,
                'targetAttribute' => ['ad_search_id' => 'id']
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'ad_offer_id' => 'Ad Offer ID',
            'ad_search_id' => 'Ad Search ID',
        ];
    }

    public static function find(): AdOfferMatchQuery
    {
        return new AdOfferMatchQuery(get_called_class());
    }

    public function getAdOffer(): ActiveQuery
    {
        return $this->hasOne(AdOffer::class, ['id' => 'ad_offer_id']);
    }

    public function getAdSearch(): ActiveQuery
    {
        return $this->hasOne(AdSearch::class, ['id' => 'ad_search_id']);
    }

    public function isNew()
    {
        return !AdSearchResponse::find()
            ->andWhere([
                'user_id' => $this->adOffer->user_id,
                'ad_search_id' => $this->ad_search_id,
            ])
            ->andWhere([
                'is not', 'viewed_at', null,
            ])
            ->exists();
    }
}
