<?php

namespace app\controllers;

use app\models\ContactForm;
use app\models\LoginForm;
use app\models\PasswordResetRequestForm;
use app\models\ResetPasswordForm;
use app\models\SignupForm;
use Yii;
use yii\base\InvalidParamException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use app\models\User;
use app\models\Moqup;
use app\models\Css;
use yii\db\Query;

class SiteController extends Controller {

    /**
     * {@inheritdoc}
     */
    public function behaviors() {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout', 'design-list', 'design-view', 'design-edit', 'account'],
                'rules' => [
                    [
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
    public function actions() {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex() {
        return $this->render('index');
    }

    public function actionDonate() {
        return $this->render('donate');
    }

    public function actionTeam() {
        return $this->render('team');
    }

    public function actionTermsOfUse() {
        return $this->render('terms-of-use');
    }

    public function actionPrivacyPolicy() {
        return $this->render('privacy-policy');
    }

    /**
     * Logs in a user.
     *
     * @return mixed
     */
    public function actionLogin() {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();

        if (Yii::$app->request->isPost) {
            if (Yii::$app->request->isAjax) {
                parse_str(Yii::$app->request->post('data'), $postData);
            } else {
                $postData = Yii::$app->request->post();
            }

            if ($model->load($postData) && $model->login()) {
                return $this->goBack();
            }
        }

        $model->password = '';

        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('login-ajax', [
                        'model' => $model,
            ]);
        }

        return $this->render('login', [
                    'model' => $model,
        ]);
    }

    /**
     * Logs out the current user.
     *
     * @return mixed
     */
    public function actionLogout() {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Displays contact page.
     *
     * @return mixed
     */
    public function actionContact() {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail(Yii::$app->params['adminEmail'])) {
                Yii::$app->session->setFlash('success', 'Thank you for contacting us. We will respond to you as soon as possible.');
            } else {
                Yii::$app->session->setFlash('error', 'There was an error sending your message.');
            }

            return $this->refresh();
        }

        return $this->render('contact', [
                    'model' => $model,
        ]);
    }

    /**
     * Signs user up.
     *
     * @return mixed
     */
    public function actionSignup() {
        if (!Yii::$app->request->isAjax) {
            throw new \yii\web\BadRequestHttpException();
        }

        $model = new SignupForm();

        if (Yii::$app->request->isPost) {
            parse_str(Yii::$app->request->post('data'), $postData);

            if ($model->load($postData)) {
                if ($user = $model->signup()) {
                    if (Yii::$app->getUser()->login($user)) {
                        $user->sendConfirmationEmail($user);
                        Yii::$app->session->setFlash('success', 'Check your email for confirmation.');
                        return $this->goHome();
                    }
                }
            }
        }

        return $this->renderAjax('signup', [
                    'model' => $model,
        ]);
    }

    /**
     * Requests password reset.
     *
     * @return mixed
     */
    public function actionRequestPasswordReset() {
        if (!Yii::$app->request->isAjax) {
            throw new \yii\web\BadRequestHttpException();
        }

        $model = new PasswordResetRequestForm();

        if (Yii::$app->request->isPost) {
            parse_str(Yii::$app->request->post('data'), $postData);

            if ($model->load($postData) && $model->validate()) {
                if ($model->sendEmail()) {
                    Yii::$app->session->setFlash('success', 'Check your email for further instructions.');

                    return $this->goHome();
                }

                $model->addError('email', 'Sorry, we are unable to reset password for the provided email address.');
            }
        }

        return $this->renderAjax('requestPasswordResetToken', [
                    'model' => $model,
        ]);
    }

    /**
     * Resets password.
     *
     * @param string $token
     *
     * @return mixed
     * @throws BadRequestHttpException
     */
    public function actionResetPassword($token) {
        try {
            $model = new ResetPasswordForm($token);
        } catch (InvalidParamException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->resetPassword()) {
            Yii::$app->session->setFlash('success', 'New password saved.');

            return $this->goHome();
        }

        return $this->render('resetPassword', [
                    'model' => $model,
        ]);
    }

    public function actionAccount() {
        $model = Yii::$app->user->identity;

        return $this->render('account', ['model' => $model]);
    }

    public function actionConfirm($id, $auth_key) {
        $user = User::findOne([
                    'id' => $id,
                    'auth_key' => $auth_key,
        ]);
        if (!empty($user)) {
            $user->is_email_confirmed = true;
            $user->status = User::STATUS_ACTIVE;
            $user->save();
            Yii::$app->session->setFlash('success', 'Your email has been successfully confirmed.');
        }

        return $this->goHome();
    }

    public function actionResendConfirmationEmail() {
        $user = Yii::$app->user->identity;
        if ($user->sendConfirmationEmail($user)) {
            Yii::$app->session->setFlash('success', 'Check your email for confirmation.');
        }

        return $this->goHome();
    }

    /**
     * Change the actual language, saving it on a cookie
     * @param $lang String The language to be set
     * @return Redirect to the previous page or if is not set, to the home page
     */
    public function actionChangeLanguage($lang) {
        $language = \app\models\Language::find($lang)->one();

        if ($language != NULL) {
            $cookies = Yii::$app->response->cookies;

            $langCookie = new \yii\web\Cookie([
                'name' => 'language',
                'value' => $lang,
            ]);

            $cookies->add($langCookie);

            return $this->redirect(Yii::$app->request->referrer ?: Yii::$app->homeUrl);
        }
    }

    public function actionDesignList($viewMode = NULL) {
        if (!empty($viewMode)) {
            $viewMode = 1;
        } else {
            $viewMode = 0;
        }
        $query = new Query;
        $query->select(['moqup.*', 'user.username as username'])
                ->from('moqup')
                ->where(['!=', 'user_id', Yii::$app->user->id])
                ->leftJoin('user', 'moqup.user_id = user.id')
                ->all();

        $command = $query->createCommand();
        $moqups = $command->queryAll();

        $your_moqups_qry = new Query;
        $your_moqups_qry->select(['moqup.*', 'user.username as username'])
                ->from('moqup')
                ->where(['user_id' => Yii::$app->user->id])
                ->leftJoin('user', 'moqup.user_id = user.id')
                ->all();

        $your_moqups_cmd = $your_moqups_qry->createCommand();
        $your_moqups = $your_moqups_cmd->queryAll();

        return $this->render('design-list', ['viewMode' => $viewMode, 'moqups' => $moqups, 'your_moqups' => $your_moqups]);
    }

    public function actionDesignView($id) {
        $moqup = Moqup::find()
                ->where(['id' => $id])
                ->one();
        $css = Css::find()
                ->where(['moqup_id' => $id])
                ->one();
        return $this->render('design-view', ['moqup' => $moqup, 'css' => $css]);
    }

    public function actionDesignAdd() {
        if (Yii::$app->request->isPost) {
            $formatter = \Yii::$app->formatter;
            $now = $formatter->asDateTime('now');
            $now = strtotime($now);

            $moqup = new Moqup;
            $moqup->user_id = Yii::$app->user->id;
            $moqup->title = Yii::$app->request->post('title');
            $moqup->html = Yii::$app->request->post('html');
            $moqup->created_at = $now;
            $moqup->updated_at = $now;
            $moqup->save();

            $css = new Css;
            $css->moqup_id = $moqup->id;
            $css->css = Yii::$app->request->post('css');
            $css->created_at = $now;
            $css->updated_at = $now;
            $css->save();
//            Yii::$app->session->setFlash('success', 'Your new moqup has been saved.');
            return $this->redirect(['site/design-list']);
        }
        \Yii::$app->getView()->registerJsFile(\Yii::$app->request->BaseUrl . '/js/common.js');
        return $this->render('design-add');
    }

    public function actionDesignEdit($id) {
        $moqup = Moqup::find()
                ->where(['id' => $id])
                ->one();
        $css = Css::find()
                ->where(['moqup_id' => $id])
                ->one();
        if (Yii::$app->request->isPost) {
            $formatter = \Yii::$app->formatter;
            $now = $formatter->asDateTime('now');
            $now = strtotime($now);

            $moqup->user_id = Yii::$app->user->id;
            $moqup->title = Yii::$app->request->post('title');
            $moqup->html = Yii::$app->request->post('html');
            $moqup->updated_at = $now;
            $moqup->save();

            $css->css = Yii::$app->request->post('css');
            $css->updated_at = $now;
//            Yii::$app->session->setFlash('success', 'Moqup has been updated.');
            return $this->redirect(['site/design-list']);
        }
        \Yii::$app->getView()->registerJsFile(\Yii::$app->request->BaseUrl . '/js/common.js');
        return $this->render('design-edit', ['moqup' => $moqup, 'css' => $css]);
    }

    /**
     * Do tasks before the action is executed
     */
    public function beforeAction($action) {
        if (!parent::beforeAction($action)) {
            return false;
        }

        if (Yii::$app->user->isGuest) {
            $this->layout = 'adminlte-guest';
        } else {
            $this->layout = 'adminlte-main';
        }

        return true;
    }

}
