<?php
declare(strict_types=1);

namespace app\controllers;

use app\models\Company;
use app\models\CompanyUser;
use app\models\scenarios\CompanyUser\DeleteCompanyScenario;
use app\models\search\CompanyUserSearch;
use app\models\User;
use Yii;
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

            return $this->redirect(['view', 'id' => $companyUserModel->id]);
        }

        return $this->render('create', [
                'companyModel' => $companyModel,
                'companyUserModel' => $companyUserModel
            ]);
    }

    public function actionCreateAjax() {
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
        return $this->renderAjax('_form', ['companyModel' => $companyModel]);
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

            return $this->redirect(['view', 'id' => $companyUserModel->id]);
        }

        return $this->render('update', [
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

    public function actionView(int $id): string
    {
        return $this->render('view', [
            'model' => $this->findCompanyUserModelById($id),
        ]);
    }

    public function actionDelete(int $id): Response
    {
        $companyUserModel = $this->findCompanyUserModelById($id);

        $scenario = new DeleteCompanyScenario($companyUserModel->company);

        if ($scenario->run()) {
            Yii::$app->session->setFlash('success', Yii::t('app','Company Deleted'));
            return $this->redirect('/company-user/index');
        }

        Yii::$app->session->setFlash('danger', Yii::t('app', $scenario->getFirstError()));
        return $this->redirect(['/company-user/update', 'id' => $companyUserModel->id]);
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
