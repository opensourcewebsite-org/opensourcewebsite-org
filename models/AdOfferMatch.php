<?php

namespace app\models;


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

    public static function tableName(): string
    {
        return 'ad_offer_match';
    }

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

    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'ad_offer_id' => 'Ad Offer ID',
            'ad_search_id' => 'Ad Search ID',
        ];
    }

    public function getAdOffer(): ActiveQuery
    {
        return $this->hasOne(AdOffer::class, ['id' => 'ad_offer_id']);
    }

    public function getAdSearch(): ActiveQuery
    {
        return $this->hasOne(AdSearch::class, ['id' => 'ad_search_id']);
    }
}
