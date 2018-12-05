<?php

namespace app\controllers;

use app\models\SupportGroupBot;
use Yii;
use app\models\SupportGroupMember;
use app\models\SupportGroup;
use yii\bootstrap\ActiveForm;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
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

        $member = new SupportGroupBot();
        $member->support_group_id = intval($id);

        if (Yii::$app->request->isAjax && $member->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($member);
        } else {
            if ($member->load(Yii::$app->request->post())) {
                $member->save();
            }
        }

        return $this->render('bots', [
            'model' => $this->findModel($id),
            'member' => $member,
            'dataProvider' => $dataProvider,
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

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
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
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['index']);
        }

        return $this->render('update', [
            'model' => $model,
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

        return $this->render('update', [
            'model' => $model,
        ]);
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
