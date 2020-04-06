<?php

namespace app\modules\dataGenerator\components\generators;

use Throwable;
use yii\base\Exception;
use yii\base\Model;
use yii\helpers\VarDumper;

class ARGeneratorException extends Exception
{
    /**
     * @param string|Model $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct($message = '', $code = 0, Throwable $previous = null)
    {
        if ($message instanceof Model) {
            $message = VarDumper::dumpAsString($message->errors);
        }
        parent::__construct($message, $code, $previous);
    }
}
