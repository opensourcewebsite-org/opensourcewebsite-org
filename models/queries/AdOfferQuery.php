<?php
declare(strict_types=1);
namespace app\models\queries;

use app\models\AdOffer;
use yii\db\ActiveQuery;

/**
 * Class AdOfferQuery
 *
 * @package app\models\queries
 */
class AdOfferQuery extends ActiveQuery
{
    public function live(): self
    {
        return $this->andWhere([AdOffer::tableName() . '.status' => AdOffer::STATUS_ON])
            ->joinWith('user')
            ->andWhere(['>=', 'user.last_activity_at', time() - AdOffer::LIVE_DAYS * 24 * 60 * 60]);
    }
}
