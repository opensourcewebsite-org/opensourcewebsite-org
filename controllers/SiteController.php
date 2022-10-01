<?php

namespace app\controllers;

use app\components\Converter;
use app\models\Contact;
use app\models\Currency;
use app\models\forms\LoginForm;
use app\models\forms\RequestResetPasswordForm;
use app\models\forms\ResetPasswordForm;
use app\models\forms\SignupForm;
use app\models\Gender;
use app\models\Language;
use app\models\Rating;
use app\models\Sexuality;
use app\models\User;
use app\models\UserEmail;
use Yii;
use yii\base\InvalidParamException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\BadRequestHttpException;
use yii\web\Controller;

class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => [
                    'logout',
                ],
                'rules' => [
                    [
                        'actions' => [
                            'logout',
                        ],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function beforeAction($action)
    {
        if (in_array($action->id, ['hook'])) {
            $this->enableCsrfValidation = false;
        }

        if (Yii::$app->user->isGuest) {
            $this->layout = 'adminlte-guest';
        } else {
            $this->layout = 'adminlte-user';
        }

        return parent::beforeAction($action);
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'app\actions\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Logs in a user.
     *
     * @return mixed
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->redirect(['/dashboard']);
        }

        $model = new LoginForm();

        if (Yii::$app->request->isPost && ($postData = Yii::$app->request->post())) {
            if ($model->load($postData) && $model->login()) {
                return $this->redirect(['/dashboard']);
            }
        }

        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Login by auth link with hash.
     *
     * @param int $id user id
     * @param int $time
     * @param string $hash
     *
     * @return mixed
     * @throws BadRequestHttpException
     */
    public function actionLoginByAuthLink(int $id, int $time, string $hash)
    {
        if (!Yii::$app->user->isGuest) {
            return $this->redirect(['/dashboard']);
        }

        // TODO add captcha
        if ((($time + User::AUTH_LINK_LIFETIME) > time()) && ($user = User::findById($id))) {
            if ($user->authByHash($time, $hash) && Yii::$app->user->login($user, 30 * 24 * 60 * 60)) {
                return $this->redirect(['/dashboard']);
            } else {
                Yii::$app->session->setFlash('warning', 'There was an error validating your request, please try again.');

                return $this->redirect(['site/login']);
            }
        } else {
            return $this->render('expired-auth-link');
        }
    }

    /**
     * Logs out the current user.
     *
     * @return mixed
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->redirect(['site/login']);
    }

    /**
     * Signs user up.
     *
     * @return mixed
     */
    public function actionSignup()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->redirect(['/dashboard']);
        }

        $model = new SignupForm();

        if (Yii::$app->request->isPost && ($postData = Yii::$app->request->post())) {
            if ($model->load($postData) && $model->signup()) {
                return $this->redirect(['/dashboard']);
            }
        }

        return $this->render('signup', [
            'model' => $model,
        ]);
    }

    /**
     * Requests password reset.
     *
     * @return mixed
     */
    public function actionRequestResetPassword()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->redirect(['/dashboard']);
        }

        $model = new RequestResetPasswordForm();

        if (Yii::$app->request->isPost && ($postData = Yii::$app->request->post())) {
            if ($model->load($postData) && $model->validate()) {
                if ($model->sendEmail()) {
                    Yii::$app->session->setFlash('success', 'Check your email for further instructions and a link to reset your password.');
                } else {
                    Yii::$app->session->setFlash('warning', 'There was an error validating your request, please try again.');
                }

                return $this->redirect(['site/login']);
            }
        }

        return $this->render('request-reset-password', [
            'model' => $model,
        ]);
    }

    /**
     * Resets password.
     *
     * @param int $id user id
     * @param int $time
     * @param string $hash
     *
     * @return mixed
     * @throws BadRequestHttpException
     */
    public function actionResetPassword(int $id, int $time, string $hash)
    {
        if (!Yii::$app->user->isGuest) {
            return $this->redirect(['/dashboard']);
        }

        /* @var $user User */
        $user = User::findById($id);

        if ((($time + User::RESET_PASSWORD_REQUEST_LIFETIME) > time()) && $user && $user->isEmailConfirmed()) {
            $model = new ResetPasswordForm();

            if (Yii::$app->request->isPost && ($postData = Yii::$app->request->post()) && $model->load($postData)) {
                if ($model->resetPassword($id, $time, $hash)) {
                    Yii::$app->session->setFlash('success', 'Your new password has been successfully saved.');

                    return $this->redirect(['/dashboard']);
                } else {
                    Yii::$app->session->setFlash('warning', 'There was an error validating your request, please try again.');

                    return $this->redirect(['site/login']);
                }
            }
            // TODO add render invalid-reset-password
            return $this->render('reset-password', [
                'model' => $model,
            ]);
        } else {
            return $this->render('expired-reset-password');
        }
    }

    /**
     * Change the actual language, saving it on a cookie
     * @param $lang String The language to be set
     *
     * @return Redirect to the previous page or if is not set, to the home page
     */
    public function actionChangeLanguage($lang)
    {
        $language = Language::find()
        ->where([
            'code' => $lang,
        ])
        ->one();

        if ($language) {
            $cookies = Yii::$app->response->cookies;

            $langCookie = new \yii\web\Cookie([
                'name' => 'language',
                'value' => $lang,
            ]);

            $cookies->add($langCookie);

            return $this->redirect(Yii::$app->request->referrer ?: Yii::$app->homeUrl);
        }
    }
}
