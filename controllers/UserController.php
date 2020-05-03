<?php

namespace app\controllers;

use app\components\helpers\ReferrerHelper;
use app\models\ChangeEmailRequest;
use app\models\Country;
use app\models\EditProfileForm;
use app\models\Gender;
use app\models\Currency;
use app\models\Language;
use app\models\LanguageLevel;
use app\models\Sexuality;
use app\models\UserCitizenship;
use app\models\UserLanguage;
use app\models\UserStatistic;
use Yii;
use app\models\User;
use app\models\UserMoqupFollow;
use yii\db\StaleObjectException;
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
        parent::init();
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

        $postData = Yii::$app->request->post('User');
        $email = $postData['email'];

        if ($email !== $this->user->email) {
            $changeEmailRequest = new ChangeEmailRequest();
            $changeEmailRequest->setAttributes([
                'email' => $email,
                'user_id' => $this->user->id,
                'token' => Yii::$app->security->generateRandomString(),
            ]);

            if ($changeEmailRequest->save()) {
                if ($changeEmailRequest->sendEmail()) {
                    Yii::$app->session->setFlash('success', 'Check your new email.');
                }
                return $this->redirect('/account');
            }
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
        $genders = Gender::find()->select(['name', 'id'])->indexBy('id')->asArray()->column();
        foreach ($genders as $key => $gender) {
            $genders[$key] = Yii::t('app', $gender);
        }

        if (!Yii::$app->request->isPost) {
            return $this->render('fields/change-gender', ['user' => $this->user, 'genders' => $genders]);
        }

        $this->user->load(Yii::$app->request->post());

        if ($this->user->save()) {
            return $this->redirect('/account');
        }

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
        $currencies = Currency::find()->select(['name', 'id'])->indexBy('id')->asArray()->column();
        foreach ($currencies as $key => $currency) {
            $currencies[$key] = Yii::t('app', $currency);
        }

        if (!Yii::$app->request->isPost) {
            return $this->render('fields/change-currency', ['user' => $this->user, 'currencies' => $currencies]);
        }

        $this->user->load(Yii::$app->request->post());

        if ($this->user->save()) {
            return $this->redirect('/account');
        }

        return $this->render('fields/change-currency', ['user' => $this->user, 'currencies' => $currencies]);
    }

    public function actionChangeSexuality()
    {
        $sexualities = Sexuality::find()->select(['name', 'id'])->indexBy('id')->asArray()->column();
        foreach ($sexualities as $key => $sexuality) {
            $sexualities[$key] = Yii::t('app', $sexuality);
        }

        if (!Yii::$app->request->isPost) {
            return $this->render('fields/change-sexuality', ['user' => $this->user, 'sexualities' =>
                $sexualities]);
        }

        $this->user->load(Yii::$app->request->post());

        if ($this->user->save()) {
            return $this->redirect('/account');
        }

        return $this->render('fields/change-sexuality', ['user' => $this->user, 'sexualities' => $sexualities]);
    }

    /*
     * Экшн для смены языков пользователя
     */
    public function actionChangeLanguage(int $id)
    {
        $languages = array_map(function ($language){
            return strtoupper($language->code) . ' - ' . Yii::t('app', $language->name);
        }, Language::find()->indexBy('id')->orderBy('code ASC')->all());

        $languageName = Language::findOne($id)->name;

        $languagesLvl = array_map(function ($languageLvl) {
            return (isset($languageLvl->code) ? strtoupper($languageLvl->code) . ' - ' : '') . Yii::t('app',
                    $languageLvl->description);
        }, LanguageLevel::find()->indexBy('id')->orderBy('code ASC')->all());

        $userLanguageRecord = UserLanguage::find()->where([
            'user_id' => $this->user->id,
            'language_id' => $id,
        ])->one();

        if(Yii::$app->request->post()) {
            $postData = Yii::$app->request->post();
            $userLanguageRecord = $userLanguageRecord ?? new UserLanguage();
            $userLanguageRecord->setAttributes([
                'user_id' => $this->user->id,
                'language_id' => $id,
                'language_level_id' => $postData['lvl']
            ]);

            if ($userLanguageRecord->save()) {
                return $this->redirect('/account');
            }
        }

        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('fields/change-language', ['user' => $this->user, 'languages' =>
                $languages, 'languagesLvl' => $languagesLvl, 'userLanguageRecord' => $userLanguageRecord, 'languageName' => $languageName]);

        } else {
            return $this->render('fields/change-language', ['user' => $this->user, 'languages' => $languages, 'languagesLvl' => $languagesLvl, 'userLanguageRecord' => $userLanguageRecord, 'languageName' => $languageName]);
        }
    }

    public function actionAddLanguage()
    {
        $languages = array_map(function ($language) {
            return strtoupper($language->code) . ' - ' . Yii::t('app', $language->name);
        }, Language::find()->indexBy('id')->orderBy('code ASC')->all());

        $languagesLvl = array_map(function ($languageLvl) {
            return (isset($languageLvl->code) ? strtoupper($languageLvl->code) . ' - ' : '') . Yii::t('app',
                    $languageLvl->description);
        }, LanguageLevel::find()->indexBy('id')->orderBy('code ASC')->all());

        if(Yii::$app->request->post()) {
            $postData = Yii::$app->request->post();

            $userLanguageRecord = UserLanguage::find()->where([
                'user_id' => $this->user->id,
                'language_id' => $postData['language'],
            ])->one();
            $userLanguageRecord = $userLanguageRecord ?? new UserLanguage();
            $userLanguageRecord->setAttributes([
                'user_id' => $this->user->id,
                'language_id' => $postData['language'],
                'language_level_id' => $postData['lvl']
            ]);

            if ($userLanguageRecord->save()) {
                return $this->redirect('/account');
            }
        }

        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('fields/add-language', ['user' => $this->user, 'languages' => $languages, 'languagesLvl' => $languagesLvl]);

        } else {
            return $this->render('fields/add-language', ['user' => $this->user, 'languages' => $languages, 'languagesLvl' => $languagesLvl]);
        }
    }

    public function actionDeleteLanguage(int $id)
    {
        $language = UserLanguage::find()->where([ 'id' => $id, 'user_id' => $this->user->id ])->one();
        if(!$language) {
            $this->redirect('/account');
        }
        try {
            $language->delete();
        } catch (StaleObjectException $e) {
        } catch (\Throwable $e) {
        }

        $this->redirect('/account');
    }

    public function actionAddCitizenship()
    {
        $citizenships = array_map(function ($citizenship) {
            return Yii::t('app', $citizenship->name);
        }, Country::find()->indexBy('id')->orderBy('code ASC')->all());

        if(Yii::$app->request->post()) {
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

        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('fields/add-citizenship', ['user' => $this->user, 'citizenships' => $citizenships]);

        } else {
            return $this->render('fields/add-citizenship', ['user' => $this->user, 'citizenships' => $citizenships]);
        }
    }

    public function actionDeleteCitizenship(int $id)
    {
        $citizenship = UserCitizenship::find()->where([ 'country_id' => $id, 'user_id' => $this->user->id ])->one();
        if(!$citizenship) {
            $this->redirect('/account');
        }
        try {
            $citizenship->delete();
        } catch (StaleObjectException $e) {
        } catch (\Throwable $e) {
        }

        $this->redirect('/account');
    }
}
