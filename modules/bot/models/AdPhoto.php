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
            [['ad_order_id', 'file_id'], 'required'],
            [['file_id'], 'string'],
            [['ad_order_id'], 'integer'],
        ];
    }

    public function behaviors()
    {
        return [
            // TimestampBehavior::className(),
        ];
    }
}
