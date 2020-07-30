<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use app\models\Debt;
use app\models\User;
use yii\web\Controller;
use app\models\Currency;
use yii\filters\VerbFilter;
use yii\data\ActiveDataProvider;
use yii\data\SqlDataProvider;
use yii\web\NotFoundHttpException;

/**
 * DebtController implements the CRUD actions for Debt model.
 */
class DebtController extends Controller
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
     * Lists all Debt models.
     * @return mixed
     */
    public function actionIndex()
    {
        if ((int)Debt::countStatusPending() === 0) {
            return $this->actionCurrent();
        }

        $userId = Yii::$app->user->id;
        $query = Debt::find()->andWhere(['to_user_id' => $userId, 'status' => Debt::STATUS_PENDING]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        return $this->render('new', [
            'direction' => Debt::DIRECTION_DEPOSIT,
            'dataProvider' => $dataProvider,
        ]);


        $userId = Yii::$app->user->id;
        $debtData = Debt::find()
            ->select([
                'currency_id',
                'depositPendingAmount' => 'IF((to_user_id = ' . $userId . ') && (status = ' . Debt::STATUS_PENDING . '), amount, 0)',
                'creditPendingAmount' => 'IF((from_user_id = ' . $userId . ') && (status = ' . Debt::STATUS_PENDING . '), amount, 0)',
                'depositConfirmedAmount' => 'IF((to_user_id = ' . $userId . ') && (status = ' . Debt::STATUS_CONFIRM . '), amount, 0)',
                'creditConfirmedAmount' => 'IF((from_user_id = ' . $userId . ') && (status = ' . Debt::STATUS_CONFIRM . '), amount, 0)',
            ])
            ->andWhere([
                'OR',
                ['from_user_id' => $userId],
                ['to_user_id' => $userId]
            ]);
        $dataProvider = new ActiveDataProvider([
            'query' => Debt::find()
                ->from(['debtData' => $debtData])
                ->select([
                    'currency_id',
                    'depositPending' => 'SUM(debtData.depositPendingAmount)',
                    'creditPending' => 'SUM(debtData.creditPendingAmount)',
                    'depositConfirmed' => 'SUM(debtData.depositConfirmedAmount)',
                    'creditConfirmed' => 'SUM(debtData.creditConfirmedAmount)',
                ])
                ->groupBy(['currency_id']),
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionCurrent()
    {
        $userId = Yii::$app->user->id;
        $debtData = Debt::find()
            ->select([
                'currency_id',
                'depositPendingAmount' => 'IF((to_user_id = ' . $userId . ') && (status = ' . Debt::STATUS_PENDING . '), amount, 0)',
                'creditPendingAmount' => 'IF((from_user_id = ' . $userId . ') && (status = ' . Debt::STATUS_PENDING . '), amount, 0)',
                'depositConfirmedAmount' => 'IF((to_user_id = ' . $userId . ') && (status = ' . Debt::STATUS_CONFIRM . '), amount, 0)',
                'creditConfirmedAmount' => 'IF((from_user_id = ' . $userId . ') && (status = ' . Debt::STATUS_CONFIRM . '), amount, 0)',
            ])
            ->andWhere([
                'OR',
                ['from_user_id' => $userId],
                ['to_user_id' => $userId]
            ]);
        $dataProvider = new ActiveDataProvider([
            'query' => Debt::find()
                ->from(['debtData' => $debtData])
                ->select([
                    'currency_id',
                    'depositPending' => 'SUM(debtData.depositPendingAmount)',
                    'creditPending' => 'SUM(debtData.creditPendingAmount)',
                    'depositConfirmed' => 'SUM(debtData.depositConfirmedAmount)',
                    'creditConfirmed' => 'SUM(debtData.creditConfirmedAmount)',
                ])
                ->groupBy(['currency_id']),
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionHistory()
    {
        // $userId = Yii::$app->user->id;
        // $query = ...
        // $dataProvider = new ActiveDataProvider([
        //     'query' => $query,
        // ]);
        // return $this->render('view', [
        //     'direction' => $direction,
        //     'currencyId' => $currencyId,
        //     'dataProvider' => $dataProvider,
        // ]);
    }

    /**
     * Displays a single Debt model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($direction, $currencyId)
    {
        $userId = Yii::$app->user->id;
        $query = Debt::find()
            ->select(['*', 'SUM(amount) totalAmount'])
            ->andWhere(['currency_id' => $currencyId]);
        if ((int) $direction === Debt::DIRECTION_DEPOSIT) {
            $query->andWhere(['to_user_id' => $userId])->groupBy('from_user_id');
        } else {
            $query->andWhere(['from_user_id' => $userId])->groupBy('to_user_id');
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        return $this->render('view', [
            'direction' => $direction,
            'currencyId' => $currencyId,
            'dataProvider' => $dataProvider,
        ]);
    }

    /*
    Display debt information by user
    */
    public function actionViewUser(int $userId, int $currencyId, int $direction)
    {
        $userDirection = ['to_user_id' => null, 'from_user_id' => null];

        if ($direction == Debt::DIRECTION_DEPOSIT) {
            $userDirection['to_user_id'] = Yii::$app->user->id;
            $userDirection['from_user_id'] = $userId;
        } else {
            $userDirection['from_user_id'] = Yii::$app->user->id;
            $userDirection['to_user_id'] = $userId;
        }

        $query = Debt::find()
        ->where(array_merge(['currency_id' => $currencyId], $userDirection));

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        return $this->render('view-user', [
            'direction' => $direction,
            'currencyId' => $currencyId,
            'dataProvider' => $dataProvider,
            'userId' => $userId
        ]);
    }

    /**
     * Creates a new Debt model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Debt();
        $model->scenario = Debt::SCENARIO_FORM;

        $user = User::find()
            ->joinWith('contact')
            ->active()
            ->andWhere(['NOT', ['link_user_id' => null]])
            ->all();

        if ($model->load(Yii::$app->request->post())) {
            $model->status = ($model->direction == Debt::DIRECTION_DEPOSIT ? Debt::STATUS_PENDING : Debt::STATUS_CONFIRM);
            if ($model->save()) {
                return $this->redirect(['view', 'direction' => $model->direction, 'currencyId' => $model->currency_id]);
            }
        }

        return $this->render('create', [
            'model' => $model,
            'user' => $user,
            'currency' => Currency::find()->all(),
        ]);
    }

    /**
     * Finds the Debt model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Debt the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Debt::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    public function actionConfirm($id, $direction, $currencyId)
    {
        $model = $this->findModel($id);
        $model->status = Debt::STATUS_CONFIRM;
        $model->save();

        return $this->redirect(['view', 'direction' => $direction, 'currencyId' => $currencyId]);
    }

    public function actionCancel($id, $direction, $currencyId)
    {
        $model = $this->findModel($id);
        if ($model->canCancelDebt()) {
            $model->delete();
        }

        return $this->redirect(['view', 'direction' => $direction, 'currencyId' => $currencyId]);
    }
}
