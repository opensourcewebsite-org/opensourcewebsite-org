<?php
namespace app\components;

use yii\helpers\Console;

class CustomConsole extends Console
{
    /**
     * @param string $message
     * @param bool $checkLog
     * @return int|bool number of bytes printed or false on error.
     */
    public static function output($message = '', $checkLog = false)
    {
        if(!$checkLog){
            return false;    
        }
        
        return static::stdout($message . PHP_EOL);
    }
}