<?php

declare(strict_types=1);

namespace app\modules\bot\models\queries;

use app\models\User as GlobalUser;
use app\modules\bot\models\Chat;
use Yii;
use yii\db\ActiveQuery;

/**
 * Class ChatQuery
 *
 * @package app\modules\bot\models\queriess
 */
class ChatQuery extends ActiveQuery
{
    public function private(): self
    {
        return $this->andWhere([
            Chat::tableName() . '.type' => Chat::TYPE_PRIVATE,
        ]);
    }

    public function group(): self
    {
        return $this->andWhere([
            'or',
            [Chat::tableName() . '.type' => Chat::TYPE_GROUP],
            [Chat::tableName() . '.type' => Chat::TYPE_SUPERGROUP],
        ]);
    }

    public function channel(): self
    {
        return $this->andWhere([
            Chat::tableName() . '.type' => Chat::TYPE_CHANNEL,
        ]);
    }

    public function hasUsername(): self
    {
        return $this->andWhere([
            'not', [Chat::tableName() . '.username' => null],
        ]);
    }

    public function orderByCreatorRank(): self
    {
        return $this->joinWith('chatMemberCreator.user.globalUser')
            ->orderBy([
                GlobalUser::tableName() . '.rating' => SORT_DESC,
                GlobalUser::tableName() . '.created_at' => SORT_ASC,
                Chat::tableName() . '.chat_id' => SORT_DESC,
            ]);
    }
}
