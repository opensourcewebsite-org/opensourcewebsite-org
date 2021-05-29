<?php
declare(strict_types=1);

namespace app\controllers;

use Yii;
use app\models\Company;
use app\models\CompanyUser;
use app\models\scenarios\CompanyUser\DeleteCompanyScenario;
use app\models\search\CompanyUserSearch;
use app\models\User;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
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
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                ],
            ]
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

            return $this->redirect(['view', 'id' => $companyModel->id]);
        }

        return $this->render('create', [
                'model' => $companyModel,
            ]);
    }

    public function actionCreateAjax()
    {
        /** @var User $user */
        $user = Yii::$app->user->getIdentity();

        $companyModel = new Company();

        $companyUserModel = new CompanyUser();
        $companyUserModel->user_id = $user->id;
        $companyUserModel->user_role = CompanyUser::ROLE_OWNER;

        if (Yii::$app->request->isPost) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            if ($companyModel->load(Yii::$app->request->post()) && $companyModel->save()) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                $companyUserModel->link('company', $companyModel);
                return $companyModel;
            }

            return ['errors' => $companyModel->errors];
        }

        return $this->renderAjax('_form', ['model' => $companyModel]);
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

        $companyModel = $this->findCompanyModelByIdAndCurrentUser($id);

        if (Yii::$app->request->isPost
            && $companyModel->load(Yii::$app->request->post())
            && $companyModel->save()) {

            return $this->redirect(['view', 'id' => $companyModel->id]);
        }

        return $this->render('update', [
                'model' => $companyModel,
            ]);
    }

    public function actionIndex(): string
    {
        $searchModel = new CompanyUserSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        Url::remember();

        return $this->render('index', ['searchModel' => $searchModel, 'dataProvider' => $dataProvider]);
    }

    public function actionView(int $id): string
    {
        return $this->render('view', [
            'model' => $this->findCompanyModelByIdAndCurrentUser($id),
        ]);
    }

    public function actionDelete(int $id): Response
    {
        $company = $this->findCompanyModelByIdAndCurrentUser($id);

        $scenario = new DeleteCompanyScenario($company);

        if ($scenario->run()) {
            return $this->redirect('/company-user/index');
        }

        Yii::$app->session->setFlash('danger', Yii::t('app', $scenario->getFirstError()));

        return $this->redirect(['/company-user/update', 'id' => $company->id]);
    }

    private function findCompanyModelByIdAndCurrentUser(int $id): Company
    {
        $user = Yii::$app->user->identity;

        /** @var Company $company */
        if ($company = Company::find()
            ->joinWith('companyUser cu')
            ->where(['company.id' => $id])
            ->andWhere(['cu.user_id' => $user->id])
            ->andWhere(['cu.user_role' => CompanyUser::ROLE_OWNER])
            ->one()) {
            return $company;
        }

        throw new NotFoundHttpException('Requested Page Not Found');
    }
}
