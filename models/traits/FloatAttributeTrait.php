<?php

namespace app\models\traits;

use app\helpers\Number;
use yii\db\ColumnSchema;
use yii\db\Schema;

trait FloatAttributeTrait
{
    public static function isAttributeFloat($name): bool
    {
        /** @var ColumnSchema $column */
        $column = self::getTableSchema()->getColumn($name);

        return in_array($column->type, [Schema::TYPE_DECIMAL, Schema::TYPE_FLOAT, Schema::TYPE_DOUBLE], true);
    }

    public static function getAttributeFloatScale($name): int
    {
        return self::getTableSchema()->getColumn($name)->scale;
    }

    private function isAttributeFloatChanged($name): bool
    {
        return !Number::isFloatEqual(
            $this->getAttribute($name),
            $this->getOldAttribute($name),
            self::getAttributeFloatScale($name)
        );
    }
}
