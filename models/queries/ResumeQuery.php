<?php


namespace app\models\queries;

use app\models\Resume;
use yii\db\ActiveQuery;

/**
 * Class ResumeQuery
 *
 * @package app\models\queries
 */
class ResumeQuery extends ActiveQuery
{
    /**
     * @return ResumeQuery
     */
    public function active()
    {
        return $this->andWhere([Resume::tableName() . '.status' => Resume::STATUS_ON])
            ->andWhere(['>=', Resume::tableName() . '.renewed_at', time() - Resume::LIVE_DAYS * 24 * 60 * 60]);
    }
}
