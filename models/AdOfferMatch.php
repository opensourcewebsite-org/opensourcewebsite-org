<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "ad_offer_match".
 *
 * @property int $id
 * @property int $ad_offer_id
 * @property int $ad_search_id
 *
 * @property AdOffer $adOffer
 * @property AdSearch $adSearch
 */
class AdOfferMatch extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'ad_offer_match';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['ad_offer_id', 'ad_search_id'], 'required'],
            [['ad_offer_id', 'ad_search_id'], 'integer'],
            [['ad_offer_id'], 'exist', 'skipOnError' => true, 'targetClass' => AdOffer::className(), 'targetAttribute' => ['ad_offer_id' => 'id']],
            [['ad_search_id'], 'exist', 'skipOnError' => true, 'targetClass' => AdSearch::className(), 'targetAttribute' => ['ad_search_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'ad_offer_id' => 'Ad Offer ID',
            'ad_search_id' => 'Ad Search ID',
        ];
    }

    /**
     * Gets query for [[AdOffer]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAdOffer()
    {
        return $this->hasOne(AdOffer::className(), ['id' => 'ad_offer_id']);
    }

    /**
     * Gets query for [[AdSearch]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAdSearch()
    {
        return $this->hasOne(AdSearch::className(), ['id' => 'ad_search_id']);
    }
}
