<?php

namespace app\models;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * Class AdSearchKeyword
 *
 * @property int $id
 * @property int $ad_search_id
 * @property int $ad_keyword_id
 *
 * @property AdSearch $adSearch
 * @property AdKeyword $adKeyword
 */
class AdSearchKeyword extends ActiveRecord
{
    public static function tableName(): string
    {
        return 'ad_search_keyword';
    }

    public function rules(): array
    {
        return [
            [['ad_search_id', 'ad_keyword_id'], 'required'],
            [['ad_search_id', 'ad_keyword_id'], 'integer'],
        ];
    }

    public function getAdSearch(): ActiveQuery
    {
        return $this->hasOne(AdSearch::class, ['id' => 'ad_search_id']);
    }

    public function getAdKeyword(): ActiveQuery
    {
        return $this->hasOne(AdKeyword::class, ['id' => 'ad_keyword_id']);
    }
}
