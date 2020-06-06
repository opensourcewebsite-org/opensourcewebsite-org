<?php

namespace app\modules\apiTesting\services;

use app\modules\apiTesting\models\ApiTestTeam;
use Yii;

/**
 * Class ProjectTeamService
 *
 * @package app\modules\apiTesting\services
 */
class ProjectTeamService extends \yii\base\Component
{
    public function inviteUserToProject(ApiTestTeam $team)
    {
        $team->status = $team::STATUS_INVITED;
        $team->invited_by = Yii::$app->user->id;
        return $team->save();
    }

    public function acceptInvite(ApiTestTeam $team)
    {
        if ($team->user_id != Yii::$app->user->id) {
            return false;
        }

        $team->status = $team::STATUS_ACCEPTED;
        return $team->save();
    }

    public function declineInvite(ApiTestTeam $team)
    {
        if ($team->user_id != Yii::$app->user->id) {
            return false;
        }

        $team->status = $team::STATUS_DECLINED;
        return $team->save();
    }

    public function removeUserFromTeam(ApiTestTeam $team)
    {
        return $team->delete();
    }

    public function checkInviterAccess(ApiTestTeam $team)
    {
    }

    public function checkUserAccess(ApiTestTeam $team)
    {
        if ($team->user_id == Yii::$app->user->id) {
            return true;
        }
    }

    public function leaveTeam(ApiTestTeam $team)
    {
        $this->removeUserFromTeam($team);
    }
}
