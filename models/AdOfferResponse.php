<?php
declare(strict_types=1);

namespace app\models;

use yii\db\ActiveRecord;

class AdOfferResponse extends ActiveRecord
{
    public static function tableName(): string
    {
        return "ad_offer_response";
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
