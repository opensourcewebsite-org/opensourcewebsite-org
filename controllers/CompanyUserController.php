<?php
declare(strict_types=1);

namespace app\controllers;

use app\models\Company;
use app\models\CompanyUser;
use app\models\search\CompanyUserSearch;
use app\models\User;
use Yii;
use yii\filters\AccessControl;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class CompanyUserController extends Controller
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
        $companyUserModel->user_id = $user->id;
        $companyUserModel->user_role = CompanyUser::ROLE_OWNER;

        if (Yii::$app->request->isPost
            && $companyModel->load(Yii::$app->request->post())) {

            $transaction = Company::getDb()->beginTransaction();
            try {
                $companyModel->save();
                $companyUserModel->link('company', $companyModel);

            } catch (\Exception | \Throwable $e) {
                $transaction->rollBack();
                throw $e;
            }
            $transaction->commit();

            Yii::$app->session->setFlash('success', 'Saved Successfully');
            return $this->goBack();
        }

        return $this->renderAjax('createAjax', [
                'companyModel' => $companyModel,
                'companyUserModel' => $companyUserModel
            ]);
    }

    /**
     * @param int $id
     * @return string|Response
     * @throws NotFoundHttpException
     * @throws \Throwable
     */
    public function actionUpdate(int $id)
    {
        /** @var User $user */
        $user = Yii::$app->user->getIdentity();
        $companyUserModel = $this->findCompanyUserModelById($id);
        $companyModel = $companyUserModel->company;

        if (Yii::$app->request->isPost
            && $companyModel->load(Yii::$app->request->post())
            && $companyModel->save()) {

            Yii::$app->session->setFlash('success', 'Saved Successfully');
            return $this->goBack();
        }

        return $this->renderAjax('updateAjax', [
                'companyModel' => $companyModel,
                'companyUserModel' => $companyUserModel
            ]);
    }

    public function actionIndex(): string
    {

        $searchModel = new CompanyUserSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        Url::remember();

        return $this->render('index', ['searchModel' => $searchModel, 'dataProvider' => $dataProvider]);
    }


    private function findCompanyUserModelById(int $id): CompanyUser
    {
        /** @var CompanyUser $companyUser */
        if ($companyUser = CompanyUser::find()
            ->where(['id' => $id])
            ->andWhere(['user_id' => Yii::$app->user->getIdentity()->getId()])
            ->andWhere(['user_role' => CompanyUser::ROLE_OWNER])
            ->one()) {
            return $companyUser;
        }
        throw new NotFoundHttpException('Requested Page Not Found');
    }
}
