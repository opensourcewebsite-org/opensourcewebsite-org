<?php

declare(strict_types=1);

namespace app\repositories;

use Yii;
use yii\web\NotFoundHttpException;

use app\models\SupportGroup;

class SupportGroupRepository
{
    public function findSupportGroup(int $id): SupportGroup
    {
        if (($model = SupportGroup::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    public function accessFindSupportGroup(int $id): SupportGroup
    {
        $supportGroup = SupportGroup::tableName();
        $model = SupportGroup::find()
            ->where([
                $supportGroup . '.user_id' => Yii::$app->user->id,
            ])
            ->orWhere([
                '{{%support_group_member}}.user_id' => Yii::$app->user->id,
            ])
            ->andWhere([$supportGroup . '.id' => intval($id)])
            ->joinWith('supportGroupMembers')
            ->one();

        if (!$model) {
            throw new NotFoundHttpException;
        }

        return $model;
    }
}
    