<?php

namespace app\controllers;

use Yii;
use yii\data\Pagination;
use yii\db\StaleObjectException;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;

use app\components\Controller;
use app\components\Converter;
use app\components\helpers\ReferrerHelper;
use app\models\Contact;
use app\models\Country;
use app\models\Currency;
use app\models\EditProfileForm;
use app\models\Gender;
use app\models\Language;
use app\models\LanguageLevel;
use app\models\Sexuality;
use app\models\StellarServer;
use app\models\User;
use app\models\UserCitizenship;
use app\models\UserEmail;
use app\models\UserLanguage;
use app\models\UserLocation;
use app\models\UserMoqupFollow;
use app\models\UserStatistic;
use app\models\UserStellar;

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

    public function actionDashboard()
    {
        $activeRating = $this->user->getActiveRating();
        $rating = $this->user->getRating();
        $totalRating = User::getTotalRating();
        $percent = $totalRating ? Converter::percentage($rating, $totalRating) : 0;

        $params = [
            'model' => $this->user,
            'activeRating' => $activeRating,
            'overallRating' => [
                'rating' => $rating,
                'totalRating' => $totalRating,
                'percent' => $percent,
            ],
        ];

        return $this->render('dashboard', $params);
    }

    public function actionAccount()
    {
        $params = [
            'model' => $this->user,
        ];

        return $this->render('account', $params);
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

        $realConfirmations = Contact::find()->where([
            'link_user_id' => $user->id,
            'is_real' => 1
        ])->count();

        $params = [
            'model' => $user,
            'realConfirmations' => $realConfirmations,
        ];
        return $this->render('profile', $params);
    }

    public function actionRating()
    {
        $rating = $this->user->getRatings();
        $countQuery = clone $rating;
        $pages = new Pagination(['totalCount' => $countQuery->count()]);
        $models = $rating->offset($pages->offset)
            ->limit($pages->limit)
            ->orderBy(['created_at' => SORT_DESC])
            ->all();

        return $this->render('details/rating', [
            'models' => $models,
            'pages' => $pages,
        ]);
    }

    public function actionChangeEmail()
    {
        $userEmail = $this->user->userEmail ?: $this->user->newUserEmail;

        $renderParams = [
            'userEmail' => $userEmail,
        ];

        if (Yii::$app->request->isPost && ($postData = Yii::$app->request->post('UserEmail'))) {
            $userEmail->email = $postData['email'];

            if ($userEmail->validate()) {
                $userEmail->save(false);
                unset($this->user->userEmail);

                if ($this->user->sendConfirmationEmail()) {
                    Yii::$app->session->setFlash('success', 'Check your email with confirmation link.');
                }

                return $this->redirect('/account');
            }
        }

        return $this->render('fields/change-email', $renderParams);
    }

    /**
     * Confirm user email.
     *
     * @param int $id user id
     * @param int $time
     * @param string $hash
     *
     * @return string
     */
    public function actionConfirmEmail(int $id, int $time, string $hash)
    {
        if ((($time + UserEmail::CONFIRM_REQUEST_LIFETIME) > time()) && !$this->user->isEmailConfirmed()) {
            if (Yii::$app->request->isPost) {
                if ($this->user->confirmEmail($id, $time, $hash)) {
                    Yii::$app->session->setFlash('success', 'Your email has been successfully confirmed.');
                } else {
                    Yii::$app->session->setFlash('warning', 'There was an error validating your request, please try again.');
                }

                return $this->redirect(['/account']);
            }
            // TODO add render invalid-confirm-email
            return $this->render('confirm-email', [
                'user' => $this->user,
            ]);
        } else {
            return $this->render('expired-confirm-email');
        }
    }

    public function actionDeleteEmail()
    {
        if (Yii::$app->request->isPost) {
            if ($userEmail = $this->user->userEmail) {
                $userEmail->delete();
                unset($this->user->userEmail);
            }
        }

        $this->redirect('/account');
    }

    public function actionChangeLocation()
    {
        $userLocation = $this->user->userLocation ?: $this->user->newUserLocation;

        $renderParams = [
            'userLocation' => $userLocation,
        ];

        if (Yii::$app->request->isPost && ($postData = Yii::$app->request->post('UserLocation'))) {
            $userLocation->location = $postData['location'];

            if ($userLocation->validate()) {
                $userLocation->save(false);
                unset($this->user->userLocation);

                return $this->redirect('/account');
            }
        }

        return $this->render('fields/change-location', $renderParams);
    }

    public function actionDeleteLocation()
    {
        if (Yii::$app->request->isPost) {
            if ($userLocation = $this->user->userLocation) {
                $userLocation->delete();
                unset($this->user->userLocation);
            }
        }

        $this->redirect('/account');
    }

    public function actionChangeUsername()
    {
        $renderParams = [
            'user' => $this->user,
        ];

        if (Yii::$app->request->isPost) {
            $this->user->load(Yii::$app->request->post());

            if ($this->user->save()) {
                return $this->redirect('/account');
            }
        }

        return $this->render('fields/change-username', $renderParams);
    }

    public function actionDeleteUsername()
    {
        if (Yii::$app->request->isPost) {
            $this->user->username = null;
            $this->user->save(false);
        }

        $this->redirect('/account');
    }

    public function actionChangeName()
    {
        $renderParams = [
            'user' => $this->user,
        ];

        if (Yii::$app->request->isPost) {
            $this->user->load(Yii::$app->request->post());

            if ($this->user->save()) {
                return $this->redirect('/account');
            }
        }

        return $this->render('fields/change-name', $renderParams);
    }

    public function actionChangeBirthday()
    {
        $renderParams = [
            'user' => $this->user,
        ];

        if (Yii::$app->request->isPost) {
            $this->user->birthday = Yii::$app->formatter->asDate(Yii::$app->request->post('birthday'));

            if ($this->user->save()) {
                return $this->redirect('/account');
            }
        }

        return $this->render('fields/change-birthday', $renderParams);
    }

    public function actionChangeGender()
    {
        $genders = Gender::find()
            ->select([
                'name',
                'id',
            ])
            ->indexBy('id')
            ->asArray()
            ->column();

        foreach ($genders as $key => $gender) {
            $genders[$key] = Yii::t('app', $gender);
        }

        $renderParams = [
            'user' => $this->user,
            'genders' => $genders,
        ];

        if (Yii::$app->request->isPost) {
            $this->user->load(Yii::$app->request->post());

            if ($this->user->save()) {
                return $this->redirect('/account');
            }
        }

        return $this->render('fields/change-gender', $renderParams);
    }

    public function actionChangeTimezone()
    {
        $renderParams = [
            'user' => $this->user,
        ];

        if (Yii::$app->request->isPost) {
            $this->user->load(Yii::$app->request->post());

            if ($this->user->save()) {
                return $this->redirect('/account');
            }
        }

        return $this->render('fields/change-timezone', $renderParams);
    }

    public function actionChangeCurrency()
    {
        $renderParams = [
            'user' => $this->user,
        ];

        if (Yii::$app->request->isPost) {
            $this->user->load(Yii::$app->request->post());

            if ($this->user->save()) {
                return $this->redirect('/account');
            }
        }

        return $this->render('fields/change-currency', $renderParams);
    }

    public function actionChangeSexuality()
    {
        $sexualities = Sexuality::find()
            ->select([
                'name',
                'id',
            ])
            ->indexBy('id')
            ->asArray()
            ->column();

        foreach ($sexualities as $key => $sexuality) {
            $sexualities[$key] = Yii::t('app', $sexuality);
        }

        $renderParams = [
            'user' => $this->user,
            'sexualities' => $sexualities,
        ];

        if (Yii::$app->request->isPost) {
            $this->user->load(Yii::$app->request->post());

            if ($this->user->save()) {
                return $this->redirect('/account');
            }
        }

        return $this->render('fields/change-sexuality', $renderParams);
    }

    public function actionAddLanguage()
    {
        $languages = array_map(function ($language) {
            return strtoupper($language->code) . ' - ' . $language->name;
        }, Language::find()->indexBy('id')->orderBy('code ASC')->all());

        $languageLevels = array_map(function ($languageLevel) {
            return (isset($languageLevel->code) ? strtoupper($languageLevel->code) . ' - ' : '') . Yii::t('user', $languageLevel->description);
        }, LanguageLevel::find()->indexBy('id')->orderBy('code ASC')->all());

        if (Yii::$app->request->isPost && ($postData = Yii::$app->request->post())) {
            $userLanguage = UserLanguage::find()
                ->where([
                    'user_id' => $this->user->id,
                    'language_id' => $postData['language'],
                ])
                ->one();

            $userLanguage = $userLanguage ?? new UserLanguage();

            $userLanguage->setAttributes([
                'user_id' => $this->user->id,
                'language_id' => $postData['language'],
                'language_level_id' => $postData['level']
            ]);

            if ($userLanguage->save()) {
                return $this->redirect('/account');
            }
        }

        $renderParams = [
            'user' => $this->user,
            'languages' => $languages,
            'languageLevels' => $languageLevels,
        ];

        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('fields/add-language', $renderParams);
        } else {
            return $this->render('fields/add-language', $renderParams);
        }
    }

    public function actionChangeLanguage(int $id)
    {
        $languages = array_map(function ($language) {
            return strtoupper($language->code) . ' - ' . $language->name;
        }, Language::find()->indexBy('id')->orderBy('code ASC')->all());

        $languageLevels = array_map(function ($languageLevel) {
            return (isset($languageLevel->code) ? strtoupper($languageLevel->code) . ' - ' : '') . Yii::t('user', $languageLevel->description);
        }, LanguageLevel::find()->indexBy('id')->orderBy('code ASC')->all());

        $userLanguage = UserLanguage::find()
            ->where([
                'user_id' => $this->user->id,
                'id' => $id,
            ])->one();

        if ($userLanguage && Yii::$app->request->isPost && ($postData = Yii::$app->request->post())) {
            $userLanguage->setAttributes([
                'language_level_id' => $postData['level'],
            ]);

            if ($userLanguage->save()) {
                return $this->redirect('/account');
            }
        }

        $renderParams = [
            'user' => $this->user,
            'languages' => $languages,
            'languageLevels' => $languageLevels,
            'userLanguage' => $userLanguage,
        ];

        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('fields/change-language', $renderParams);
        } else {
            return $this->render('fields/change-language', $renderParams);
        }
    }

    public function actionDeleteLanguage()
    {
        if (Yii::$app->request->isPost) {
            $id = Yii::$app->request->post('id');

            $userLanguage = UserLanguage::find()
                ->where([
                    'id' => $id,
                    'user_id' => $this->user->id,
                ])
            ->one();

            if ($userLanguage) {
                $userLanguage->delete();
            }
        }

        return $this->redirect('/account');
    }

    public function actionAddCitizenship()
    {
        if (Yii::$app->request->post() && ($postData = Yii::$app->request->post())) {
            $userCitizenship = UserCitizenship::find()
                ->where([
                    'user_id' => $this->user->id,
                    'country_id' => $postData['UserCitizenship']['country_id'],
                ])
                ->one();

            $userCitizenship = $userCitizenship ?? new UserCitizenship();

            $userCitizenship->setAttributes([
                'user_id' => $this->user->id,
                'country_id' => $postData['UserCitizenship']['country_id'],
            ]);

            if ($userCitizenship->save()) {
                return $this->redirect('/account');
            }
        } else {
            $userCitizenship = new UserCitizenship();
        }

        $renderParams = [
            'model' => $userCitizenship,
        ];

        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('fields/add-citizenship', $renderParams);
        } else {
            return $this->render('fields/add-citizenship', $renderParams);
        }
    }

    public function actionDeleteCitizenship()
    {
        if (Yii::$app->request->isPost) {
            $id = Yii::$app->request->post('id');

            $citizenship = UserCitizenship::find()
                ->where([
                    'country_id' => $id,
                    'user_id' => $this->user->id,
                ])
                ->one();

            if ($citizenship) {
                $citizenship->delete();
            }
        }

        return $this->redirect('/account');
    }

    public function actionViewLocation(): string
    {
        return $this->renderAjax('modals/view-location', ['model' => $this->user->userLocation]);
    }

    public function actionChangeStellar()
    {
        if (!$userStellar = $this->user->stellar) {
            $userStellar = new UserStellar();
            $userStellar->user_id = $this->user->id;
        }

        $renderParams = [
            'userStellar' => $userStellar,
        ];

        if (Yii::$app->request->isPost && ($postData = Yii::$app->request->post('UserStellar'))) {
            $publicKey = $postData['public_key'];

            if ($userStellar->isNewRecord || ($userStellar->public_key != $publicKey)) {
                $userStellar->public_key = $publicKey;
                $userStellar->created_at = time();
                $userStellar->confirmed_at = null;
            }

            if ($userStellar->getDirtyAttributes() && $userStellar->save()) {
                return $this->redirect('/account');
            }
        }

        return $this->render('fields/change-stellar', $renderParams);
    }

    public function actionDeleteStellar()
    {
        if (Yii::$app->request->isPost) {
            if ($userStellar = $this->user->stellar) {
                $userStellar->delete();
            }
        }

        $this->redirect('/account');
    }

    public function actionConfirmStellar()
    {
        if (($userStellar = $this->user->stellar) && !$userStellar->isConfirmed()) {
            if ($stellarServer = new StellarServer()) {
                if (!$stellarServer->accountExists($userStellar->getPublicKey())) {
                    return $this->renderAjax('modals/stellar-alert-account-not-found');
                }


                $userSentTransaction = $stellarServer->operationExists(
                    $userStellar->getPublicKey(),
                    StellarServer::getDistributorPublicKey(),
                    $userStellar->created_at,
                    $userStellar->created_at + UserStellar::CONFIRM_REQUEST_LIFETIME
                );

                if (!$userSentTransaction) {
                    return $this->renderAjax('modals/stellar-alert-transaction-not-found');
                }

                $userStellar->confirm();
            }
        }

        return $this->redirect('/account');
    }
}
