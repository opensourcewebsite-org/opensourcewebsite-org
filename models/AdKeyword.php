<?php

namespace app\models;

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

    public function behaviors()
    {
        return [
            // TimestampBehavior::className(),
        ];
    }

    public function getAdSearches()
    {
        return $this->hasMany(AdSearch::className(), ['id' => 'ad_search_id'])
            ->viaTable('{{%ad_search_keyword}}', ['ad_keyword_id' => 'id']);
    }

    /**
     * @return string
     */
    public function getAdKeywordLabel()
    {
        return $this->keyword;
    }

    public function getAdOffers()
    {
        return $this->hasMany(AdOffer::className(), ['id' => 'ad_offer_id'])
            ->viaTable('{{%ad_offer_keyword}}', ['ad_keyword_id' => 'id']);
    }

    /** @inheritDoc */
    public static function find()
    {
        $query = parent::find();
        $query->orderBy(['keyword' => SORT_ASC]);

        return $query;
    }
}
