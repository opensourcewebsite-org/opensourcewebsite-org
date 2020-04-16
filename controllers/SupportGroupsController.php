<?php

namespace app\controllers;

use app\components\helpers;
use app\components\SupportGroupComponent;
use app\models\Language;
use app\models\search\SupportGroupBotClientSearch;
use app\models\search\SupportGroupLanguageSearch;
use app\models\search\SupportGroupOutsideMessageSearch;
use app\models\search\SupportGroupSearch;
use app\models\Setting;
use app\models\SupportGroup;
use app\models\SupportGroupBot;
use app\models\SupportGroupBotClient;
use app\models\SupportGroupCommand;
use app\models\SupportGroupCommandText;
use app\models\SupportGroupInsideMessage;
use app\models\SupportGroupLanguage;
use app\models\SupportGroupMember;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\HttpException;
use Yii;
use yii\base\Model;
use yii\bootstrap\ActiveForm;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use app\models\SupportGroupOutsideMessage;
use app\models\User;

/**
 * SupportGroupController implements the CRUD actions for SupportGroup model.
 */
class SupportGroupsController extends Controller
{

    /** @var SupportGroupComponent\Keeper */
    protected $supportComponent;

    public function init()
    {
        $this->supportComponent = new SupportGroupComponent\Keeper();
    }

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
            'verbs'  => [
                'class'   => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all SupportGroup models.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        $dataProvider = new SupportGroupSearch();
        $dataProvider->user_id = Yii::$app->user->id;

        $setting = Setting::findOne(['key' => 'support_group_quantity_value_per_one_rating']);
        $settingQty = $setting->value;

        return $this->render('index', [
            'dataProvider' => $dataProvider->search(),
            'settingQty'   => $settingQty,
        ]);
    }

