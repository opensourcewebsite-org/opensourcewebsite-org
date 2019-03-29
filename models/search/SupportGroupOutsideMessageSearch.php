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
 * @property string $member_name
 *
 */
class SupportGroupOutsideMessageSearch extends SupportGroupOutsideMessage
{
    public $language;
    public $support_group_id;
    public $created_by;
    public $member_name;

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
                'support_group_outside_message.created_at',
                'support_group_bot_client_id',
                new Expression('null as `created_by`'),
                new Expression('null as `member_name`'),
            ])
            ->union(
                SupportGroupInsideMessage::find()
                ->select([
                    'message',
                    'support_group_inside_message.created_at',
                    'support_group_bot_client_id',
                    'created_by',
                    new Expression('user.name as `member_name`')
                ])->joinWith('user'),
                true
            );

        $query = self::find()->with('supportGroupBotClient');
        $query->from(['a' => $unionQuery])
            ->orderBy(['created_at'=>SORT_ASC]);

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
            if (!empty($this->member_name)) {
                return $this->member_name;
            }
            return 'Member ' . $this->created_by;
        }

        return $this->supportGroupBotClient->showUserName();
    }
}
