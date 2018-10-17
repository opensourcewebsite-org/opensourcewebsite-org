<?php

namespace app\components;

use yii;
use \yii\base\BaseObject;

class Converter extends BaseObject
{
    /**
     * @param integer|double $number The number to be formatted
     * @return The formatted number
     */
    public static function formatNumber($number)
    {
        return number_format($number, 2, ',', '.');
    }

    /**
     * @param integer $bytes The mount in bytes
     * @return integer The size in MB
     */
    public static function byteToMega($bytes)
    {
        $megas = $bytes / 1024 / 1024;
        return $megas;
    }

    /**
     * @param integer|double $value The number to be converted
     * @param integer|double $total The amount that represents the 100%
     * @return string The number in percentage
     */
    public function percentage($value, $total)
    {
        $result = ($value * 100) / $total;
        return self::formatNumber($result);
    }
}