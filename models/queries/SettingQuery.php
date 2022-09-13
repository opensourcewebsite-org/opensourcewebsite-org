<?php

declare(strict_types=1);

namespace app\models\queries;

use app\interfaces\UserRelation\ByDebtInterface;
use app\interfaces\UserRelation\ByOwnerInterface;
use app\models\Setting;
use app\models\queries\traits\RandomTrait;
use yii\db\ActiveQuery;

/**
 * This is the ActiveQuery class for [[Setting]].
 *
 * @see Setting
 *
 * @method Setting[] all()
 * @method null|array|Setting one()
 */
class SettingQuery extends ActiveQuery
{
    use RandomTrait;
}
