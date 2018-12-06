<?php

namespace app\controllers;

use Yii;
use app\models\SupportGroupBot;
use app\models\SupportGroupCommand;
use app\models\SupportGroupCommandText;
use app\models\SupportGroupLanguage;
use app\models\SupportGroupMember;
use app\models\SupportGroup;
use yii\base\Model;
use yii\bootstrap\ActiveForm;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\Response;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;

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
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all SupportGroup models.
     * @return mixed
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => SupportGroup::find(),
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single SupportGroup model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionMembers($id)
    {
        $dataProvider = new ActiveDataProvider([
            'query' => SupportGroupMember::find()->where(['support_group_id' => intval($id)]),
        ]);

        $member = new SupportGroupMember();
        $member->support_group_id = intval($id);

        if (Yii::$app->request->isAjax && $member->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($member);
        } else {
            if ($member->load(Yii::$app->request->post())) {
                $member->save();
            }
        }

        return $this->render('members', [
            'model' => $this->findModel($id),
            'member' => $member,
            'dataProvider' => $dataProvider,
        ]);
    }


    /**
     * Displays a single SupportGroupBot model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionBots($id)
    {
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
                $bot->save();
            }
        }

        return $this->render('bots', [
            'model' => $this->findModel($id),
            'bot' => $bot,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single SupportGroupCommand model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionCommands($id)
    {
        $dataProvider = new ActiveDataProvider([
            'query' => SupportGroupCommand::find()->where(['support_group_id' => intval($id)]),
        ]);

        $command = new SupportGroupCommand();
        $command->support_group_id = intval($id);

        if ($command->load(Yii::$app->request->post())) {
            if ($command->is_default) {
                SupportGroupCommand::updateAll(['is_default' => 0], 'support_group_id = ' . intval($id));
            }
            $command->save();
        }

        return $this->render('commands', [
            'model' => $this->findModel($id),
            'command' => $command,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single SupportGroupCommand model.
     * @param integer $id
     * @return mixed
     */
    public function actionViewCommand($id)
    {
        $model = SupportGroupCommand::findOne($id);

        if ($model->load(Yii::$app->request->post())) {
            if ($model->is_default) {
                SupportGroupCommand::updateAll(['is_default' => 0], 'support_group_id = ' . $model->support_group_id);
            }
            $model->save();
        }

        return $this->render('view-command', [
            'model' => $model,
            'text' => SupportGroupCommandText::find()->where(['support_group_command_id' => intval($id)])->indexBy('language_code')->all(),
        ]);
    }

    /**
     * Creates a new SupportGroup model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new SupportGroup();

        $count = count(Yii::$app->request->post('SupportGroupLanguage', []));
        $langs = [new SupportGroupLanguage()];
        for($i = 1; $i < $count; $i++) {
            $langs[] = new SupportGroupLanguage();
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {

            if (Model::loadMultiple($langs, Yii::$app->request->post()) && Model::validateMultiple($langs, ['language_code'])) {
                foreach ($langs as $lang) {
                    $lang->support_group_id = $model->id;
                    $lang->save(false);
                }
            }

            return $this->redirect(['index']);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing SupportGroup model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        $langs = SupportGroupLanguage::find()->where(['support_group_id' => intval($id)])->indexBy('id')->all();
        if(empty($langs)){
            $langs[] = new SupportGroupLanguage();
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            /*if (Model::loadMultiple($langs, Yii::$app->request->post())) {
                //SupportGroupLanguage::deleteAll(['support_group_id' => intval($id)]);
                foreach(Yii::$app->request->post('SupportGroupLanguage') as $i => $lang){
                    if($i == 0) {
                        $model2 = new SupportGroupLanguage();
                        $model2->language_code = $lang['language_code'];
                        $model2->support_group_id = intval($id);
                        $model2->save(false);
                    } else {
                        $langs[$i]->save(false);
                    }
                }
            }*/
            return $this->redirect(['index']);
        }

        return $this->render('update', [
            'model' => $model,
            'langs' => $langs,
        ]);
    }

    /**
     * Updates an existing SupportGroupBot model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionBotsUpdate($id)
    {
        $model = SupportGroupBot::findOne($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['bots', 'id' => $model->support_group_id]);
        }

        return $this->redirect(['bots', 'id' => $model->support_group_id]);
    }

    /**
     * Updates an existing SupportGroupCommandText model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionTextUpdate($id)
    {
        $model = SupportGroupCommandText::findOne($id);
        if(is_null($model)){
            $model = new SupportGroupCommandText();
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view-command', 'id' => $model->support_group_command_id]);
        }

        return $this->redirect(['view-command', 'id' => $model->support_group_command_id]);
    }

    /**
     * Deletes an existing SupportGroup model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
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
     * @param integer $id
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
     * @param integer $id
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
     * @param integer $id
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
     * @param integer $id
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
