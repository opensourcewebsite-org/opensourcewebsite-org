<?php

namespace app\controllers;

use yii\filters\AccessControl;
use yii\web\Controller;

class ExamplesController extends Controller
{
    public function behaviors()
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

    public function actionDashboard($id)
    {
        return $this->render('dashboard/' . $id);
    }

    public function actionWidgets()
    {
        return $this->render('widgets');
    }

    public function actionCharts($id)
    {
        return $this->render('charts/' . $id);
    }

    public function actionUiElements($id)
    {
        return $this->render('ui-elements/' . $id);
    }

    public function actionForms($id)
    {
        return $this->render('forms/' . $id);
    }

    public function actionTables($id)
    {
        return $this->render('tables/' . $id);
    }

    public function actionCalendar()
    {
        return $this->render('calendar');
    }

    public function actionGallery()
    {
        return $this->render('gallery');
    }
}
