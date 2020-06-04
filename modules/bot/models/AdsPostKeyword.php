<?php
namespace app\modules\bot\models;

use yii\db\ActiveRecord;

class AdsPostKeyword extends ActiveRecord
{
    public static function tableName()
    {
        return 'ads_post_keyword';
    }

    public function rules()
    {
        return [
            [['ads_post_id', 'keyword_id'], 'required'],
            [['ads_post_id', 'keyword_id'], 'integer'],
        ];
    }

    public function behaviors()
    {
        return [
            // TimestampBehavior::className(),
        ];
    }
}
