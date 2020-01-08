<?php

namespace app\modules\guest\controllers;

use Yii;
use yii\web\Controller;
use app\models\User;
use app\components\helpers\ReferrerHelper;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

/**
 * Default controller for the `public` module
 */
class DefaultController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionTermsOfUse()
    {
        return $this->render('terms-of-use');
    }

    public function actionPrivacyPolicy()
    {
        return $this->render('privacy-policy');
    }

    /**
     * Store Referrer ID in Cookies for future user
     *
     * @param $id
     *
     * @return Response
     */
    public function actionInvite($id)
    {
        /** @var User $user */
        if (Yii::$app->user->isGuest) {
            $referrer = ReferrerHelper::getReferrerFromCookie();
            if ($user = User::findOne($id)) {
                if ($referrer === null) {
                    // first time
                    ReferrerHelper::addReferrer($user);
                } elseif ($referrer->value != $id) {
                    // change refferer
                    ReferrerHelper::changeReferrer($user);
                }
            }
        }

        return $this->goHome();
    }
}
