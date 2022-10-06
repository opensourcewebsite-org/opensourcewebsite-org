<?php

namespace app\models;

use app\models\queries\AdKeywordQuery;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * Class AdKeyword
 *
 * @property int $id
 * @property string $keyword
 */
class AdKeyword extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'ad_keyword';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
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

    public function getLabel(): string
    {
        return $this->keyword;
    }

    public function getAdSearches(): ActiveQuery
    {
        return $this->hasMany(AdSearch::class, ['id' => 'ad_search_id'])
            ->viaTable('{{%ad_search_keyword}}', ['ad_keyword_id' => 'id']);
    }

    public function getAdOffers(): ActiveQuery
    {
        return $this->hasMany(AdOffer::class, ['id' => 'ad_offer_id'])
            ->viaTable('{{%ad_offer_keyword}}', ['ad_keyword_id' => 'id']);
    }
}
