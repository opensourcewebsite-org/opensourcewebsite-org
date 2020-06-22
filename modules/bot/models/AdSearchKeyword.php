<?php

namespace app\modules\bot\models;

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

    public function behaviors()
    {
        return [
            // TimestampBehavior::className(),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAdKeywordRelation()
    {
        return $this->hasOne(AdKeyword::class, ['id' => 'ad_keyword_id']);
    }

    /**
     * @return string
     */
    public function getAdSearchKeywordLabel()
    {
        return $this->adKeywordRelation->keyword;
    }
}
