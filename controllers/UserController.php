<?php

namespace app\controllers;

use app\components\helpers\ReferrerHelper;
use app\models\EditProfileForm;
use app\models\Gender;
use app\models\Currency;
use app\models\Sexuality;
use app\models\UserStatistic;
use Yii;
use app\models\User;
use app\models\UserMoqupFollow;
use yii\web\Controller;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;

class UserController extends Controller
{
    public $user;

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

    public function init()
    {
        $this->user = Yii::$app->user->identity;
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
        if (!Yii::$app->request->isPost) {
            return $this->render('fields/change-email', ['emailModel' => $this->user]);
        }

        $this->user->load(Yii::$app->request->post());
        $this->user->is_authenticated = false;

        if ($this->user->validate() && $this->user->save()) {
            $this->user->sendConfirmationEmail($this->user);
            Yii::$app->session->setFlash('success', 'Check your email.');
            return $this->redirect('/account');
        }

        return $this->render('fields/change-email', ['emailModel' => $this->user]);
    }

    public function actionChangeUsername()
    {
        if (!Yii::$app->request->isPost) {
            return $this->render('fields/change-username', ['usernameModel' => $this->user]);
        }

        $this->user->load(Yii::$app->request->post());

        if ($this->user->validate() && $this->user->save()) {
            Yii::$app->session->setFlash('success', 'Username changed.');
            return $this->redirect('/account');
        } else {
            return $this->render('fields/change-username', ['usernameModel' => $this->user]);
        }
    }

    public function actionChangeName()
    {
        if (!Yii::$app->request->isPost) {
            return $this->render('fields/change-name', ['nameModel' => $this->user]);
        }

        $this->user->load(Yii::$app->request->post());

        if ($this->user->validate() && $this->user->save()) {
            Yii::$app->session->setFlash('success', 'Name changed.');
            return $this->redirect('/account');
        } else {
            return $this->render('fields/change-name', ['nameModel' => $this->user]);
        }
    }

    public function actionChangeBirthday()
    {

        if (!Yii::$app->request->isPost) {
            return $this->render('fields/change-birthday', ['birthdayModel' => $this->user]);
        }

        $this->user->load(Yii::$app->request->post());
        $this->user->birthday = Yii::$app->formatter->asDate($this->user->birthday);
        if ($this->user->validate() && $this->user->save()) {
            Yii::$app->session->setFlash('success', 'Birthday changed.');
            return $this->redirect('/account');
        } else {
            return $this->render('fields/change-birthday', ['birthdayModel' => $this->user]);
        }
    }

    public function actionChangeGender()
    {
        if (!Yii::$app->request->isPost) {
            $genders = Gender::find()->select(['name', 'id'])->indexBy('id')->asArray()->column();
            return $this->render('fields/change-gender', ['genderModel' => $this->user, 'genders' => $genders]);
        }

        $postData = Yii::$app->request->post('User');
        $gender_id = $postData['gender'];
        $this->user->gender_id = $gender_id;

        if ($this->user->validate() && $this->user->save()) {
            Yii::$app->session->setFlash('success', 'Gender changed.');
            return $this->redirect('/account');
        } else {
            $genders = Gender::find()->select(['name', 'id'])->indexBy('id')->asArray()->column();
            return $this->render('fields/change-gender', ['genderModel' => $this->user, 'genders' => $genders]);
        }
    }

    public function actionChangeTimezone()
    {
        if (!Yii::$app->request->isPost) {
            return $this->render('fields/change-timezone', ['timezoneModel' => $this->user]);
        }

        $this->user->load(Yii::$app->request->post());

        if ($this->user->validate() && $this->user->save()) {
            Yii::$app->session->setFlash('success', 'Timezone changed.');
            return $this->redirect('/account');
        } else {
            return $this->render('fields/change-timezone', ['timezoneModel' => $this->user]);
        }
    }

    public function actionChangeCurrency()
    {
        if (!Yii::$app->request->isPost) {
            $currencies = Currency::find()->select(['name', 'id'])->indexBy('id')->asArray()->column();
            return $this->render('fields/change-currency', ['currencyModel' => $this->user, 'currencies' =>
                $currencies]);
        }

        $postData = Yii::$app->request->post('User');
        $currency_id = $postData['currency'];
        $this->user->currency_id = $currency_id;

        if ($this->user->validate() && $this->user->save()) {
            Yii::$app->session->setFlash('success', 'Currency changed.');
            return $this->redirect('/account');
        } else {
            $currencies = Currency::find()->select(['name', 'id'])->indexBy('id')->asArray()->column();
            return $this->render('fields/change-currency', ['currencyModel' => $this->user, 'currencies' =>
                $currencies]);
        }
    }

    public function actionChangeSexuality()
    {
        if (!Yii::$app->request->isPost) {
            $sexualities = Sexuality::find()->select(['name', 'id'])->indexBy('id')->asArray()->column();
            return $this->render('fields/change-sexuality', ['sexualityModel' => $this->user, 'sexualities' =>
                $sexualities]);
        }

        $postData = Yii::$app->request->post('User');
        $sexuality_id = $postData['sexuality'];
        $this->user->sexuality_id = $sexuality_id;

        if ($this->user->validate() && $this->user->save()) {
            Yii::$app->session->setFlash('success', 'Sexuality changed.');
            return $this->redirect('/account');
        } else {
            $sexualities = Sexuality::find()->select(['name', 'id'])->indexBy('id')->asArray()->column();
            return $this->render('fields/change-sexuality', ['sexualityModel' => $this->user, 'sexualities' =>
                $sexualities]);
        }
    }
}
