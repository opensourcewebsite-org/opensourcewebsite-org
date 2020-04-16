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
    private $user;

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
            return $this->render('fields/change-email', ['user' => $this->user]);
        }

        $this->user->load(Yii::$app->request->post());
        $this->user->is_authenticated = false;

        if($this->user->email == $this->user->getOldAttribute('email')) {
            return $this->render('fields/change-email', ['user' => $this->user]);
        }

        if ($this->user->save()) {
            $this->user->sendConfirmationEmail($this->user);
            Yii::$app->session->setFlash('success', 'Check your email.');
            return $this->redirect('/account');
        }

        return $this->render('fields/change-email', ['user' => $this->user]);
    }

    public function actionChangeUsername()
    {
        if (!Yii::$app->request->isPost) {
            return $this->render('fields/change-username', ['user' => $this->user]);
        }

        $this->user->load(Yii::$app->request->post());

        if ($this->user->save()) {
            return $this->redirect('/account');
        }

        return $this->render('fields/change-username', ['user' => $this->user]);
    }

    public function actionChangeName()
    {
        if (!Yii::$app->request->isPost) {
            return $this->render('fields/change-name', ['user' => $this->user]);
        }

        $this->user->load(Yii::$app->request->post());

        if ($this->user->save()) {
            return $this->redirect('/account');
        }

        return $this->render('fields/change-name', ['user' => $this->user]);
    }

    public function actionChangeBirthday()
    {

        if (!Yii::$app->request->isPost) {
            return $this->render('fields/change-birthday', ['user' => $this->user]);
        }

        $this->user->birthday = Yii::$app->formatter->asDate(Yii::$app->request->post('birthday'));

        if ($this->user->save()) {
            return $this->redirect('/account');
        }

        return $this->render('fields/change-birthday', ['user' => $this->user]);
    }

    public function actionChangeGender()
    {
        if (!Yii::$app->request->isPost) {
            $genders = Gender::find()->select(['name', 'id'])->indexBy('id')->asArray()->column();
            return $this->render('fields/change-gender', ['user' => $this->user, 'genders' => $genders]);
        }

        $this->user->load(Yii::$app->request->post());

        if ($this->user->save()) {
            return $this->redirect('/account');
        }

        $genders = Gender::find()->select(['name', 'id'])->indexBy('id')->asArray()->column();
        return $this->render('fields/change-gender', ['user' => $this->user, 'genders' => $genders]);
    }

    public function actionChangeTimezone()
    {
        if (!Yii::$app->request->isPost) {
            return $this->render('fields/change-timezone', ['user' => $this->user]);
        }

        $this->user->load(Yii::$app->request->post());

        if ($this->user->save()) {
            return $this->redirect('/account');
        }

        return $this->render('fields/change-timezone', ['user' => $this->user]);
    }

    public function actionChangeCurrency()
    {
        if (!Yii::$app->request->isPost) {
            $currencies = Currency::find()->select(['name', 'id'])->indexBy('id')->asArray()->column();
            return $this->render('fields/change-currency', ['user' => $this->user, 'currencies' => $currencies]);
        }

        $this->user->load(Yii::$app->request->post());

        if ($this->user->save()) {
            return $this->redirect('/account');
        }

        $currencies = Currency::find()->select(['name', 'id'])->indexBy('id')->asArray()->column();
        return $this->render('fields/change-currency', ['user' => $this->user, 'currencies' => $currencies]);
    }

    public function actionChangeSexuality()
    {
        if (!Yii::$app->request->isPost) {
            $sexualities = Sexuality::find()->select(['name', 'id'])->indexBy('id')->asArray()->column();
            return $this->render('fields/change-sexuality', ['user' => $this->user, 'sexualities' =>
                $sexualities]);
        }

        $this->user->load(Yii::$app->request->post());

        if ($this->user->save()) {
            return $this->redirect('/account');
        }

        $sexualities = Sexuality::find()->select(['name', 'id'])->indexBy('id')->asArray()->column();
        return $this->render('fields/change-sexuality', ['user' => $this->user, 'sexualities' => $sexualities]);
    }
}
