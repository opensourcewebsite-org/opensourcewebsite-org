<?php

namespace app\models;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * Class AdPhoto
 * @package app\models
 *
 * @property int $id
 * @property int $ad_offer_id
 * @property string $file_id
 *
 * @property AdOffer $offer
 */
class AdPhoto extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%ad_offer_photo}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['ad_offer_id', 'file_id'], 'required'],
            [['file_id'], 'string'],
            [['ad_offer_id'], 'integer'],
        ];
    }

    public function getAdOffer(): ActiveQuery
    {
        return $this->hasOne(AdOffer::class, ['id' => 'ad_offer_id']);
    }
}
