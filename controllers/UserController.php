<?php

namespace app\controllers;

use app\components\helpers\ReferrerHelper;
use app\models\EditProfileForm;
use app\models\profile\Birthday;
use app\models\profile\Email;
use app\models\profile\Gender as GenderModel;
use app\models\Gender;
use app\models\profile\Currency as CurrencyModel;
use app\models\Currency;
use app\models\profile\Sexuality as SexualityModel;
use app\models\profile\Timezone;
use app\models\Sexuality;
use app\models\profile\Name;
use app\models\profile\Username;
use app\models\UserStatistic;
use Yii;
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
     *
     * @param string $type
     * @return mixed
     */
    public function actionDisplay($type = 'age')
    {
        $usersCount = User::find()->count();

        $userStatistics = new UserStatistic();
        $dataProvider = $userStatistics->getDataProvider($type);

        return $this->render('display', [
            'usersCount' => $usersCount,
            'dataProvider' => $dataProvider,
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
        $emailModel = new Email;

        if (!Yii::$app->request->isPost) {
            return $this->render('fields/change-email', ['emailModel' => $emailModel]);
        }

        $emailModel->load(Yii::$app->request->post());
        $user = Yii::$app->user->identity;
        $user->email = $emailModel->email;
        $user->is_authenticated = false;

        if ($user->validate() && $emailModel->validate() && $user->save()) {
            $user->sendConfirmationEmail($user);
            Yii::$app->session->setFlash('success', 'Check your email.');
            return $this->redirect('/account');
        } else {
            Yii::$app->session->setFlash('warning', 'Email is already in use!');
            return $this->render('fields/change-email', ['emailModel' => $emailModel]);
        }
    }

    public function actionChangeUsername()
    {
        $usernameModel = new Username;

        if (!Yii::$app->request->isPost) {
            return $this->render('fields/change-username', ['usernameModel' => $usernameModel]);
        }

        $usernameModel->load(Yii::$app->request->post());
        $user = Yii::$app->user->identity;
        $user->username = $usernameModel->username;
        if ($user->validate() && $usernameModel->validate() && $user->save()) {
            Yii::$app->session->setFlash('success', 'Username changed.');
            return $this->redirect('/account');
        } else {
            return $this->render('fields/change-username', ['usernameModel' => $usernameModel]);
        }
    }

    public function actionChangeName()
    {
        $nameModel = new Name;

        if (!Yii::$app->request->isPost) {
            return $this->render('fields/change-name', ['nameModel' => $nameModel]);
        }

        $nameModel->load(Yii::$app->request->post());
        $user = Yii::$app->user->identity;
        $user->name = $nameModel->name;
        if ($user->validate() && $nameModel->validate() && $user->save()) {
            Yii::$app->session->setFlash('success', 'Name changed.');
            return $this->redirect('/account');
        } else {
            return $this->render('fields/change-name', ['nameModel' => $nameModel]);
        }
    }

    public function actionChangeBirthday()
    {
        $birthdayModel = new Birthday;

        if (!Yii::$app->request->isPost) {
            return $this->render('fields/change-birthday', ['birthdayModel' => $birthdayModel]);
        }

        $birthdayModel->load(Yii::$app->request->post());
        $user = Yii::$app->user->identity;
        $user->birthday = date('Y-m-d', strtotime($birthdayModel->birthday));
        if ($user->validate() && $birthdayModel->validate() && $user->save()) {
            Yii::$app->session->setFlash('success', 'Birthday changed.');
            return $this->redirect('/account');
        } else {
            return $this->render('fields/change-birthday', ['birthdayModel' => $birthdayModel]);
        }
    }

    public function actionChangeGender()
    {
        $genderModel = new GenderModel;

        if (!Yii::$app->request->isPost) {
            $genders = Gender::find()->select(['name', 'id'])->indexBy('id')->asArray()->column();
            return $this->render('fields/change-gender', ['genderModel' => $genderModel, 'genders' => $genders]);
        }

        $genderModel->load(Yii::$app->request->post());
        $user = Yii::$app->user->identity;
        $user->gender_id = $genderModel->gender;
        if ($user->validate() && $genderModel->validate() && $user->save()) {
            Yii::$app->session->setFlash('success', 'Gender changed.');
            return $this->redirect('/account');
        } else {
            $genders = Gender::find()->select(['name', 'id'])->indexBy('id')->asArray()->column();
            return $this->render('fields/change-gender', ['genderModel' => $genderModel, 'genders' => $genders]);
        }
    }

    public function actionChangeTimezone()
    {
        $timezoneModel = new Timezone;

        if (!Yii::$app->request->isPost) {
            return $this->render('fields/change-timezone', ['timezoneModel' => $timezoneModel]);
        }

        $timezoneModel->load(Yii::$app->request->post());
        $user = Yii::$app->user->identity;
        $user->timezone = $timezoneModel->timezone;
        if ($user->validate() && $timezoneModel->validate() && $user->save()) {
            Yii::$app->session->setFlash('success', 'Timezone changed.');
            return $this->redirect('/account');
        } else {
            return $this->render('fields/change-timezone', ['timezoneModel' => $timezoneModel]);
        }
    }

    public function actionChangeCurrency()
    {
        $currencyModel = new CurrencyModel;

        if (!Yii::$app->request->isPost) {
            $currencies = Currency::find()->select(['name', 'id'])->indexBy('id')->asArray()->column();
            return $this->render('fields/change-currency', ['currencyModel' => $currencyModel, 'currencies' =>
                $currencies]);
        }

        $currencyModel->load(Yii::$app->request->post());
        $user = Yii::$app->user->identity;
        $user->currency_id = $currencyModel->currency;
        if ($user->validate() && $currencyModel->validate() && $user->save()) {
            Yii::$app->session->setFlash('success', 'Currency changed.');
            return $this->redirect('/account');
        } else {
            $currencies = Currency::find()->select(['name', 'id'])->indexBy('id')->asArray()->column();
            return $this->render('fields/change-currency', ['currencyModel' => $currencyModel, 'currencies' =>
                $currencies]);
        }
    }

    public function actionChangeSexuality()
    {
        $sexualityModel = new SexualityModel;

        if (!Yii::$app->request->isPost) {
            $sexualities = Sexuality::find()->select(['name', 'id'])->indexBy('id')->asArray()->column();
            return $this->render('fields/change-sexuality', ['sexualityModel' => $sexualityModel, 'sexualities' =>
                $sexualities]);
        }

        $sexualityModel->load(Yii::$app->request->post());
        $user = Yii::$app->user->identity;
        $user->sexuality_id = $sexualityModel->sexuality;
        if ($user->validate() && $sexualityModel->validate() && $user->save()) {
            Yii::$app->session->setFlash('success', 'Sexuality changed.');
            return $this->redirect('/account');
        } else {
            $sexualities = Sexuality::find()->select(['name', 'id'])->indexBy('id')->asArray()->column();
            return $this->render('fields/change-sexuality', ['sexualityModel' => $sexualityModel, 'sexualities' =>
                $sexualities]);
        }
    }
}
