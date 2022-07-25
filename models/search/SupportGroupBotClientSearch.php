<?php

namespace app\models\search;

use Yii;
use app\models\SupportGroupBotClient;
use yii\data\ActiveDataProvider;

/**
 * Class SupportGroupSearch
 * @package app\models\search
 *
 * @property string $language
 * @property int $support_group_id
 *
 */
class SupportGroupBotClientSearch extends SupportGroupBotClient
{
    public $language;
    public $support_group_id;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [];
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {

        $query = self::find()
            ->joinWith([
                'supportGroupClient',
                'supportGroupBot'
            ]);

        $dataProvider = new ActiveDataProvider([
            'query'      => $query,
            'sort'       => [
                'defaultOrder' => [
                    'last_message_at' => SORT_DESC,
                ],
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'support_group_client.language_code' => $this->language,
            'support_group_client.support_group_id' => $this->support_group_id,
        ]);

        return $dataProvider;
    }
}
