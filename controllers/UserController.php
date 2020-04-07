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
        if (!Yii::$app->request->isPost) {
            return false;
        }
        $user = Yii::$app->user->identity;
        $postData = Yii::$app->request->post('Email');
        $user->email = $postData['email'];
        $user->is_authenticated = false;
        if ($user->validate() && $user->save()) {
            $user->sendConfirmationEmail($user);
        }
        return $this->redirect('/account');
    }

    public function actionChangeUsername()
    {
        if (!Yii::$app->request->isPost) {
            return false;
        }
        $user = Yii::$app->user->identity;
        $postData = Yii::$app->request->post('Username');
        $user->username = $postData['username'];
        if($user->validate()) {
            $user->save();
        }
        return $this->redirect('/account');
    }

    public function actionChangeName()
    {
        if (!Yii::$app->request->isPost) {
            return false;
        }
        $user = Yii::$app->user->identity;
        $postData = Yii::$app->request->post('Name');
        $user->name = $postData['name'];
        if($user->validate()) {
            $user->save();
        }

        return $this->redirect('/account');
    }

    public function actionChangeBirthday()
    {
        if (!Yii::$app->request->isPost) {
            return false;
        }
        $user = Yii::$app->user->identity;
        $postData = Yii::$app->request->post('Birthday');
        $user->birthday = date('Y-m-d', strtotime($postData['birthday']));
        if($user->validate()) {
            $user->save();
        }

        return $this->redirect('/account');
    }

    public function actionChangeGender()
    {
        if (!Yii::$app->request->isPost) {
            return false;
        }
        $user = Yii::$app->user->identity;
        $postData = Yii::$app->request->post('Gender');
        $user->gender_id = $postData['gender'];
        if($user->validate()) {
            $user->save();
        }

        return $this->redirect('/account');
    }

    public function actionChangeTimezone()
    {
        if (!Yii::$app->request->isPost) {
            return false;
        }
        $user = Yii::$app->user->identity;
        $postData = Yii::$app->request->post('Timezone');
        $user->timezone = $postData['timezone'];
        if($user->validate()) {
            $user->save();
        }

        return $this->redirect('/account');
    }

    public function actionChangeCurrency()
    {
        if (!Yii::$app->request->isPost) {
            return false;
        }
        $user = Yii::$app->user->identity;
        $postData = Yii::$app->request->post('Currency');
        $user->currency_id = $postData['currency'];
        if($user->validate()) {
            $user->save();
        }

        return $this->redirect('/account');
    }

    public function actionChangeSexuality()
    {
        if (!Yii::$app->request->isPost) {
            return false;
        }
        $user = Yii::$app->user->identity;
        $postData = Yii::$app->request->post('Sexuality');
        $user->sexuality_id = $postData['sexuality'];
        if($user->validate()) {
            $user->save();
        }

        return $this->redirect('/account');
    }
}
