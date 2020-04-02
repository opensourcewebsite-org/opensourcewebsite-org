<?php

namespace app\controllers;

use app\components\helpers\ReferrerHelper;
use app\models\EditProfileForm;
use app\models\Rating;
use Yii;
use app\models\Moqup;
use app\models\User;
use app\models\UserMoqupFollow;
use yii\web\Controller;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;

class UserController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['edit-profile', 'display', 'follow-moqup', 'unfollow-moqup', 'follow-user', 'unfollow-user'],
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

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
     * Lists all User models.
     * @return mixed
     */
    public function actionDisplay()
    {
        $confirmed_users = User::findAll(['is_authenticated' => true]);

        return $this->render('display', [
            'confirmed_users' => count($confirmed_users),
        ]);
    }

    /**
     * Add a moqup to the list followed by the user
     * @return boolean If the relation was saved
     */
    public function actionFollowMoqup($id)
    {
        $exists = UserMoqupFollow::findOne(['moqup_id' => $id, 'user_id' => Yii::$app->user->identity->id]);

        $withoutErrors = false;

        if ($exists == null) {
            $relation = new UserMoqupFollow([
                'moqup_id' => $id,
                'user_id' => Yii::$app->user->identity->id
            ]);

            if ($relation->save()) {
               $withoutErrors = true;
            }
        }

        echo $withoutErrors;
        exit;
    }

    /**
     * Remove a moqup from the list followed by the user
     * @return boolean If the relation was removed
     */
    public function actionUnfollowMoqup($id)
    {
        $model = UserMoqupFollow::findOne(['moqup_id' => $id, 'user_id' => Yii::$app->user->identity->id]);

        $withoutErrors = false;

        if ($model != null && $model->delete()) {
            $withoutErrors = true;
        }

        echo $withoutErrors;
        exit;
    }

    public function actionProfile()
    {
        $userId = \Yii::$app->request->get('id');
        if (!$userId) {
            throw new NotFoundHttpException();
        }

        /** @var User $user */
        $user = User::find()->where(['or', ['id' => $userId], ['username' => $userId]])->one();
        if (!$user) {
            throw new NotFoundHttpException();
        }

        $currentUser = Yii::$app->getUser();

        if ($currentUser->getIsGuest()) {
            $referrer = ReferrerHelper::getReferrerFromCookie();
            if ($referrer === null) {
                ReferrerHelper::addReferrer($user);
            } elseif ($referrer->value != $user->id) {
                ReferrerHelper::changeReferrer($user);
            }

            $currentUser->loginRequired();
            return;
        }

        if ($userId == $user->id && $user->username) {
            $this->redirect(['user/profile', 'id' => $user->username]);
            return;
        }

        return $this->render('profile', ['model' => $user]);
    }

    public function actionChangeEmail()
    {
        if(!Yii::$app->request->isPost) {
            return false;
        }
        $user = Yii::$app->user->identity;
        $postData = Yii::$app->request->post('EditProfileForm');
        $user->email = $postData['field'];
        $user->is_authenticated = false;
        if($user->save()) {
            $user->sendConfirmationEmail($user);
        }
        return $this->redirect('/account');
    }

    public function actionChangeUsername()
    {
        if(!Yii::$app->request->isPost) {
            return false;
        }
        $user = Yii::$app->user->identity;
        $postData = Yii::$app->request->post('EditProfileForm');
        $user->username = $postData['field'];
        $user->save();

        return $this->redirect('/account');
    }

    public function actionChangeName()
    {
        if(!Yii::$app->request->isPost) {
            return false;
        }
        $user = Yii::$app->user->identity;
        $postData = Yii::$app->request->post('EditProfileForm');
        $user->name = $postData['field'];
        $user->save();

        return $this->redirect('/account');
    }

    public function actionChangeBirthday()
    {
        if(!Yii::$app->request->isPost) {
            return false;
        }
        $user = Yii::$app->user->identity;
        $postData = Yii::$app->request->post('EditProfileForm');
        $user->birthday = date('Y-m-d', strtotime($postData['field']));
        $user->save();

        return $this->redirect('/account');
    }

    public function actionChangeGender()
    {
        if(!Yii::$app->request->isPost) {
            return false;
        }
        $user = Yii::$app->user->identity;
        $postData = Yii::$app->request->post('EditProfileForm');
        $user->gender_id = $postData['field'];
        $user->save();

        return $this->redirect('/account');
    }

    public function actionChangeTimezone()
    {
        if(!Yii::$app->request->isPost) {
            return false;
        }
        $user = Yii::$app->user->identity;
        $postData = Yii::$app->request->post('EditProfileForm');
        $user->timezone = $postData['field'];
        $user->save();

        return $this->redirect('/account');
    }
}
