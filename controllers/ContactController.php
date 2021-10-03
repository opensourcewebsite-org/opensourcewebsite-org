<?php

namespace app\controllers;

use app\models\ContactGroup;
use Yii;
use app\models\User;
use app\models\Contact;
use app\components\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;

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
                    'delete-group' => ['POST'],

                ],
            ],
        ];
    }

    /**
     * Lists all Contact models.
     * @return mixed
     */
    public function actionIndex()
    {
        $query = Contact::find()
            ->userOwner()
            ->user();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'name' => SORT_ASC,
                ],
            ],
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Lists all Contact models.
     * @return mixed
     */
    public function actionNonUsers()
    {
        $query = Contact::find()
            ->userOwner()
            ->nonUser();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'name' => SORT_ASC,
                ],
            ],
        ]);

        return $this->render('index', [
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
        $contact = $this->findModel($id);

        return $this->render('view', [
            'contact' => $contact,
            'user' => $contact->linkedUser,
        ]);
    }

    /**
     * @param integer|string|null $id User ID / Username
     * @return mixed
     * @throws NotFoundHttpException if the user cannot be found
     */
    public function actionViewUser($id = null)
    {
        if ($id) {
            if ($id == $this->user->id) {
                return $this->run('user/account');
            }

            $user = User::findByUsername($id) ?: User::findById($id);

            if ($user) {
                $contact = Contact::find()
                    ->andWhere([
                        'link_user_id' => $user->id,
                        ])
                    ->userOwner()
                    ->one();

                if (!$contact) {
                    $contact = new Contact();
                    $contact->user_id = $this->user->id;
                    $contact->link_user_id = $user->id;
                    $contact->save(false);
                }

                return $this->render('view', [
                    'contact' => $contact,
                    'user' => $user,
                ]);
            }
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    /*
     * View groups list
     */
    public function actionGroup()
    {
        $query = $this->user->getContactGroups();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'=> [
                'defaultOrder' => [
                    'name' => SORT_ASC,
                ],
            ],
        ]);

        return $this->render('group/index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionCreateGroup()
    {
        $group = new ContactGroup();

        if (Yii::$app->request->isPost && ($postData = Yii::$app->request->post()) && $group->load($postData)) {
            $group->user_id = $this->user->id;

            if ($group->save()) {
                return $this->redirect(['contact/group']);
            }
        }

        $renderParams = [
            'model' => $group,
        ];

        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('group/create', $renderParams);
        } else {
            return $this->render('group/create', $renderParams);
        }
    }

    public function actionDeleteGroup(int $id)
    {
        $model = ContactGroup::findOne([
            'id' => $id,
            'user_id' => $this->user->id,
        ]);

        if ($model) {
            $model->delete();
        }

        return $this->redirect(['contact/group']);
    }

    public function actionUpdateGroup(int $id)
    {
        $group = ContactGroup::findOne([
            'id' => $id,
            'user_id' => $this->user->id,
        ]);

        if (!$group) {
            return $this->redirect(['contact/group']);
        }

        if (Yii::$app->request->isPost && ($postData = Yii::$app->request->post()) && $group->load($postData)) {
            if ($group->save()) {
                return $this->redirect(['contact/group']);
            }
        }

        $renderParams = [
            'model' => $group,
        ];

        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('group/create', $renderParams);
        } else {
            return $this->render('group/create', $renderParams);
        }
    }

    public function actionUpdateGroups(int $id = null, int $link_user_id = null)
    {
        $contact = Contact::findOne([
            'id' => $id,
            'user_id' => $this->user->id,
        ]);

        if (!$contact) {
            return $this->redirect(['index']);
        }

        if (Yii::$app->request->isPost && ($postData = Yii::$app->request->post()) && $contact->load($postData)) {
            if ($contact->validate(['contact_group_ids']) && $contact->save()) {
                return $this->redirect([
                    'view',
                    'id' => $id,
                ]);
            }
        }

        $renderParams = [
            'model' => $contact,
        ];

        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('update-groups', $renderParams);
        } else {
            return $this->render('update-groups', $renderParams);
        }
    }

    /**
     * Creates a new Contact model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     *
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Contact();
        $model->user_id = $this->user->id;

        if (Yii::$app->request->isPost && ($postData = Yii::$app->request->post()) && $model->load($postData)) {
            if ($model->save()) {
                return $this->redirect([
                    'view',
                    'id' => $model->id,
                ]);
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
    public function actionUpdate(int $id)
    {
        $model = $this->findModel($id);
        $model->userIdOrName = $model->getUserIdOrName();

        if (Yii::$app->request->isPost && ($postData = Yii::$app->request->post()) && $model->load($postData)) {
            if ($model->save()) {
                return $this->redirect([
                    'view',
                    'id' => $model->id,
                ]);
            }
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
    public function actionDelete(int $id)
    {
        $model = $this->findModel($id);

        if ($model) {
            $model->delete();
        }

        return $this->redirect(['index']);
    }

    public function getContactGroups()
    {
        return $this->hasMany(ContactGroup::class, ['id' => 'group_id'])
                    ->viaTable('contact_has_group', ['contact_id' => 'id']);
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
        $model = Contact::find()
            ->andWhere([
                'id' => $id,
                ])
            ->userOwner()
            ->one();

        if ($model) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
