<?php

namespace app\controllers;

use Yii;
use app\models\User;
use app\models\Contact;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;

/**
 * ContactController implements the CRUD actions for Contact model.
 */
class ContactController extends Controller
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
     * Lists all Contact models.
     * @return mixed
     */
    public function actionIndex($view = Contact::VIEW_USER)
    {
        $query = Contact::find()->andWhere(['user_id' => Yii::$app->user->id]);
        if ((int) $view === Contact::VIEW_USER) {
            $query->andWhere(['NOT', ['link_user_id' => null]]);
        } else {
            $query->andWhere(['link_user_id' => null]);
        }
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        return $this->render('index', [
            'view' => $view,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Contact model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Contact model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Contact();

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $model->user_id = Yii::$app->user->id;
            if (!empty($model->userIdOrName)) {
                if ($model->user_id == $model->userIdOrName) {
                    Yii::$app->session->setFlash('error', 'User ID / You are trying to enter your ID.');

                    return $this->render('create', [
                        'model' => $model,
                    ]);
                } else {
                    $user = User::find()
                        ->andWhere([
                            'OR',
                            ['id' => $model->userIdOrName],
                            ['username' => $model->userIdOrName]
                        ])
                        ->one();
                    if (!empty($user->contact)) {
                        $contact = $user->contact;
                        $contact->link_user_id = null;
                        $contact->save(false);
                    }
                    $model->link_user_id = $user->id;

                    $model->save(false);

                    return $this->redirect(['view', 'id' => $model->id]);
                }
            }else{
                $model->save(false);
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Contact model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        if (!empty($model->linkedUser)) {
            $model->userIdOrName = !empty($model->linkedUser->username) ? $model->linkedUser->username : $model->linkedUser->id;
        }

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $model->user_id = Yii::$app->user->id;
            if (!empty($model->userIdOrName)) {
                if ($model->user_id == $model->userIdOrName) {
                    Yii::$app->session->setFlash('error', 'User ID / You are trying to enter your ID.');

                    return $this->render('update', [
                        'model' => $model,
                    ]);

                } else {
                    $user = User::find()
                        ->andWhere([
                            'OR',
                            ['id' => $model->userIdOrName],
                            ['username' => $model->userIdOrName]
                        ])
                        ->one();
                    if (!empty($user->contact) && ((int) $user->contact->id !== (int) $id)) {
                        $contact = $user->contact;
                        $contact->link_user_id = null;
                        $contact->save(false);
                    }
                    $model->link_user_id = $user->id;
                }
            } else {
                $model->link_user_id = null;
            }
            $model->save(false);
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Contact model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index', 'view' => Contact::VIEW_USER]);
    }

    /**
     * Finds the Contact model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Contact the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Contact::find()->andWhere(['id' => $id, 'user_id' => Yii::$app->user->id])->one()) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
