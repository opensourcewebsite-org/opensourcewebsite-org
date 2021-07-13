<?php

namespace app\components;

use app\models\CronJob;
use app\models\CronJobLog;
use yii\helpers\Console;
use yii\helpers\ArrayHelper;

/**
 * Class CustomConsole
 * @package app\components
 */
class CustomConsole extends Console
{
    /**
     * @param string $message
     * @param array $options
     *
     * - `logs`: bool activate log posting into console
     * - `jobName`: string Name of controller to insert in DB
     *
     * @return int|bool number of bytes printed or false on error.
     */
    public static function output($message = '', $options = [])
    {
        $options = ArrayHelper::merge(
            [
                'logs' => false,
                'jobName' => null,
            ],
            $options
        );

        if ($options['jobName']) {
            if ($job = CronJob::findOne(['name' => $options['jobName']])) {
                $cronJobModel = new CronJobLog();
                $cronJobModel->log($message, $job->id);
            }
        }

        if (!($options['logs'])) {
            return false;
        }

        return static::stdout($message . PHP_EOL);
    }

    /**
     * @param string $name
     * @return string
     */
    public static function convertName($name)
    {
        $start = mb_strrpos($name, '\\') + 1;

        return mb_substr($name, $start, -10);
    }
}
