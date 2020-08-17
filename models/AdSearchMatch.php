<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "ad_search_match".
 *
 * @property int $id
 * @property int $ad_search_id
 * @property int $ad_offer_id
 *
 * @property AdOffer $adOffer
 * @property AdSearch $adSearch
 */
class AdSearchMatch extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'ad_search_match';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['ad_search_id', 'ad_offer_id'], 'required'],
            [['ad_search_id', 'ad_offer_id'], 'integer'],
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
            'ad_search_id' => 'Ad Search ID',
            'ad_offer_id' => 'Ad Offer ID',
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
