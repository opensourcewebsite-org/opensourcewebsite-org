<?php

namespace app\controllers;

use app\models\LoginForm;
use app\models\PasswordResetRequestForm;
use app\models\Rating;
use app\models\ResetPasswordForm;
use app\models\SignupForm;
use app\models\User;
use app\modules\bot\models\BotClient;
use app\models\MergeAccountsRequest;
use Yii;
use yii\base\InvalidParamException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use app\components\Converter;

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
                    'logout', 'design-list', 'design-view', 'design-edit', 'account', 'confirm', 'resend-confirmation-email',
                ],
                'rules' => [
                    [
                        'actions' => ['confirm', 'resend-confirmation-email'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'actions' => ['logout', 'design-list', 'design-view', 'design-edit', 'account'],
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

        if (!parent::beforeAction($action)) {
            return false;
        }

        if (Yii::$app->user->isGuest) {
            $this->layout = 'adminlte-guest';
        } else {
            $this->layout = 'adminlte-user';
        }

        return true;
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
            return $this->redirect(['site/account']);
        }

        $model = new LoginForm();

        if (Yii::$app->request->isPost) {
            $postData = Yii::$app->request->post();

            if ($model->load($postData) && $model->login()) {
                return $this->redirect(['site/account']);
            }
        }

        $model->password = '';

        return $this->render('login', [
            'model' => $model,
        ]);
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
            return $this->redirect(['site/account']);
        }

        $model = new SignupForm();

        if (Yii::$app->request->isPost) {
            $postData = Yii::$app->request->post();

            if ($model->load($postData)) {
                if ($user = $model->signup()) {
                    if (Yii::$app->getUser()->login($user)) {
                        $user->sendConfirmationEmail($user);
                        Yii::$app->session->setFlash('success', 'Check your email for confirmation.');

                        return $this->redirect(['site/login']);
                    }
                }
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
    public function actionRequestPasswordReset()
    {
        $model = new PasswordResetRequestForm();

        if (Yii::$app->request->isPost) {
            $postData = Yii::$app->request->post();

            if ($model->load($postData) && $model->validate()) {
                if ($model->sendEmail()) {
                    Yii::$app->session->setFlash('success', 'Check your email for further instructions.');

                    return $this->redirect(['site/login']);
                }

                $model->addError('email', 'Sorry, we are unable to reset password for the provided email address.');
            }
        }

        return $this->render('requestPasswordResetToken', [
            'model' => $model,
        ]);
    }

    public function actionMergeAccounts($token)
    {
        $mergeAccountsRequest = MergeAccountsRequest::findOne(['token' => $token]);
        if ($mergeAccountsRequest)
        {
            $user = User::findOne(['id' => $mergeAccountsRequest->user_id]);
            $userToMerge = User::findOne(['id' => $mergeAccountsRequest->user_to_merge_id]);
            if (Yii::$app->request->isPost)
            {
                $botClients = BotClient::find()->where(['user_id' => $userToMerge->id])->all();
                foreach ($botClients as $botClient) {
                    $botClient->user_id = $user->id;
                    $botClient->save();
                }
                $userToMerge->delete();
                $mergeAccountsRequest->delete();
                return $this->redirect(['site/login']);
            }
            else
            {
                $created_at = $mergeAccountsRequest->created_at;
                $requestLifeTime = Yii::$app->params['user.passwordResetTokenExpire'];

                if ($created_at + $requestLifeTime < time())
                {
                    $mergeAccountsRequest->delete();
                    unset($mergeAccountsRequest);
                }
            }
        }
        return $this->render('mergeAccounts', [
            'model' => $mergeAccountsRequest,
            'user' => $user,
            'userToMerge' => $userToMerge,
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
    public function actionResetPassword($token)
    {
        try {
            $model = new ResetPasswordForm($token);
        } catch (InvalidParamException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->resetPassword()) {
            Yii::$app->session->setFlash('success', 'New password saved.');

            return $this->redirect(['site/login']);
        }

        return $this->render('resetPassword', [
            'model' => $model,
        ]);
    }

    public function actionAccount()
    {
        $model = Yii::$app->user->identity;

        $activeRating = $model->activeRating;

        $rating = $model->rating;
        $totalRating = Rating::getTotalRating();
        if ($totalRating < 1) {
            $percent = 0;
        } else {
            $percent = Converter::percentage($rating, $totalRating);
        }

        list($total, $rank) = Rating::getRank($rating);

        return $this->render('account', [
            'model' => $model,
            'activeRating' => $activeRating,
            'overallRating' => [
                'rating' => $rating,
                'totalRating' => $totalRating,
                'percent' => $percent,
            ],
            'ranking' => [
                'rank' => $rank,
                'total' => $total,
            ]
        ]);
    }

    /**
     * Confirm user email.
     *
     * @param int $id the user id
     * @param int $auth_key the user auth_key
     *
     * @return string
     */
    public function actionConfirm($id = '', $auth_key = '')
    {
        $transaction = Yii::$app->db->beginTransaction();
        $commit = false;

        $user = SignupForm::confirmEmail($id, $auth_key);

        if (!empty($user)) {

            //Add user rating for confirm email
            $commit = $user->addRating(Rating::CONFIRM_EMAIL, 1, false);
        }

        if ($commit) {
            $transaction->commit();
            Yii::$app->session->setFlash('success', 'Your email has been successfully confirmed.');
        } else {
            $transaction->rollback();
            Yii::$app->session->setFlash('warning', 'There was an error validating your email, please try again.');
        }

        return $this->redirect(['site/login']);
    }

    /**
     * Change the actual language, saving it on a cookie
     * @param $lang String The language to be set
     * @return Redirect to the previous page or if is not set, to the home page
     */
    public function actionChangeLanguage($lang)
    {
        $language = \app\models\Language::find($lang)->one();

        if ($language != null) {
            $cookies = Yii::$app->response->cookies;

            $langCookie = new \yii\web\Cookie([
                'name' => 'language',
                'value' => $lang,
            ]);

            $cookies->add($langCookie);

            return $this->redirect(Yii::$app->request->referrer ?: Yii::$app->homeUrl);
        }
    }

    public function actionDesignList()
    {
        return $this->render('design-list');
    }

    public function actionDesignView()
    {
        return $this->render('design-view');
    }

    public function actionDesignEdit()
    {
        return $this->render('design-edit');
    }
}
