<?php

namespace app\models\queries\builders;

use yii\db\ActiveQuery;
use yii\db\Expression;

interface ConditionExpressionBuilderInterface {

    public function build(): Expression;
}
