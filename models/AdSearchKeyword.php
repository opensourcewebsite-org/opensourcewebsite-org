<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * Class AdSearchKeyword
 *
 * @package app\modules\bot\models
 */
class AdSearchKeyword extends ActiveRecord
{
    public static function tableName()
    {
        return 'ad_search_keyword';
    }

    public function rules()
    {
        return [
            [['ad_search_id', 'ad_keyword_id'], 'required'],
            [['ad_search_id', 'ad_keyword_id'], 'integer'],
        ];
    }
}
