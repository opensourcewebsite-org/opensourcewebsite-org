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
        $query = Contact::find()
            ->userOwner()
            ->virtual((int)$view !== Contact::VIEW_USER);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'name' => SORT_ASC
                ]
            ]
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
        $model = $this->findModel($id);
        if($model->link_user_id) {
            $realConfirmations = Contact::find()->where([
                'link_user_id' => $model->link_user_id,
                'is_real' => 1
            ])->count();
        } else {
            $realConfirmations = $model->is_real ? 1 : 0;
        }

        $params = [
            'model' => $model,
            'realConfirmations' => $realConfirmations,
        ];
        return $this->render('view', $params);
    }

    /**
     * Creates a new Contact model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     *
     * TODO [ref] methods actionCreate and actionUpdate has a lot of duplicated code. Merge logic in common functions
     *        [ref] validation logic should not be implemented in controller. Move it into Model::rules()
     */
    public function actionCreate()
    {
        $model = new Contact();
        $model->loadDefaultValues();

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $model->user_id = Yii::$app->user->id;
            if (!empty($model->userIdOrName)) {
                if ($model->user_id == $model->userIdOrName) {
                    Yii::$app->session->setFlash('error', 'User ID / You are trying to enter your ID.');

                    return $this->render('create', [
                        'model' => $model,
                    ]);
                }
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

            $model->save(false);
            return $this->redirect(['view', 'id' => $model->id]);
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
        $model->userIdOrName = $model->getUserIdOrName();

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $model->user_id = Yii::$app->user->id;
            if (!empty($model->userIdOrName)) {
                if ($model->user_id == $model->userIdOrName) {
                    Yii::$app->session->setFlash('error', 'User ID / You are trying to enter your ID.');

                    return $this->render('update', [
                        'model' => $model,
                    ]);
                }
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
        if (($model = Contact::find()->andWhere(['id' => $id])->userOwner()->one()) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
