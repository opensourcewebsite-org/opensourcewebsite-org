<?php

namespace app\controllers;

use app\models\Contact;
use app\models\DebtRedistributionForm;
use app\models\search\DebtRedistributionSearch;
use Yii;
use yii\filters\AccessControl;
use yii\filters\AjaxFilter;
use yii\helpers\Html;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;

/**
 * DebtRedistributionController implements the CRUD actions for DebtRedistribution model.
 */
class DebtRedistributionController extends Controller
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
            'verbs'  => [
                'class'   => VerbFilter::className(),
                'actions' => [
                    'save'   => ['POST'],
                    'delete' => ['POST'],
                ],
            ],
            'ajax'   => [
                'class' => AjaxFilter::class,
                'only'  => ['save', 'index', 'form'],
            ],
        ];
    }

    /**
     * @param null $id
     *
     * @return Response
     * @throws NotFoundHttpException
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionSave($id = null)
    {
        $model = $id ? $this->findModel($id) : DebtRedistributionForm::factory();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->asJson(['success' => true]);
        }

        $validation = [];
        foreach ($model->getErrors() as $attribute => $errors) {
            $validation[Html::getInputId($model, $attribute)] = $errors;
        }

        return $this->asJson(['validation' => $validation]);
    }

    /**
     * @param int|Contact|null $contactId
     * @param int|null $linkUserId
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionIndex($contactId = null, $linkUserId = null)
    {
        if ($contactId instanceof Contact) {
            $contact = $contactId;
        } else {
            $contact = Contact::find()->forDebtRedistribution($contactId)->one();
        }

        if (!$contact) {
            throw new NotFoundHttpException('The requested page does not exist.');
        }

        $searchModel  = new DebtRedistributionSearch();
        $dataProvider = $searchModel->search($contact, Yii::$app->request->queryParams);

        return $this->renderAjax('index', [
            'dataProvider' => $dataProvider,
            'contact'      => $contact,
        ]);
    }

    /**
     * @param $id
     *
     * @return mixed
     * @throws NotFoundHttpException
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $model->delete();

        if (Yii::$app->request->isAjax) {
            return $this->actionIndex($model->contact);
        }

        return $this->redirect(Yii::$app->request->referrer);
    }

    /**
     * @param int|null $id
     * @param int|null $contactId
     *
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionForm($id = null, $contactId = null)
    {
        $model   = $id ? $this->findModel($id) : null;
        $contact = $contactId ? Contact::find()->forDebtRedistribution($contactId)->one() : null;

        if (!$model && !$contact) {
            throw new NotFoundHttpException('The requested page does not exist.');
        }

        return $this->renderAjax('form', [
            'model'   => $model,
            'contact' => $contact,
        ]);
    }

    /**
     * Finds the DebtRedistribution model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @param integer $id
     *
     * @return DebtRedistributionForm the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = DebtRedistributionForm::findModel($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
