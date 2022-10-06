<?php

declare(strict_types=1);

namespace app\models\interfaces;

use yii\db\ActiveQuery;

interface MatchesInterface
{
    public function getMatches(): ActiveQuery;

    public function getMatchModels(): ActiveQuery;

    public function getCounterMatches(): ActiveQuery;

    public function getCounterMatchModels(): ActiveQuery;

    public function clearMatches();
}
