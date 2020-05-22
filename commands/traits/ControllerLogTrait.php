<?php

namespace app\commands\traits;

use app\components\CustomConsole;
use app\interfaces\CronChainedInterface;

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
     *
     * @param string $message
     * @param array $ansiFormat {@see CustomConsole::ansiFormat()}
     *
     * @return bool|int
     */
    protected function output(string $message = '', $ansiFormat = [])
    {
        $options = ['logs' => $this->log];

        if ($this instanceof CronChainedInterface) {
            $options['jobName'] = CustomConsole::convertName(get_class($this));
        }
        if (!empty($ansiFormat)) {
            $message = CustomConsole::ansiFormat($message, $ansiFormat);
        }

        return CustomConsole::output($message, $options);
    }

    protected function outputLogState(): void
    {
        if ($this->log) {
            return;
        }

        $this->log = true;
        $this->output(
            'LOGS MUTED (user param --log)',
            [CustomConsole::FG_BLACK, CustomConsole::BG_YELLOW, CustomConsole::BOLD]
        );
        $this->log = false;
    }
}
