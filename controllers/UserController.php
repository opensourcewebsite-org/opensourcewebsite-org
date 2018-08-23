<?php
namespace app\controllers;

use Yii;
use app\models\User;
use yii\web\Controller;
use yii\helpers\ArrayHelper;
use yii\authclient\AuthAction;

/**
 * Class UserController
 */
class UserController extends Controller
{

    /**
     * @return array
     */
    public function actions()
    {
        return [
            'oauth' => [
                'class' => AuthAction::class,
                'successCallback' => [$this, 'successOAuthCallback']
            ],
        ];
    }

    /**
     * @param $client \yii\authclient\BaseClient
     * @return bool
     * @throws Exception
     */
    public function successOAuthCallback($client)
    {
        $attributes = $client->getUserAttributes();
        $user = User::find()->where([
                'oauth_client' => $client->getName(),
                'oauth_client_user_id' => ArrayHelper::getValue($attributes, 'id')
            ])->one();
        if (empty($user)) {
            $email = ArrayHelper::getValue($attributes, 'email');
            if ($email === null) {
                $email = ArrayHelper::getValue($attributes, ['emails', 0, 'value']);
            }
            if (!empty(User::findByEmail($email))) {
                Yii::$app->session->setFlash('danger', [
                    'body' => Yii::t('app', 'Email {email} already exists.', [
                        'email' => $email
                    ])
                ]);
                return false;
            } else {
                $user = new User();
                $user->username = $email;
                $user->email = $email;
                $user->oauth_client = $client->getName();
                $user->oauth_client_user_id = ArrayHelper::getValue($attributes, 'id');
                $user->status = User::STATUS_ACTIVE;
                $password = Yii::$app->security->generateRandomString(8);
                $user->setPassword($password);
                $user->generateAuthKey();
                if ($user->save()) {
                    Yii::$app->session->setFlash('success', [
                        'body' => Yii::t('app', 'Welcome to {app-name}.', [
                            'app-name' => Yii::$app->name
                        ])
                    ]);
                } else {
                    Yii::$app->session->setFlash('danger', [
                        'body' => Yii::t('app', 'Error while oauth process.')
                    ]);
                }
            }
        }
        if (Yii::$app->user->login($user, 3600 * 24 * 30)) {
            return true;
        }
        throw new Exception('OAuth error');
    }
}
