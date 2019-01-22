<?php

namespace app\models\search;

use app\models\SupportGroupOutsideMessage;
use Yii;
use yii\data\ActiveDataProvider;

/**
 * Class SupportGroupOutsideMessageSearch
 * @package app\models\search
 *
 * @property string $language
 * @property int $support_group_id
 *
 */
class SupportGroupOutsideMessageSearch extends SupportGroupOutsideMessage
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

        $query = self::find()->with('supportGroupBotClient');

        $dataProvider = new ActiveDataProvider([
            'query'      => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'support_group_bot_client_id' => $this->support_group_bot_client_id,
        ]);

        return $dataProvider;
    }
}
