<?php

namespace app\components\debt;

use yii\base\Exception;

class OutdatedObjectException  extends Exception
{
    /**
     * @return string the user-friendly name of this exception
     */
    public function getName()
    {
        return 'Outdated Object Exception';
    }
}
