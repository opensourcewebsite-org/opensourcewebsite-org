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
     * @param int|string|null $id
     *
     * @return Response
     */
    public function actionInvite($id = null)
    {
        /** @var User $user */
        if (Yii::$app->user->isGuest && $id) {
            $referrer = ReferrerHelper::getReferrerFromCookie();

            $user = User::find()
                ->andWhere([
                    'OR',
                    ['id' => $id],
                    ['username' => $id],
                ])
                ->one();

            if ($user) {
                if ($referrer === null) {
                    // add referrer for first time
                    ReferrerHelper::addReferrer($user);
                } elseif ($referrer->value != $user->id) {
                    // change referrer
                    ReferrerHelper::changeReferrer($user);
                }
            }
        }

        return $this->goHome();
    }
}
