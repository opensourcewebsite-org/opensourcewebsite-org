<?php

namespace app\components\helpers;

use app\components\Converter;
use yii\base\Component;

class SettingHelper extends Component
{
    /**
     * @param settingValues object of SettingValues model
     * @return string html to display vote percentage
     */
    public static function getVoteHTMl($settingValues = '', $vote = 0)
    {
        if ($settingValues != null) {
            $vote = $settingValues->getUserVotesPercent(false);
        }

        $vote = floor($vote);

        $voteFormat = Converter::formatNumber($vote, 0) . '%';

        return '<div class="progress" style="height: 20px;">
                    <div class="progress-bar bg-success" role="progressbar" style="width: ' . $vote . '%" aria-valuenow="' . $vote . '" aria-valuemin="0" aria-valuemax="100">' . $voteFormat . '</div>
                </div>';
    }
}
