<?php

namespace app\controllers;

use app\components\Controller;
use app\models\Currency;
use app\models\Debt;
use app\models\DebtBalance;
use app\models\forms\CreateDebtForm;
use app\models\User;
use Yii;
use yii\data\ActiveDataProvider;
use yii\data\Pagination;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class DebtController extends Controller
{
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    public function actionIndex(): string
    {
        $query = $this->user->getPendingDebts();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        return $this->render('pending', [
            'dataProvider' => $dataProvider,
            'user' => $this->user,
        ]);
    }

    public function actionDeposit(): string
    {
        $query = Currency::find()
            ->joinWith('debtBalances')
            ->andWhere([
                DebtBalance::tableName() . '.to_user_id' => $this->user->id,
            ])
            ->andWhere(['>', DebtBalance::tableName() . '.amount', 0])
            ->orderBy([
                Currency::tableName() . '.code' => SORT_ASC,
            ])
            ->groupBy(Currency::tableName() . '.id');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        return $this->render('deposit', [
            'dataProvider' => $dataProvider,
            'user' => $this->user,
        ]);
    }

    public function actionCurrencyDeposit($currencyId): string
    {
        $query = User::find()
            ->andWhere([
                'in',
                'id',
                DebtBalance::find()
                    ->select('from_user_id')
                    ->andWhere([
                        'to_user_id' => $this->user->id,
                        'currency_id' => $currencyId,
                    ])
                    ->andWhere(['>', 'amount', 0])
            ]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        return $this->render('currency-deposit', [
            'dataProvider' => $dataProvider,
            'user' => $this->user,
            'currency' => Currency::findOne($currencyId),
        ]);
    }

    public function actionCurrencyUserDeposit($currencyId, $counterUserId): string
    {
        $query = Debt::find()
            ->andWhere([
                'from_user_id' => [
                    $this->user->id,
                    $counterUserId,
                ],
                'to_user_id' => [
                    $this->user->id,
                    $counterUserId,
                ],
                'currency_id' => $currencyId,
            ])
            ->orderBy([
                'created_at' => SORT_DESC,
            ]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        return $this->render('currency-user-deposit', [
            'dataProvider' => $dataProvider,
            'user' => $this->user,
            'currency' => Currency::findOne($currencyId),
        ]);
    }

    public function actionCredit(): string
    {
        $query = Currency::find()
            ->joinWith('debtBalances')
            ->andWhere([
                DebtBalance::tableName() . '.from_user_id' => $this->user->id,
            ])
            ->andWhere(['>', DebtBalance::tableName() . '.amount', 0])
            ->orderBy([
                Currency::tableName() . '.code' => SORT_ASC,
            ])
            ->groupBy(Currency::tableName() . '.id');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        return $this->render('credit', [
            'dataProvider' => $dataProvider,
            'user' => $this->user,
        ]);
    }

    public function actionCurrencyCredit($currencyId): string
    {
        $query = User::find()
            ->andWhere([
                'in',
                'id',
                DebtBalance::find()
                    ->select('to_user_id')
                    ->andWhere([
                        'from_user_id' => $this->user->id,
                        'currency_id' => $currencyId,
                    ])
                    ->andWhere(['>', 'amount', 0])
            ]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        return $this->render('currency-credit', [
            'dataProvider' => $dataProvider,
            'user' => $this->user,
            'currency' => Currency::findOne($currencyId),
        ]);
    }

    public function actionCurrencyUserCredit($currencyId, $counterUserId): string
    {
        $query = Debt::find()
            ->andWhere([
                'from_user_id' => [
                    $this->user->id,
                    $counterUserId,
                ],
                'to_user_id' => [
                    $this->user->id,
                    $counterUserId,
                ],
                'currency_id' => $currencyId,
            ])
            ->orderBy([
                'created_at' => SORT_DESC,
            ]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        return $this->render('currency-user-credit', [
            'dataProvider' => $dataProvider,
            'user' => $this->user,
            'currency' => Currency::findOne($currencyId),
        ]);
    }

    public function actionArchive()
    {
        // TODO add data
        return $this->render('archive', [
            'user' => $this->user,
        ]);
    }

    /**
     * @return string|Response
     */
    public function actionCreate()
    {
        $formModel = new CreateDebtForm();
        $formModel->currency_id = $this->user->currency_id;

        $users = User::find()
            ->active()
            ->joinWith('contact')
            ->andWhere([
                'not',
                ['link_user_id' => null],
            ])
            ->all();

        if (Yii::$app->request->isPost && $formModel->load(Yii::$app->request->post()) && $formModel->validate()) {
            $model = new Debt();

            $model->setAttributes([
                'from_user_id' => $formModel->direction == Debt::DIRECTION_DEPOSIT ? $formModel->counter_user_id : $this->user->id,
                'to_user_id' => $formModel->direction == Debt::DIRECTION_DEPOSIT ? $this->user->id : $formModel->counter_user_id,
                'amount' => $formModel->amount,
                'currency_id' => $formModel->currency_id,
                'status' => $formModel->direction == Debt::DIRECTION_DEPOSIT ? Debt::STATUS_PENDING : Debt::STATUS_CONFIRM,
            ]);

            if ($model->save()) {
                return $this->redirect([
                    'currency-user-' . ($formModel->direction == Debt::DIRECTION_DEPOSIT ? 'deposit' : 'credit'),
                    'counterUserId' => $formModel->counter_user_id,
                    'currencyId' => $model->currency_id,
                ]);
            }
        }

        return $this->render('create', [
            'model' => $formModel,
            'users' => $users,
        ]);
    }

    public function actionConfirm(int $id): Response
    {
        $model = Debt::findOne($id);

        if (!$model) {
            throw new NotFoundHttpException();
        }

        if ($model->canConfirm()) {
            $model->confirm();
        }

        return $this->redirect(['index']);
    }

    /**
     * @param string $q
     * @return array
    */

    public function actionAjaxUsers($q = null)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $out = ['results'=>[]];

        $page = (int)Yii::$app->request->get('page');

        $userQuery = User::find()
        ->active()
        ->joinWith('contact')
        ->select(['user.id as id','user.username'])
        ->andWhere(['not',['link_user_id' => null]])
        ->orderBy([
            'user.username' => SORT_ASC,
        ]);

        if (!empty($q)) {
            $userQuery->andWhere(['like', 'user.username', $q]);
        }

        $countUserQuery = clone $userQuery;

        $pages = new Pagination(['pageSize'=>6, 'totalCount' => $countUserQuery->count()]);

        $users = $userQuery->offset($pages->offset)
            ->limit($pages->limit)
            ->all();

        if (!$users) {
            return $out;
        }

        foreach ($users as $user) {
            $out['results'][] = ['id' => $user->id, 'username' => $user->displayName];
        }

        if ($page < $pages->pageCount) {
            $out['pagination']['more'] = true;
        }

        return $out;
    }

    public function actionCancel(int $id): Response
    {
        $model = Debt::findOne($id);

        if (!$model) {
            throw new NotFoundHttpException();
        }

        if ($model->canCancel()) {
            $model->delete();
        }

        return $this->redirect(['index']);
    }
}
