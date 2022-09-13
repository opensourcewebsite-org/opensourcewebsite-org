<?php

declare(strict_types=1);

namespace app\models\search;

use app\models\SupportGroup;
use Yii;
use yii\data\ActiveDataProvider;

/**
 * Class SupportGroupSearch
 *
 * @package app\models\search
 */
class SupportGroupSearch extends SupportGroup
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [];
    }

    /**
     * @return ActiveDataProvider
     */
    public function search()
    {
        $query = self::find()
            ->where([
                self::tableName() . '.user_id' => $this->user_id,
            ])
            ->orWhere([
                '{{%support_group_member}}.user_id' => $this->user_id,
            ])
            ->joinWith('supportGroupMembers');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        if (!$this->validate()) {
            return $dataProvider;
        }

        return $dataProvider;
    }
}
