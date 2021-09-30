<?php

namespace app\controllers;

use Yii;
use app\components\Controller;
use app\components\helpers\ReferrerHelper;
use app\models\Country;
use app\models\Contact;
use app\models\EditProfileForm;
use app\models\Gender;
use app\models\Currency;
use app\models\Language;
use app\models\LanguageLevel;
use app\models\Sexuality;
use app\models\UserCitizenship;
use app\models\UserLanguage;
use app\models\UserStatistic;
use app\models\User;
use app\models\UserEmail;
use app\models\UserMoqupFollow;
use yii\data\Pagination;
use yii\db\StaleObjectException;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;
use app\components\Converter;

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

    public function actionAccount()
    {
        $activeRating = $this->user->getActiveRating();
        $rating = $this->user->getRating();
        $totalRating = User::getTotalRating();
        $percent = $totalRating ? Converter::percentage($rating, $totalRating) : 0;

        $rank = $this->user->getRank();
        $totalRank = User::getTotalRank();

        $realConfirmations = $this->user->getContactsToMe()
            ->where([
                'is_real' => 1,
            ])
            ->count();

        $params = [
            'model' => $this->user,
            'realConfirmations' => $realConfirmations,
            'activeRating' => $activeRating,
            'overallRating' => [
                'rating' => $rating,
                'totalRating' => $totalRating,
                'percent' => $percent,
            ],
            'ranking' => [
                'rank' => $rank,
                'total' => $totalRank,
            ],
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
        $userEmail = $this->user->email;

        if (!$userEmail) {
            $userEmail = new UserEmail();
            $userEmail->user_id = $this->user->id;
        }

        $renderParams = [
            'userEmail' => $userEmail,
        ];

        if (Yii::$app->request->isPost) {
            $postData = Yii::$app->request->post('UserEmail');
            $email = $postData['email'];

            if ($userEmail->isNewRecord || ($userEmail->email != $email)) {
                $userEmail->email = $email;
            }

            if ($userEmail->getDirtyAttributes() && $userEmail->save()) {
                unset($this->user->email);
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
                    Yii::$app->session->setFlash('warning', 'There was an error validating your email, please try again.');
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
            if ($userEmail = $this->user->email) {
                $userEmail->delete();
                unset($this->user->email);
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

            $this->user->save();
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
        $currencies = Currency::find()
            ->select([
                'name',
                'id',
            ])
            ->indexBy('id')
            ->asArray()
            ->column();

        foreach ($currencies as $key => $currency) {
            $currencies[$key] = Yii::t('app', $currency);
        }

        $renderParams = [
            'user' => $this->user,
            'currencies' => $currencies,
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

    /*
     * Action for changing language
     */
    public function actionChangeLanguage(int $id)
    {
        $languages = array_map(function ($language) {
            return strtoupper($language->code) . ' - ' . Yii::t('app', $language->name);
        }, Language::find()->indexBy('id')->orderBy('code ASC')->all());

        $languageName = Language::findOne($id)->name;

        $languagesLevel = array_map(function ($languageLevel) {
            return (isset($languageLevel->code) ? strtoupper($languageLevel->code) . ' - ' : '') . Yii::t('user', $languageLevel->description);
        }, LanguageLevel::find()->indexBy('id')->orderBy('code ASC')->all());

        $userLanguageRecord = UserLanguage::find()->where([
            'user_id' => $this->user->id,
            'language_id' => $id,
        ])->one();

        if (Yii::$app->request->post()) {
            $postData = Yii::$app->request->post();
            $userLanguageRecord = $userLanguageRecord ?? new UserLanguage();
            $userLanguageRecord->setAttributes([
                'user_id' => $this->user->id,
                'language_id' => $id,
                'language_level_id' => $postData['level']
            ]);

            if ($userLanguageRecord->save()) {
                return $this->redirect('/account');
            }
        }

        $renderParams = [
            'user' => $this->user,
            'languages' => $languages,
            'languagesLevel' => $languagesLevel,
            'userLanguageRecord' => $userLanguageRecord,
            'languageName' => $languageName
        ];

        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('fields/change-language', $renderParams);
        } else {
            return $this->render('fields/change-language', $renderParams);
        }
    }

    public function actionAddLanguage()
    {
        $languages = array_map(function ($language) {
            return strtoupper($language->code) . ' - ' . Yii::t('app', $language->name);
        }, Language::find()->indexBy('id')->orderBy('code ASC')->all());

        $languagesLevel = array_map(function ($languageLevel) {
            return (isset($languageLevel->code) ? strtoupper($languageLevel->code) . ' - ' : '') . Yii::t('user', $languageLevel->description);
        }, LanguageLevel::find()->indexBy('id')->orderBy('code ASC')->all());

        if (Yii::$app->request->post()) {
            $postData = Yii::$app->request->post();

            $userLanguageRecord = UserLanguage::find()->where([
                'user_id' => $this->user->id,
                'language_id' => $postData['language'],
            ])->one();
            $userLanguageRecord = $userLanguageRecord ?? new UserLanguage();
            $userLanguageRecord->setAttributes([
                'user_id' => $this->user->id,
                'language_id' => $postData['language'],
                'language_level_id' => $postData['level']
            ]);

            if ($userLanguageRecord->save()) {
                return $this->redirect('/account');
            }
        }

        $renderParams = [
            'user' => $this->user,
            'languages' => $languages,
            'languagesLevel' => $languagesLevel,
        ];

        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('fields/add-language', $renderParams);
        } else {
            return $this->render('fields/add-language', $renderParams);
        }
    }

    public function actionDeleteLanguage()
    {
        if (Yii::$app->request->isPost) {
            $id = Yii::$app->request->post('id');

            $language = UserLanguage::find()
                ->where([
                    'id' => $id,
                    'user_id' => $this->user->id,
                ])
            ->one();

            if (!$language) {
                $this->redirect('/account');
            }

            $language->delete();
        }

        $this->redirect('/account');
    }

    public function actionAddCitizenship()
    {
        $citizenships = array_map(function ($citizenship) {
            return Yii::t('app', $citizenship->name);
        }, Country::find()->indexBy('id')->orderBy('code ASC')->all());

        if (Yii::$app->request->post()) {
            $postData = Yii::$app->request->post();

            $userCitizenshipRecord = UserCitizenship::find()->where([
                'user_id' => $this->user->id,
                'country_id' => $postData['country'],
            ])->one();
            $userCitizenshipRecord = $userCitizenshipRecord ?? new UserCitizenship();
            $userCitizenshipRecord->setAttributes([
                'user_id' => $this->user->id,
                'country_id' => $postData['country'],
            ]);

            if ($userCitizenshipRecord->save()) {
                return $this->redirect('/account');
            }
        }

        $renderParams = [
            'user' => $this->user,
            'citizenships' => $citizenships,
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

            if (!$citizenship) {
                $this->redirect('/account');
            }

            $citizenship->delete();
        }

        $this->redirect('/account');
    }
}
