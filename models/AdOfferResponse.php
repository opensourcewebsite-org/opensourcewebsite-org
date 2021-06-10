<?php
declare(strict_types=1);

namespace app\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

class AdOfferResponse extends ActiveRecord
{
    public static function tableName(): string
    {
        return "ad_offer_response";
    }

    public function behaviors(): array
    {
        return [
            'timestamp' => [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'viewed_at',
                'updatedAtAttribute' => false,
            ],
        ];
    }

    public function rules(): array
    {
        return [
            [
                ['user_id','ad_offer_id'],
                'required'
            ],
            [
                ['user_id','ad_offer_id', 'viewed_at'],
                'integer'
            ]
        ];
    }
}
