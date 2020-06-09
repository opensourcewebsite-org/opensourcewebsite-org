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

    public function getAdOffers()
    {
        return $this->hasMany(AdOffer::className(), ['id' => 'ad_offer_id'])
            ->viaTable('{{%ad_offer_keyword}}', ['ad_keyword_id' => 'id']);
    }
}
