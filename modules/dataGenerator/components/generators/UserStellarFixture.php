<?php

namespace app\modules\dataGenerator\components\generators;

use Yii;
use app\models\UserStellar;
use app\models\User;
use yii\db\ActiveRecord;
use yii\helpers\Console;
use yii\validators\NumberValidator;

class UserStellarFixture extends ARGenerator
{
    /**
     * @return UserStellar|null
     * @throws ARGeneratorException
     */
    protected function factoryModel(): ?ActiveRecord
    {
        if (!$user = $this->getRandomUser()) {
            return null;
        }

        if ($user->userStellar) {
            return null;
        }

        $model = new UserStellar();

        $model->user_id = $user->id;
        $model->public_key = 'GB3WYAPNTUTKAZYCRK2F2KUURWI43OMCTI6EVTPM5DLCSAMHVGUBZVL2';
        $model->confirmed_at = time();

        return $model;
    }
}
