<?php

declare(strict_types=1);

namespace app\models\events\interfaces;

interface ViewedByUserInterface
{
    public function markViewedByUserId(int $userId);
}
