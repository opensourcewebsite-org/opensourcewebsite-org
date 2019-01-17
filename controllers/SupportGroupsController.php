<?php

namespace app\controllers;

use app\models\Language;
use app\models\search\SupportGroupSearch;
use app\models\Setting;
use app\models\SupportGroup;
use app\models\SupportGroupBot;
use app\models\SupportGroupCommand;
use app\models\SupportGroupCommandText;
use app\models\SupportGroupLanguage;
use app\models\SupportGroupMember;
use Yii;
use yii\base\Model;
use yii\bootstrap\ActiveForm;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * SupportGroupController implements the CRUD actions for SupportGroup model.
 */
class SupportGroupsController extends Controller
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
            'settingQty' => $settingQty,
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
     * Displays a single SupportGroupCommand model.
     *
     * @param integer $id
     *
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionCommands($id)
    {
        $model = $this->findModel($id);

        //TODO bug for member users
        if ($model->user_id != Yii::$app->user->identity->id) {
            $this->redirect('index');
        }

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

        $count = count(Yii::$app->request->post('SupportGroupLanguage', []));
        $langs = [new SupportGroupLanguage()];
        for ($i = 1; $i < $count; $i++) {
            $langs[] = new SupportGroupLanguage();
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            if (Model::loadMultiple($langs, Yii::$app->request->post()) && Model::validateMultiple($langs, ['language_code'])) {
                unset($langs[0]);
                foreach (Yii::$app->request->post('SupportGroupLanguage') as $i => $lang) {
                    if ($i != 0) {
                        $model2 = new SupportGroupLanguage();
                        $model2->support_group_id = $model->id;
                        $model2->language_code = $lang['language_code'];
                        $model2->save(false);
                    }
                }
            }

            return $this->redirect(['index']);
        }

        return $this->render('create', [
            'model'     => $model,
            'langs'     => $langs,
            'languages' => Language::find()->all(),
        ]);
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
        if (empty($langs)) {
            $langs[] = new SupportGroupLanguage();
        }

        $languages = Language::find()->all();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            unset($_POST['SupportGroupLanguage'][0]);
            SupportGroupLanguage::deleteAll(['support_group_id' => intval($id)]);
            foreach (Yii::$app->request->post('SupportGroupLanguage') as $i => $lang) {
                if ($i != 0) {
                    $model2 = new SupportGroupLanguage();
                    $model2->language_code = $lang['language_code'];
                    $model2->support_group_id = intval($id);
                    $model2->save(false);
                }
            }

            return $this->redirect(['index']);
        }

        return $this->render('update', [
            'model'     => $model,
            'langs'     => $langs,
            'languages' => $languages,
        ]);
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
     */
    public function actionTextUpdate($id = null)
    {
        $model = SupportGroupCommandText::findOne($id);
        if (is_null($model)) {
            $model = new SupportGroupCommandText();
        }

        // TODO for security reasons better to check owner and member
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view-command', 'id' => $model->support_group_command_id]);
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
}
