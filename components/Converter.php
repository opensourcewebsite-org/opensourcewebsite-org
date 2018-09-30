<?php

namespace app\components;

use yii;
use \yii\base\BaseObject;

class Converter extends BaseObject
{
    /**
     * @param integer $bytes The mount in bytes
     * @return integer The size in MB
     */
    public static function byteToMega($bytes)
    {
        return number_format($bytes / 1024 / 1024, '2', ',', '.');
    }
}