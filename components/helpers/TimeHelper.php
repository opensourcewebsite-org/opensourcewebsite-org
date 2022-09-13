<?php

namespace app\components\helpers;

use DateTime;
use Yii;
use yii\base\InvalidArgumentException;
use yii\base\InvalidParamException;

/**
 * General operations with dates and times
 */
class TimeHelper
{
    public static function getTimezoneNames()
    {
        $offsets = [
            -720,
            -660,
            -600,
            -570,
            -540,
            -480,
            -420,
            -360,
            -300,
            -240,
            -210,
            -180,
            -150,
            -120,
            -60,
            0,
            60,
            120,
            180,
            240,
            270,
            300,
            330,
            345,
            360,
            390,
            420,
            480,
            525,
            540,
            570,
            600,
            630,
            660,
            720,
            765,
            780,
            840,
        ];

        $offsetsNames = [];

        foreach ($offsets as $offset) {
            $offsetsNames[$offset] = self::getNameByOffset($offset);
        }

        return $offsetsNames;
    }

    public static function getNameByOffset($offset)
    {
        return 'UTC ' . self::getTimezoneByOffset($offset);
    }

    public static function getTimezoneByOffset($offset)
    {
        return ($offset < 0 ? '-' : '+') . date('H:i', abs($offset) * 60);
    }

    public static function getTimeOfDayByMinutes(int $minutes = 0)
    {
        return date('H:i', $minutes * 60);
    }

    public static function getMinutesByTimeOfDay(string $timeOfDay = null)
    {
        if (!$timeOfDay) {
            return 0;
        }

        if (!$date = DateTime::createFromFormat('H:i|', $timeOfDay)) {
            return null;
        }

        return (int)($date->getTimestamp() / 60);
    }
}
