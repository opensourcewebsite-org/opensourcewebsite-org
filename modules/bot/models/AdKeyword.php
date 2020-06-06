<?php
namespace app\modules\bot\models;

use yii\db\ActiveRecord;

class AdKeyword extends ActiveRecord
{
    public static function tableName()
    {
        return 'ad_keyword';
    }

    public function rules()
    {
        return [
            [['keyword'], 'required'],
            [['keyword'], 'string'],
        ];
    }

    public function behaviors()
    {
        return [
            // TimestampBehavior::className(),
        ];
    }
}
