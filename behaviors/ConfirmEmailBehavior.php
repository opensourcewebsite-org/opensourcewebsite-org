<?php

namespace app\behaviors;

use Yii;
use app\models\User;
use yii\base\Behavior;
use yii\base\Controller;

/**
 * Class ConfirmEmailBehavior
 */
class ConfirmEmailBehavior extends Behavior
{

    /**
     * @return array
     */
    public function events()
    {
        return [
            Controller::EVENT_BEFORE_ACTION => 'beforeAction'
        ];
    }

    public function beforeAction()
    {
        if (Yii::$app->user->isGuest || $this->allowedUrlRequest()) {
            return true;
        }

        $user = User::findOne(Yii::$app->user->id);
        if ($user->is_email_confirmed) {
            return true;
        }

        return Yii::$app->response->redirect(['site/account']);
    }

    public function allowedUrlRequest()
    {
        $route = Yii::$app->controller->getroute();

        return $route === 'site/account' || $route === 'site/resend-confirmation-email' || $route === 'site/confirm';
    }
}
