<?php

declare(strict_types=1);

namespace app\models\queries;

use app\models\queries\traits\RandomTrait;
use yii\db\ActiveQuery;

class GenderQuery extends ActiveQuery
{
    use RandomTrait;
}
