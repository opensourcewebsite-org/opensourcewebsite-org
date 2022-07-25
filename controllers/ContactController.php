<?php

namespace app\controllers;

use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use yii\web\Response;

use app\components\Controller;
use app\models\Contact;
use app\models\ContactGroup;
use app\models\DebtRedistribution;
use app\models\scenarios\Contact\UpdateGroupsByIdsScenario;
use app\models\search\DebtRedistributionSearch;
use app\models\User;
use app\repositories\ContactRepository;

class ContactController extends Controller
{
    public ContactRepository $contactRepository;

    public function __construct()
    {
        parent::__construct(...func_get_args());

        $this->contactRepository = new ContactRepository();
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
        $contact = $this->contactRepository->findContact($id);

        $searchModel  = new DebtRedistributionSearch();
        $dataProvider = $searchModel->search($contact, Yii::$app->request->queryParams);

        return $this->render('view', [
            'dataProvider' => $dataProvider,
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
            if (($id == $this->user->id) || ($id == $this->user->username)) {
                return $this->run('user/account');
            }

            $user = User::findByUsername($id) ?: User::findById($id);

            if ($user) {
                $contact = $user->contact ?: $user->newContact;

                $searchModel  = new DebtRedistributionSearch();
                $dataProvider = $searchModel->search($contact, Yii::$app->request->queryParams);

                return $this->render('view', [
                    'dataProvider' => $dataProvider,
                    'contact' => $contact,
                    'user' => $user,
                ]);
            }
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    public function actionAddDebtTransferLimit(int $linkUserId)
    {
        if (Yii::$app->request->isPost && ($postData = Yii::$app->request->post())) {
            $debtRedistribution = DebtRedistribution::find()
                ->where([
                    'user_id' => $this->user->id,
                    'link_user_id' => $linkUserId,
                    'currency_id' => $postData['DebtRedistribution']['currency_id'],
                ])
                ->one();

            $debtRedistribution = $debtRedistribution ?? new DebtRedistribution();

            $debtRedistribution->setAttributes([
                'user_id' => $this->user->id,
                'link_user_id' => $linkUserId,
                'currency_id' => $postData['DebtRedistribution']['currency_id'],
                'max_amount' => $postData['DebtRedistribution']['max_amount'],
            ]);

            if ($debtRedistribution->save()) {
                return $this->redirect([
                    'view-user',
                    'id' => $debtRedistribution->link_user_id,
                ]);
            }
        } else {
            $debtRedistribution = new DebtRedistribution();
        }

        $renderParams = [
            'model' => $debtRedistribution,
        ];

        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('modals/add-debt-transfer-limit', $renderParams);
        } else {
            return $this->render('modals/add-debt-transfer-limit', $renderParams);
        }
    }

    public function actionChangeDebtTransferLimit(int $id)
    {
        $debtRedistribution = DebtRedistribution::find()
            ->where([
                'id' => $id,
                'user_id' => $this->user->id,
            ])
            ->one();

        if ($debtRedistribution) {
        }

        if (Yii::$app->request->isPost && ($postData = Yii::$app->request->post())) {
            $debtRedistribution->setAttributes([
                'max_amount' => $postData['DebtRedistribution']['max_amount'],
            ]);

            if ($debtRedistribution->save()) {
                return $this->redirect([
                    'view-user',
                    'id' => $debtRedistribution->link_user_id,
                ]);
            }
        }

        $renderParams = [
            'user' => $this->user,
            'model' => $debtRedistribution,
        ];

        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('modals/change-debt-transfer-limit', $renderParams);
        } else {
            return $this->render('modals/change-debt-transfer-limit', $renderParams);
        }
    }

    /**
     * @param int $id
     *
     * @return mixed
     * @throws NotFoundHttpException
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionDeleteDebtTransferLimit(int $id)
    {
        $debtRedistribution = DebtRedistribution::find()
            ->where([
                'id' => $id,
                'user_id' => $this->user->id,
            ])
            ->one();

        if ($debtRedistribution) {
            $linkUserId = $debtRedistribution->link_user_id;

            $debtRedistribution->delete();
        }

        if (isset($linkUserId)) {
            return $this->redirect([
                'view-user',
                'id' => $linkUserId,
            ]);
        } else {
            return $this->redirect(['index']);
        }
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
        $model = new ContactGroup();

        if (Yii::$app->request->isPost && ($postData = Yii::$app->request->post()) && $model->load($postData)) {
            $model->user_id = $this->user->id;

            if ($model->save()) {
                return $this->redirect(['contact/group']);
            }
        }

        $renderParams = [
            'model' => $model,
        ];

        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('group/create', $renderParams);
        } else {
            return $this->render('group/create', $renderParams);
        }
    }

    public function actionCreateGroupAjax(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $model = new ContactGroup();

        if (Yii::$app->request->isPost && ($postData = Yii::$app->request->post()) && $model->load($postData)) {
            $model->user_id = $this->user->id;

            if ($model->save()) {
                return ['success' => true, 'id' => $model->id, 'name' => $model->name];
            }
        }

        return ['success' => false];
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

    public function actionUpdateGroups(int $id = null, int $linkUserId = null)
    {
        $contact = Contact::findOne([
            'id' => $id,
            'user_id' => $this->user->id,
        ]);

        if (!$contact) {
            $linkUser = User::findOne($linkUserId);

            if (!$linkUser) {
                return $this->redirect(['index']);
            }

            $contact = Contact::findOne([
                'link_user_id' => $linkUser->id,
                'user_id' => $this->user->id,
            ]);

            if (!$contact) {
                $contact = new Contact();
                $contact->user_id = $this->user->id;
                $contact->link_user_id = $linkUser->id;
                $contact->save(false);
            }
        }

        if (Yii::$app->request->isPost && ($postData = Yii::$app->request->post()) && $contact->load($postData)) {
            if ($contact->validate(['groupIds'])) {
                (new UpdateGroupsByIdsScenario($contact))->run();

                return $this->redirect([
                    'view',
                    'id' => $contact->id,
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
        $model->scenario = 'form';

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
     * @param int|null $id
     * @param int|null $linkUserId
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate(int $id = null, int $linkUserId = null)
    {
        $model = Contact::findOne([
            'id' => $id,
            'user_id' => $this->user->id,
        ]);

        if (!$model) {
            $linkUser = User::findOne($linkUserId);

            if (!$linkUser) {
                return $this->redirect(['index']);
            }

            $model = Contact::findOne([
                'link_user_id' => $linkUser->id,
                'user_id' => $this->user->id,
            ]);

            if (!$model) {
                $model = new Contact();
                $model->user_id = $this->user->id;
                $model->link_user_id = $linkUser->id;
                $model->save(false);
            }
        }

        $model->scenario = 'form';

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
        $model = $this->contactRepository->findContact($id);

        if ($model) {
            $model->delete();
        }

        return $this->redirect(['index']);
    }
}
