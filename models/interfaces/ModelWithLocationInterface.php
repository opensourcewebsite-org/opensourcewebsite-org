<?php

declare(strict_types=1);

namespace app\models\interfaces;

interface ModelWithLocationInterface
{
    public function getLocation(): string;

    public function getTableName(): string;
}
