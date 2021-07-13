<?php

namespace app\components;

use Yii;
use yii\base\BaseObject;

class Converter extends BaseObject
{
    /**
     * @param integer|double $number The number to be formatted
     * @param integer|double $decimalPlace The number of digits after decimal seperator
     * @return The formatted number
     */
    public static function formatNumber($number, $decimalPlace = 2)
    {
        return number_format($number, $decimalPlace, ',', '.');
    }

    /**
     * @param integer $timestamp The timestamp need to be formatted
     * @return The formatted date
     */
    public static function formatDate($timestamp)
    {
        return Yii::$app->formatter->format($timestamp, 'relativeTime');
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
     * @param bool $format whether to format perentage value or not
     * @return string The number in percentage
     */
    public static function percentage($value, $total, $format = true)
    {
        $result = 0;
        if ($total > 0) {
            $result = ($value * 100) / $total;
        }
        if ($format) {
            $result = self::formatNumber($result);
        }

        return $result;
    }
}
