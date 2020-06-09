<?php
namespace app\modules\bot\models;

use yii\db\ActiveRecord;

class AdOfferKeyword extends ActiveRecord
{
    public static function tableName()
    {
        return 'ad_offer_keyword';
    }

    public function rules()
    {
        return [
            [['ad_offer_id', 'ad_keyword_id'], 'required'],
            [['ad_offer_id', 'ad_keyword_id'], 'integer'],
        ];
    }

    public function behaviors()
    {
        return [
            // TimestampBehavior::className(),
        ];
    }
}
