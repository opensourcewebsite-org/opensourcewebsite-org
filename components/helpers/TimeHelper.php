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
        $zones_array = $sorted = [];
        $timestamp = time();
        foreach (timezone_identifiers_list() as $key => $zone) {
            date_default_timezone_set($zone);
            $zones_array[str_replace(':', '', date('P', $timestamp))][$zone] = ' (UTC ' . date('P', $timestamp) . ') ' . $zone;
        }
        ksort($zones_array);
        foreach ($zones_array as $zones) {
            foreach ($zones as $key => $zone) {
                $sorted[$key] = $zone;
            }
        }

        return $sorted;
    }
}
