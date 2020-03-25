<?php

namespace app\commands\traits;

use app\components\CustomConsole;
use app\interfaces\ICronChained;

/**
 * Optionally, to extend controller options add next:
 *
 * ```php
 * public function options($actionID)
 * {
 *     return $this->optionsAppendLog(parent::options($actionID));
 * }
 * ```
 */
trait ControllerLogTrait
{
    public $log = false;

    /**
     * @param string[] $options result from {@see \yii\console\Controller::options()}
     *
     * @return array
     */
    public function optionsAppendLog($options)
    {
        return array_merge($options, [
            'log',
        ]);
    }

    /**
     * Docs are here: {@see CustomConsole::output()}
     */
    protected function output(string $message)
    {
        $options = ['logs' => $this->log];

        if ($this instanceof ICronChained) {
            $options['jobName'] = CustomConsole::convertName(get_class($this));
        }

        return CustomConsole::output($message, $options);
    }
}