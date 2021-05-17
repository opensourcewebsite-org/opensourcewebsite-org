<?php
declare(strict_types=1);

namespace app\controllers;

use app\models\Company;
use app\models\CompanyUser;
use app\models\search\CompanyUserSearch;
use app\models\User;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class CompanyUserController extends Controller {

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
        ];
    }

    /**
     * @return string|Response
     * @throws \Throwable
     */
    public function actionCreate()
    {
        /** @var User $user */
        $user = Yii::$app->user->getIdentity();

        $companyModel = new Company();

        $companyUserModel = new CompanyUser();

        if (Yii::$app->request->isPost
            && $companyModel->load(Yii::$app->request->post())
            && $companyUserModel->load(Yii::$app->request->post())) {

            $transaction = Company::getDb()->beginTransaction();
            try {
                $companyModel->save();
                $companyUserModel->user_id = $user->id;
                $companyUserModel->link('company', $companyModel);

            } catch (\Exception | \Throwable $e) {
                $transaction->rollBack();
                throw $e;
            }
            $transaction->commit();
            Yii::$app->session->setFlash('Saved Successfully');
            return $this->redirect('index');
        }
        return $this->renderAjax(
            'create',
            [
                'companyModel' => $companyModel,
                'companyUserModel' => $companyUserModel
            ]
        );
    }

    public function actionUpdate(): string
    {

    }

    public function actionIndex(): string
    {
        $companies = Company::findAll([1=>1]);
        $searchModel = new CompanyUserSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', ['searchModel' => $searchModel, 'dataProvider' => $dataProvider]);
    }

}
