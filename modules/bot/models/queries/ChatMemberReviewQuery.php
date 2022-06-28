<?php

declare(strict_types=1);

namespace app\modules\bot\models\queries;

use app\models\User as GlobalUser;
use app\modules\bot\models\ChatMemberReview;
use Yii;
use yii\db\ActiveQuery;

/**
 * Class ChatMemberReviewQuery
 *
 * @package app\modules\bot\models\queriess
 */
class ChatMemberReviewQuery extends ActiveQuery
{
    public function active(): self
    {
        return $this->andWhere([
            '>', ChatMemberReview::tableName() . '.status', 0,
        ]);
    }

    public function orderByRank(): self
    {
        return $this->joinWith('globalUser')
            ->orderBy([
                GlobalUser::tableName() . '.rating' => SORT_DESC,
                GlobalUser::tableName() . '.created_at' => SORT_ASC,
            ]);
    }
}
