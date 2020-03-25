<?php

namespace app\commands\traits;

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
}