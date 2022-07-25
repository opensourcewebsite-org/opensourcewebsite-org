<?php

namespace app\components\helpers;

use Yii;
use yii\base\InvalidArgumentException;
use yii\base\InvalidParamException;

/**
 * General operations with dates and times
 */
class TimeHelper
{
    public static function timezonesList()
    {
        $timeOffsets = [
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
            840];

        $timeOffsetsNames = [];
        foreach ($timeOffsets as $timeOffset) {
            $timeOffsetsNames[$timeOffset] = self::getNameByOffset($timeOffset);
        }

        return $timeOffsetsNames;
    }

    public static function getNameByOffset($timeOffset)
    {
        return 'UTC ' . ($timeOffset < 0 ? '-' : '+') . date('H:i', abs($timeOffset) * 60);
    }
}
