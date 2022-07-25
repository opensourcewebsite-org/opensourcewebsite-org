<?php

namespace app\components\helpers;

use app\components\Converter;
use app\models\UserIssueVote;
use yii\base\Component;

class IssuesHelper extends Component
{
    /**
     * @param Issue object of Issue model
     * @return string html to display vote percentage
     */
    public static function getVoteHTMl($issue)
    {
        $votes = $issue->getUserVotesPercent(false);

        $v1 = $votes[UserIssueVote::YES];
        $v3 = $votes[UserIssueVote::NO];

        $vote1 = !empty($v1) ? Converter::formatNumber($v1, 0) . '%' : '';
        $vote3 = !empty($v3) ? Converter::formatNumber($v3, 0) . '%' : '';

        return '<div class="progress" style="height: 20px;">
            <div class="progress-bar bg-success" role="progressbar" style="width: ' . $v1 . '%" aria-valuenow="' . $v1 . '" aria-valuemin="0" aria-valuemax="100">' . $vote1 . '</div>
            <div class="progress-bar bg-danger" role="progressbar" style="width: ' . $v3 . '%" aria-valuenow="' . $v3 . '" aria-valuemin="0" aria-valuemax="100">' . $vote3 . '</div>
        </div>';
    }
}
