<?php

declare(strict_types=1);

namespace app\models\interfaces;

use yii\db\ActiveQuery;

interface MatchesInterface
{
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMatches(): ActiveQuery;

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCounterMatches(): ActiveQuery;
}
