<?php

namespace app\controllers;

use Yii;
use app\models\Contact;
use app\models\Gender;
use app\models\LoginForm;
use app\models\RequestResetPasswordForm;
use app\models\Rating;
use app\models\ResetPasswordForm;
use app\models\SignupForm;
use app\models\User;
use app\models\UserEmail;
use app\models\Currency;
use app\models\Sexuality;
use app\modules\bot\models\User as BotUser;
use app\models\MergeAccountsRequest;
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

    public function actionMergeAccounts($token)
    {
        $mergeAccountsRequest = MergeAccountsRequest::findOne(['token' => $token]);
        $user = null;
        $userToMerge = null;
        if ($mergeAccountsRequest) {
            $user = User::findOne(['id' => $mergeAccountsRequest->user_id]);
            $userToMerge = User::findOne(['id' => $mergeAccountsRequest->user_to_merge_id]);
            if (Yii::$app->request->isPost) {
                if ($this->mergeAccounts($user, $userToMerge)) {
                    return $this->redirect(['site/login']);
                } else {
                    $mergeAccountsRequest->delete();
                    unset($mergeAccountsRequest);
                }
            } else {
                $created_at = $mergeAccountsRequest->created_at;

                if ($created_at + User::PASSWORD_RESET_TOKEN_EXPIRE < time()) {
                    $mergeAccountsRequest->delete();
                    unset($mergeAccountsRequest);
                }
            }
        }

        return $this->render('mergeAccounts', [
            'model' => $mergeAccountsRequest ?? null,
            'user' => $user,
            'userToMerge' => $userToMerge,
        ]);
    }

    private function mergeAccounts($user, $userToMerge)
    {
        $connection = Yii::$app->db;
        $transaction = $connection->beginTransaction();
        try {
            BotUser::updateAll(['user_id' => $user->id], "user_id = {$userToMerge->id}");
            \app\models\CompanyUser::updateAll(['user_id' => $user->id], "user_id = {$userToMerge->id}");
            \app\models\AdSearch::updateAll(['user_id' => $user->id], "user_id = {$userToMerge->id}");
            \app\models\AdSearchResponse::updateAll(['user_id' => $user->id], "user_id = {$userToMerge->id}");
            \app\models\AdOffer::updateAll(['user_id' => $user->id], "user_id = {$userToMerge->id}");
            \app\models\AdOfferResponse::updateAll(['user_id' => $user->id], "user_id = {$userToMerge->id}");
            \app\models\Resume::updateAll(['user_id' => $user->id], "user_id = {$userToMerge->id}");
            \app\models\ResumeResponse::updateAll(['user_id' => $user->id], "user_id = {$userToMerge->id}");
            \app\models\Vacancy::updateAll(['user_id' => $user->id], "user_id = {$userToMerge->id}");
            \app\models\VacancyResponse::updateAll(['user_id' => $user->id], "user_id = {$userToMerge->id}");
            \app\models\CurrencyExchangeOrder::updateAll(['user_id' => $user->id], "user_id = {$userToMerge->id}");
            \app\models\CurrencyExchangeOrderResponse::updateAll(['user_id' => $user->id], "user_id = {$userToMerge->id}");
            \app\models\User::updateAll(['referrer_id' => $user->id], "referrer_id = {$userToMerge->id}");
            \app\models\UserLanguage::updateAll(['user_id' => $user->id], "user_id = {$userToMerge->id}");
            \app\models\UserCitizenship::updateAll(['user_id' => $user->id], "user_id = {$userToMerge->id}");
            \app\models\Rating::updateAll(['user_id' => $user->id], "user_id = {$userToMerge->id}");
            \app\modules\comment\models\MoqupComment::updateAll(['user_id' => $user->id], "user_id = {$userToMerge->id}");
            \app\models\Contact::updateAll(['user_id' => $user->id], "user_id = {$userToMerge->id}");
            \app\models\Contact::updateAll(['link_user_id' => $user->id], "link_user_id = {$userToMerge->id}");
            \app\models\Debt::updateAll(['from_user_id' => $user->id], "from_user_id = {$userToMerge->id}");
            \app\models\Debt::updateAll(['to_user_id' => $user->id], "to_user_id = {$userToMerge->id}");
            \app\models\DebtBalance::updateAll(['from_user_id' => $user->id], "from_user_id = {$userToMerge->id}");
            \app\models\DebtBalance::updateAll(['to_user_id' => $user->id], "to_user_id = {$userToMerge->id}");
            \app\models\DebtRedistribution::updateAll(['user_id' => $user->id], "user_id = {$userToMerge->id}");
            \app\models\DebtRedistribution::updateAll(['link_user_id' => $user->id], "link_user_id = {$userToMerge->id}");
            \app\models\Issue::updateAll(['user_id' => $user->id], "user_id = {$userToMerge->id}");
            \app\modules\comment\models\IssueComment::updateAll(['user_id' => $user->id], "user_id = {$userToMerge->id}");
            \app\models\Moqup::updateAll(['user_id' => $user->id], "user_id = {$userToMerge->id}");
            \app\models\SettingValueVote::updateAll(['user_id' => $user->id], "user_id = {$userToMerge->id}");
            \app\models\SupportGroup::updateAll(['user_id' => $user->id], "user_id = {$userToMerge->id}");
            \app\models\SupportGroupBotClient::updateAll(['provider_bot_user_id' => $user->id], "provider_bot_user_id = {$userToMerge->id}");
            \app\models\SupportGroupMember::updateAll(['user_id' => $user->id], "user_id = {$userToMerge->id}");
            \app\models\UserIssueVote::updateAll(['user_id' => $user->id], "user_id = {$userToMerge->id}");
            \app\models\UserMoqupFollow::updateAll(['user_id' => $user->id], "user_id = {$userToMerge->id}");
            \app\models\UserWikiPage::updateAll(['user_id' => $user->id], "user_id = {$userToMerge->id}");
            \app\models\UserWikiToken::updateAll(['user_id' => $user->id], "user_id = {$userToMerge->id}");
            \app\models\UserStellar::updateAll(['user_id' => $user->id], "user_id = {$userToMerge->id}");
            \app\models\UserEmail::updateAll(['user_id' => $user->id], "user_id = {$userToMerge->id}");

            $userToMerge->delete();

            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();

            return false;
        }

        return true;
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
        $language = \app\models\Language::find($lang)
            ->one();

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
}
