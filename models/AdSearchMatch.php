<?php

namespace app\models;

use app\models\queries\AdSearchMatchQuery;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 *
 * @property int $id
 * @property int $ad_search_id
 * @property int $ad_offer_id
 *
 * @property AdOffer $adOffer
 * @property AdSearch $adSearch
 */
class AdSearchMatch extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%ad_search_match}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['ad_search_id', 'ad_offer_id'], 'required'],
            [['ad_search_id', 'ad_offer_id'], 'integer'],
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
            'ad_search_id' => 'Ad Search ID',
            'ad_offer_id' => 'Ad Offer ID',
        ];
    }

    public static function find(): AdSearchMatchQuery
    {
        return new AdSearchMatchQuery(get_called_class());
    }

    public function getAdSearch(): ActiveQuery
    {
        return $this->hasOne(AdSearch::class, ['id' => 'ad_search_id']);
    }

    public function getAdOffer(): ActiveQuery
    {
        return $this->hasOne(AdOffer::class, ['id' => 'ad_offer_id']);
    }

    public function isNew()
    {
        return !AdOfferResponse::find()
            ->andWhere([
                'user_id' => $this->adSearch->user_id,
                'ad_offer_id' => $this->ad_offer_id,
            ])
            ->andWhere([
                'is not', 'viewed_at', null,
            ])
            ->exists();
    }
}
