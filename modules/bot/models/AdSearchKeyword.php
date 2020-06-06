<?php
namespace app\modules\bot\models;

use yii\db\ActiveRecord;

class AdSearchKeyword extends ActiveRecord
{
    public static function tableName()
    {
        return 'ad_search_keyword';
    }

    public function rules()
    {
        return [
            [['ad_search_id', 'keyword_id'], 'required'],
            [['ad_search_id', 'keyword_id'], 'integer'],
        ];
    }

    public function behaviors()
    {
        return [
            // TimestampBehavior::className(),
        ];
    }
}
