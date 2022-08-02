<?php

declare(strict_types=1);

namespace app\modules\bot\models\queries;

use app\models\User as GlobalUser;
use Yii;
use yii\db\ActiveQuery;

/**
 * Class UserQuery
 *
 * @package app\modules\bot\models\queriess
 */
class UserQuery extends ActiveQuery
{
    public function human(): self
    {
        return $this->andWhere([
            'is_bot' => 0,
        ]);
    }
}