    /**
     * Displays a single SupportGroupMember model.
     *
     * @param integer $id
     *
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionMembers($id)
    {
        $model = $this->findModel($id);
        if ($model->user_id != Yii::$app->user->identity->id) {
            $this->redirect('index');
        }

        $user = User::find()
            ->joinWith('contact')
            ->andWhere(['status' => User::STATUS_ACTIVE])
            ->andWhere(['NOT', ['link_user_id' => null]])
            ->all();

        $dataProvider = new ActiveDataProvider([
            'query' => SupportGroupMember::find()->where(['support_group_id' => intval($id)]),
        ]);

        $setting = Setting::findOne(['key' => 'support_group_member_quantity_value_per_one_rating']);
        $settingQty = $setting->value;

        $member = new SupportGroupMember();
        $member->support_group_id = intval($id);

        if (Yii::$app->request->isAjax && $member->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            return ActiveForm::validate($member);
        } else {
            if ($member->load(Yii::$app->request->post())) {
                $member->save();

                return $this->redirect(['members', 'id' => $id]);
            }
        }

        return $this->render('members', [
            'model'        => $model,
            'user'         => $user,
            'member'       => $member,
            'dataProvider' => $dataProvider,
            'settingQty'   => $settingQty,
        ]);
    }

    /**
     * Displays a single SupportGroupBot model.
     *
     * @param integer $id
     *
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionBots($id)
    {
        $model = $this->findModel($id);
        if ($model->user_id != Yii::$app->user->identity->id) {
            $this->redirect('index');
        }

        $dataProvider = new ActiveDataProvider([
            'query' => SupportGroupBot::find()->where(['support_group_id' => intval($id)]),
        ]);

        $bot = new SupportGroupBot();
        $bot->support_group_id = intval($id);

        if (Yii::$app->request->isAjax && $bot->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            return ActiveForm::validate($bot);
        } else {
            if ($bot->load(Yii::$app->request->post())) {
                if ($bot->setWebhook()) {
                    //delete bot if already exists
                    $botexists = SupportGroupBot::find()->where(['token' => $bot->token])->one();
                    if ($botexists) {
                        $botexists->delete();
                    }

                    $bot->save();

                    return $this->redirect(['bots', 'id' => $id]);
                }
            }
        }

        $setting = Setting::findOne(['key' => 'support_group_bot_quantity_value_per_one_rating']);
        $settingQty = $setting->value;

        return $this->render('bots', [
            'model'        => $model,
            'bot'          => $bot,
            'dataProvider' => $dataProvider,
            'settingQty'   => $settingQty,
        ]);
    }

    /**
     * @param integer $id
     *
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionClientsLanguages($id)
    {

        $access = self::accessFindModel($id);

        if (!$access) {
            throw new NotFoundHttpException;
        }

        $searchModel = new SupportGroupLanguageSearch();
        $searchModel->support_group_id = $id;
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        if ($dataProvider->getTotalCount() == 1) {
            $model = $dataProvider->getModels();
            return $this->redirect(['clients-list', 'id' => $searchModel->support_group_id]);
        }

        return $this->render('clients-languages', [
            'dataProvider' => $dataProvider,
            'searchModel'  => $searchModel,
        ]);
    }

    /**
     * @param int $id
     * @param string $language
     *
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionClientsList($id, $language = null)
    {
        $access = self::accessFindModel($id);

        if (!$access) {
            throw new NotFoundHttpException;
        }

        $searchModel = new SupportGroupBotClientSearch();
        $searchModel->language = $language;
        $searchModel->support_group_id = $id;
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('clients-list', [
            'dataProvider' => $dataProvider,
            'searchModel'  => $searchModel,
        ]);
    }

    /**
     * @param int $id
     *
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionClientsView($id)
    {
        $model = SupportGroupBotClient::find()
            ->with(['supportGroupClient', 'supportGroupBot'])
            ->where(['id' => $id])
            ->one();

        $access = self::accessFindModel($model->supportGroupClient->support_group_id);

        if (!$access) {
            throw new NotFoundHttpException;
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', Yii::t('app', 'Description changed'));
            return $this->refresh();
        }

        $searchModel = new SupportGroupOutsideMessageSearch();
        $searchModel->support_group_bot_client_id = $model->id;
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $sendMessage = new SupportGroupInsideMessage();

        if ($sendMessage->load(Yii::$app->request->post())) {
            $botApi = new BotApi($model->supportGroupBot->token);

            # For Test in my country;
            if (isset(Yii::$app->params['telegramProxy'])) {
                $botApi->setProxy(Yii::$app->params['telegramProxy']);
            }

            try {
                $botApi->sendMessage($model->provider_bot_user_id, $sendMessage->message);
            } catch (HttpException $e) {
                # we consider here that user blocked bot
                if ($e->getMessage() === 'Forbidden: bot was blocked by the user') {
                    $model->provider_bot_user_blocked = true;
                    Yii::$app->session->setFlash('danger', Yii::t('app', $e->getMessage()));
                    $model->save();
                    return $this->refresh();
                }
            }
            $sendMessage->setAttributes([
                'support_group_bot_id' => $model->supportGroupBot->id,
                'support_group_bot_client_id' => $model->id
            ]);

            if ($sendMessage->save()) {
                Yii::$app->session->setFlash('success', Yii::t('app', 'Message delivered!'));
                return $this->redirect([
                    'clients-view',
                    'id' => $id,
                    'page' => SupportGroupOutsideMessage::getLastPage($model->id)
                ]);
            }
        }

        return $this->render('view-client', [
            'model' => $model,
            'sendMessage' => $sendMessage,
            'dataProvider' => $dataProvider,
            'searchModel'  => $searchModel,
        ]);
    }

    /**
     * Displays a single SupportGroupCommand model.
     *
     * @param integer $id
     *
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionCommands($id)
    {
        $model = $this->accessFindModel($id);

        $dataProvider = new ActiveDataProvider([
            'query' => SupportGroupCommand::find()->where(['support_group_id' => intval($id)]),
        ]);

        $command = new SupportGroupCommand();
        $command->support_group_id = intval($id);

        if (Yii::$app->request->isAjax && $command->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            return ActiveForm::validate($command);
        } elseif ($command->load(Yii::$app->request->post())) {
            if ($command->is_default) {
                SupportGroupCommand::updateAll(['is_default' => 0], 'support_group_id = ' . intval($id));
            }

            $command->save();
        }

        return $this->render('commands', [
            'model'        => $model,
            'command'      => $command,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single SupportGroupCommand model.
     *
     * @param integer $id
     *
     * @return mixed
     *
     * @throws NotFoundHttpException
     */
    public function actionViewCommand($id)
    {
        $model = SupportGroupCommand::find()
            ->where(['id' => $id])
            ->with(['supportGroupCommandTexts', 'languages'])
            ->one();

        if (!$model) {
            throw new NotFoundHttpException;
        }

        $model->setLanguagesIndexes();

        if ($model->load(Yii::$app->request->post())) {
            if ($model->is_default) {
                SupportGroupCommand::updateAll(['is_default' => 0], 'support_group_id = ' . $model->support_group_id);
            }
            $model->save();
        }

        return $this->render('view-command', [
            'model' => $model,
        ]);
    }

