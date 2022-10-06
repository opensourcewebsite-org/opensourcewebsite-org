<?php

namespace app\models;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * Class AdOfferKeyword
 *
 * @property int $id
 * @property int $ad_offer_id
 * @property int $ad_keyword_id
 *
 * @property AdOffer $offer
 * @property AdKeyword $keyword
 */
class AdOfferKeyword extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%ad_offer_keyword}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['ad_offer_id', 'ad_keyword_id'], 'required'],
            [['ad_offer_id', 'ad_keyword_id'], 'integer'],
        ];
    }

    public function getOffer(): ActiveQuery
    {
        return $this->hasOne(AdOffer::class, ['id' => 'ad_offer_id']);
    }

    public function getKeyword(): ActiveQuery
    {
        return $this->hasOne(AdKeyword::class, ['id' => 'ad_keyword_id']);
    }
}
