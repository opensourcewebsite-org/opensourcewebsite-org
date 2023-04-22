<?php

namespace app\behaviors;

use Yii;
use yii\db\ActiveRecord;

class JsonBehavior extends ConverterBehavior
{
    protected function convertToStoredFormat($value)
    {
        return json_encode($value);
    }

    protected function convertFromStoredFormat($value)
    {
        $array = json_decode($value, true);
        return $array;
    }
}
