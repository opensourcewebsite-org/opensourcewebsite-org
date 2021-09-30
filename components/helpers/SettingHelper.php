<?php

namespace app\components\helpers;

use app\components\Converter;
use yii\base\Component;

class SettingHelper extends Component
{
    /**
     * @param settingValue $settingValue
     *
     * @return string html to display vote percentage
     */
    public static function getVotesHTMl($settingValue = '')
    {
        $votes = $settingValue->getVotesPercent();

        $voteFormat = Converter::formatNumber($votes, 2) . ' %';

        return '<div class="progress" style="height: 20px;">
                    <div class="progress-bar bg-success" role="progressbar" style="width: ' . $votes . '%" aria-valuenow="' . $votes . '" aria-valuemin="0" aria-valuemax="100">' . $voteFormat . '</div>
                </div>';
    }
}