    /**
     * Creates a new SupportGroup model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     *
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new SupportGroup();
        $languages = Language::find()->all();
        $requestData = Yii::$app->request->post();
        if (empty($requestData)) {
            return $this->render('create', [
                'model' => $model,
                'langs' => [],
                'languages' => $languages,
            ]);
        }

        $sgLanguageCodes = helpers\ArrayHelper::getValue($requestData, 'SupportGroupLanguage', []);
        $supportGroupLanguages = [];
        try {
            $this->supportComponent->storeSupportGroup($model, $requestData, $languages);
            $command = $this->supportComponent->createSupportGroupCommand($model->id);
            $supportGroupLanguages = $this->supportComponent->createSupportGroupLanguages($model->id, $sgLanguageCodes);

            foreach ($supportGroupLanguages as $supportGroupLanguage) {
                $this->supportComponent->createSupportGroupCommandText(
                    $command->id,
                    $supportGroupLanguage->language->code,
                    Yii::t('app', 'Welcome to OpenSourceWebsite.org') . '!'
                );
            }
        } catch (\Exception $e) {
            return $this->render('create', [
                'model' => $model,
                'langs' => $supportGroupLanguages,
                'languages' => $languages,
            ]);
        }


        return $this->redirect(['index']);
    }

    /**
     * Updates an existing SupportGroup model.
     * If update is successful, the browser will be redirected to the 'view' page.
     *
     * @param integer $id
     *
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        if ($model->user_id != Yii::$app->user->identity->id) {
            $this->redirect('index');
        }

        $langs = SupportGroupLanguage::find()->where(['support_group_id' => intval($id)])->indexBy('id')->all();
        $languages = Language::find()->all();
        $requestData = Yii::$app->request->post();

        if (empty($requestData)) {
            return $this->render('update', [
                'model' => $model,
                'langs' => $langs,
                'languages' => $languages,
            ]);
        }

        try {
            $this->supportComponent->storeSupportGroup($model, $requestData, $languages);
            $this->supportComponent->removeAllSupportGroupLanguagesBySupportGroupId(intval($id));
            $this->supportComponent->createSupportGroupLanguages(
                $model->id,
                helpers\ArrayHelper::getValue($requestData, 'SupportGroupLanguage', [])
            );
        } catch (\Exception $e) {
            return $this->render('update', [
                'model' => $model,
                'langs' => $langs,
                'languages' => $languages,
            ]);
        }

        return $this->redirect(['index']);
    }

    /**
     * Updates an existing SupportGroupBot model.
     * If update is successful, the browser will be redirected to the 'view' page.
     *
     * @param integer $id
     *
     * @return mixed
     */
    public function actionBotsUpdate($id)
    {
        $bot = SupportGroupBot::findOne($id);

        if (Yii::$app->request->isAjax && $bot->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            return ActiveForm::validate($bot);
        } else {
            if ($bot->load(Yii::$app->request->post())) {
                if ($bot->setWebhook()) {
                    //delete bot if already exists
                    $botexists = SupportGroupBot::find()->where(['token' => $bot->token])->andWhere([
                        '!=', 'id', $bot->id,
                    ])->one();
                    if ($botexists) {
                        $botexists->delete();
                    }
                    $bot->save();

                    return $this->redirect(['bots', 'id' => $bot->support_group_id]);
                }
            }
        }

        return $this->redirect(['bots', 'id' => $bot->support_group_id]);
    }

