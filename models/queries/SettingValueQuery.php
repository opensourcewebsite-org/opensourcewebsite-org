<?php

declare(strict_types=1);

namespace app\models\queries;

use app\interfaces\UserRelation\ByDebtInterface;
use app\interfaces\UserRelation\ByOwnerInterface;
use app\models\SettingValue;
use app\models\queries\traits\RandomTrait;
use yii\db\ActiveQuery;

/**
 * This is the ActiveQuery class for [[SettingValue]].
 *
 * @see SettingValue
 *
 * @method SettingValue[] all()
 * @method null|array|SettingValue one()
 */
class SettingValueQuery extends ActiveQuery
{
    use RandomTrait;
}
