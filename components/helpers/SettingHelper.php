<?php

namespace app\components\helpers;

use app\components\Converter;
use yii\base\Component;
use Yii;

class SettingHelper extends Component
{
    /**
     * @param settingValue $settingValue
     *
     * @return string html to display vote percentage
     */
    public static function getVotesHTMl($settingValue = '')
    {
        $threshHold = Yii::$app->settings->website_setting_min_vote_percent_to_apply_change;

        $votes = $settingValue->getVotesPercent();
        $votes = $votes > 1 ? $votes : 1;

        return '<div class="progress" style="height: 20px;">
                    <div class="progress-bar bg-success" role="progressbar" style="width:' . $votes . '%" aria-valuenow="' . $votes . '" aria-valuemin="0" aria-valuemax="100"></div>
                    <div class="progress-bar bg-secondary" role="progressbar" style="width:' . ($threshHold - $votes) . '%" aria-valuenow="' . ($threshHold - $votes) . '" aria-valuemin="0" aria-valuemax="100"></div>
                </div>';
    }
}