    /**
     * Updates an existing SupportGroupCommandText model.
     * If update is successful, the browser will be redirected to the 'view' page.
     *
     * @param integer $id
     *
     * @return mixed
     *
     * @throws NotFoundHttpException
     */
    public function actionTextUpdate($id = null)
    {
        $model = SupportGroupCommandText::find()
            ->with([
                'supportGroupCommand',
                'supportGroupCommand.supportGroup',
            ])
            ->where(['id' => $id])
            ->one();

        if ($model) {
            $access = self::accessFindModel($model->supportGroupCommand->supportGroup->id);

            if (!$access) {
                throw new NotFoundHttpException;
            }
        }

        if (is_null($model)) {
            $model = new SupportGroupCommandText();
        }

        if ($model->load(Yii::$app->request->post())) {
            if ($model->validate() && $model->save()) {
                return $this->redirect(['view-command', 'id' => $model->support_group_command_id]);
            }
        }

        return $this->redirect(['view-command', 'id' => $model->support_group_command_id]);
    }

    /**
     * Deletes an existing SupportGroup model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     *
     * @param integer $id
     *
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Deletes an existing SupportGroupBot model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     *
     * @param integer $id
     *
     * @return mixed
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionBotsDelete($id)
    {
        $model = SupportGroupBot::findOne($id);
        $model->delete();

        return $this->redirect(['bots', 'id' => $model->support_group_id]);
    }

    /**
     * Deletes an existing SupportGroupMember model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     *
     * @param integer $id
     *
     * @return mixed
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionMembersDelete($id)
    {
        $members = SupportGroupMember::findOne($id);
        $members->delete();

        return $this->redirect(['members', 'id' => $members->support_group_id]);
    }

    /**
     * Deletes an existing SupportGroupCommand model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     *
     * @param integer $id
     *
     * @return mixed
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionCommandDelete($id)
    {
        $members = SupportGroupCommand::findOne($id);
        $members->delete();

        return $this->redirect(['commands', 'id' => $members->support_group_id]);
    }

    /**
     * Finds the SupportGroup model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @param integer $id
     *
     * @return SupportGroup the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = SupportGroup::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    /**
     * Finds the SupportGroup model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @param integer $id
     *
     * @return SupportGroup the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function accessFindModel($id)
    {
        $supportGroup = SupportGroup::tableName();
        $model = SupportGroup::find()
            ->where([
                $supportGroup . '.user_id' => Yii::$app->user->id,
            ])
            ->orWhere([
                '{{%support_group_member}}.user_id' => Yii::$app->user->id,
            ])
            ->andWhere([$supportGroup . '.id' => intval($id)])
            ->joinWith('supportGroupMembers')
            ->one();

        if (!$model) {
            throw new NotFoundHttpException;
        }

        return $model;
    }

    /**
     * Leave an existing SupportGroupMember model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     *
     * @param integer $id
     */
    public function actionLeave($id)
    {
        $member = SupportGroupMember::find()
            ->andWhere([
                'support_group_id' => $id,
                'user_id' => Yii::$app->user->id,
            ])
            ->one();
        $member->delete();

        return $this->redirect(['index']);
    }
}
