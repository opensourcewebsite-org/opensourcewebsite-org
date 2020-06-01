<?php
namespace app\modules\bot\models;

use yii\db\ActiveRecord;

class AdCategory extends ActiveRecord
{
    public static function tableName()
    {
        return 'bot_ad_category';
    }

    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name'], 'string'],
        ];
    }

    public function behaviors()
    {
        return [
            // TimestampBehavior::className(),
        ];
    }
}
