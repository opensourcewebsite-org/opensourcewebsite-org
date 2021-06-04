<?php

namespace app\models;

use app\models\queries\AdKeywordQuery;
use yii\db\ActiveRecord;

/**
 * Class AdKeyword
 *
 * @package app\modules\bot\models
 */
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
            [['keyword'], 'unique'],
        ];
    }

    public static function find(): AdKeywordQuery
    {
        return new AdKeywordQuery(get_called_class());
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->keyword;
    }

    public function getAdSearches()
    {
        return $this->hasMany(AdSearch::className(), ['id' => 'ad_search_id'])
            ->viaTable('{{%ad_search_keyword}}', ['ad_keyword_id' => 'id']);
    }

    public function getAdOffers()
    {
        return $this->hasMany(AdOffer::className(), ['id' => 'ad_offer_id'])
            ->viaTable('{{%ad_offer_keyword}}', ['ad_keyword_id' => 'id']);
    }
}
