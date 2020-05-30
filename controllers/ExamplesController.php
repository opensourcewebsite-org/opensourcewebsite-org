<?php

namespace app\controllers;

use yii\filters\AccessControl;
use yii\web\Controller;
use Yii;
use yii\helpers\ArrayHelper;

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

    // https://www.php.net/manual/en/function.phpinfo.php
    public function actionPhpInfo()
    {
        return $this->render('php-info');
    }

    // https://dev.mysql.com/doc/refman/8.0/en/server-system-variables.html
    // https://dev.mysql.com/doc/refman/8.0/en/server-status-variables.html
    public function actionMysqlInfo()
    {
        $mysqlvars = Yii::$app->db->createCommand('SHOW GLOBAL VARIABLES')->queryAll();
        $mysqlvars = ArrayHelper::map($mysqlvars, 'Variable_name', 'Value');

        return $this->render('mysql-info', [
            'mysqlvars' => $mysqlvars
        ]);
    }
}
