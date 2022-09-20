<?php

declare(strict_types=1);

namespace app\modules\bot\models\queries;

use app\models\User as GlobalUser;
use Yii;
use yii\db\ActiveQuery;

/**
 * Class ChatMarketplacePostQuery
 *
 * @package app\modules\bot\models\queriess
 */
class ChatMarketplacePostQuery extends ActiveQuery
{
    /**
     * @return self
     */
    public function orderByRank(): self
    {
        return $this->joinWith('chatMember.user.globalUser')
            ->orderBy([
                GlobalUser::tableName() . '.rating' => SORT_DESC,
                GlobalUser::tableName() . '.created_at' => SORT_ASC,
            ]);
    }
}
