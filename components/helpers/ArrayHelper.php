<?php

namespace app\components\helpers;

use Yii;

class ArrayHelper extends \yii\helpers\ArrayHelper
{
    /**
     * @param array $items
     * @param callable $boolFunc
     * @param mixed $defaultValue
     * @return mixed
     */
    public static function findFirst(array $items, callable $boolFunc, $defaultValue = null)
    {
        foreach ($items as $item) {
            if ($boolFunc($item)) {
                return $item;
            }
        }

        return $defaultValue;
    }

    /**
     * @param array $items
     * @param callable $boolFunc
     * @return bool
     */
    public static function isEvery(array $items, callable $boolFunc)
    {
        foreach ($items as $item) {
            if (!$boolFunc($item)) {
                return false;
            }
        }

        return true;
    }
}
