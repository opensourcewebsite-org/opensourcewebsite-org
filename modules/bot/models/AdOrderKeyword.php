<?php
namespace app\modules\bot\models;

use yii\db\ActiveRecord;

class AdOrderKeyword extends ActiveRecord
{
    public static function tableName()
    {
        return 'ad_order_keyword';
    }

    public function rules()
    {
        return [
            [['ad_order_id', 'ad_keyword_id'], 'required'],
            [['ad_order_id', 'ad_keyword_id'], 'integer'],
        ];
    }

    public function behaviors()
    {
        return [
            // TimestampBehavior::className(),
        ];
    }
}
