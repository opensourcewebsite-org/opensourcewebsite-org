<?php
declare(strict_types=1);
namespace app\models\queries;

use app\models\AdSearch;
use yii\db\ActiveQuery;

/**
 * Class AdSearchQuery
 *
 * @package app\models\queries
 */
class AdSearchQuery extends ActiveQuery
{
    public function live(): self
    {
        return $this->andWhere([AdSearch::tableName() . '.status' => AdSearch::STATUS_ON])
            ->joinWith('user')
            ->andWhere(['>=', 'user.last_activity_at', time() - AdSearch::LIVE_DAYS * 24 * 60 * 60]);
    }
}
