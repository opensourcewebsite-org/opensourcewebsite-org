<?php

namespace app\components;

use Yii;
use app\models\User;

/**
 * Class Controller
 */
class Controller extends \yii\web\Controller
{
    /**
     * @return User
     */
    protected function getUser(): User
    {
        return Yii::$app->user->identity;
    }
}
