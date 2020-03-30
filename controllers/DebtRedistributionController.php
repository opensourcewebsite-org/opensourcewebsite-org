<?php

namespace app\controllers;

use app\models\DebtRedistributionForm;
use Yii;
use yii\filters\AccessControl;
use yii\filters\AjaxFilter;
use yii\helpers\Html;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * DebtRedistibutionController implements the CRUD actions for DebtRedistribution model.
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
            'verbs' => [
                'class'   => VerbFilter::className(),
                'actions' => [
                    'save' => ['POST'],
                ],
            ],
            'ajax' => [
                'class' => AjaxFilter::class,
                'only'  => ['save'],
            ],
        ];
    }

    /**
     * @param null|int $id
     *
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionSave($id = null)
    {
        $model = DebtRedistributionForm::getModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->addFlash('success', Yii::t('app', 'Success'));
            return $this->asJson(['success' => true]);
        }

        $validation = [];
        foreach ($model->getErrors() as $attribute => $errors) {
            $validation[Html::getInputId($model, $attribute)] = $errors;
        }

        return $this->asJson(['validation' => $validation]);
    }
}
