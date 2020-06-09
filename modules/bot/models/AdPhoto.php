<?php
namespace app\modules\bot\models;

use yii\db\ActiveRecord;

class AdPhoto extends ActiveRecord
{
    public static function tableName()
    {
        return 'ad_photo';
    }

    public function rules()
    {
        return [
            [['ad_offer_id', 'file_id'], 'required'],
            [['file_id'], 'string'],
            [['ad_offer_id'], 'integer'],
        ];
    }

    public function behaviors()
    {
        return [
            // TimestampBehavior::className(),
        ];
    }
}
