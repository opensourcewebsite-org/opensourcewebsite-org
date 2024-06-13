<?php

namespace app\controllers;

use Yii;
use yii\data\Pagination;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\web\Controller;

class ExamplesController extends Controller
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

    // https://www.php.net/manual/en/function.phpinfo.php
    public function actionPhpInfo(): string
    {
        return $this->render('php-info');
    }

    // https://dev.mysql.com/doc/refman/8.0/en/server-system-variables.html
    // https://dev.mysql.com/doc/refman/8.0/en/server-status-variables.html
    public function actionMysqlInfo(): string
    {
        $mysqlVars = Yii::$app->db->createCommand('SHOW GLOBAL VARIABLES')->queryAll();
        $mysqlVars = ArrayHelper::map($mysqlVars, 'variable_name', 'value');

        return $this->render('mysql-info', [
            'mysqlVars' => $mysqlVars
        ]);
    }

    // https://www.yiiframework.com/doc/guide/2.0/en/db-migrations
    public function actionMigrations(): string
    {
        $query = (new Query())
            ->select([
                '*',
            ])
            ->from('{{%migration}}')
            ->orderBy([
                'apply_time' => SORT_DESC,
            ]);

        $countQuery = clone $query;
        $pages = new Pagination(['totalCount' => $countQuery->count()]);
        $models = $query->offset($pages->offset)
            ->limit($pages->limit)
            ->all();

        return $this->render('migration', [
            'models' => $models,
            'pages' => $pages,
        ]);
    }
}
