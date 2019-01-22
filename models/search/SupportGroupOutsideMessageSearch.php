<?php

namespace app\models\search;

use app\models\SupportGroupInsideMessage;
use app\models\SupportGroupOutsideMessage;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\Expression;
use yii\db\Query;

/**
 * Class SupportGroupOutsideMessageSearch
 * @package app\models\search
 *
 * @property string $language
 * @property int $support_group_id
 * @property int $created_by
 *
 */
class SupportGroupOutsideMessageSearch extends SupportGroupOutsideMessage
{
    public $language;
    public $support_group_id;
    public $created_by;

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

        $unionQuery = self::find()
            ->select([
                'message',
                'created_at',
                'support_group_bot_client_id',
                new Expression('null as `created_by`')
            ])
            ->union(
                SupportGroupInsideMessage::find()
                ->select([
                    'message',
                    'created_at',
                    'support_group_bot_client_id',
                    'created_by'
                ]),
                true
            )->orderBy('created_at ASC');

        $query = self::find()->with('supportGroupBotClient');
        $query->from(['a' => $unionQuery])->orderBy(['created_at'=>SORT_ASC]);

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

    /**
     * string
     */
    public function showChatName()
    {
        if ($this->created_by) {
            return 'Member ' . $this->created_by;
        }

        return 'Client ' . $this->supportGroupBotClient->provider_bot_user_id;
    }
}
