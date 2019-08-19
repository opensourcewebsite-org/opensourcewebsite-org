<?php

namespace app\components\helpers;

use yii\base\Component;
use app\models\SupportGroupExchangeRateCommand;

class SupportGroupHelper extends Component
{

    /**
     * @param $type Buying/Selling
     */
    public static function getExchangeRateCommandType($type)
    {
        $title = 'Buying Commands';
        if ((int) $type === SupportGroupExchangeRateCommand::TYPE_SELLING_COMMAND) {
            $title = 'Selling Commands';
        }

        return $title;
    }
}
