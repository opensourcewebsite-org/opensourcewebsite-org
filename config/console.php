<?php

use yii\faker\FixtureController;

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

$config = [
    'id' => 'basic-console',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log', 'maintenanceMode'],
    'controllerNamespace' => 'app\commands',
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm' => '@vendor/npm-asset',
        '@tests' => '@app/tests',
    ],
    'components' => [
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'maintenanceMode' => [
            'class' => '\brussens\maintenance\MaintenanceMode',
            'layoutPath' => 'maintenance',
            'viewPath' => '/maintenance/index',
            'enabled' => false,
            'statusCode' => 503,
        ],
        'log' => [
            'targets' => [
                'file' => [
                    'class' => 'yii\log\FileTarget',
                    'logFile' => '@runtime/logs/console.log',
                    'levels' => ['error', 'warning'],
                    'maxFileSize' => 1024,
                    'except' => [
                        'yii\web\HttpException:400',
                        'yii\web\HttpException:403',
                        'yii\web\HttpException:404',
                        'yii\web\HttpException:429',
                        'yii\i18n\PhpMessageSource',
                    ],
                ],
            ],
        ],
        'db' => $db,
        'pusher' => new \Pusher\Pusher(getenv('PUSHER_APP_KEY'), getenv('PUSHER_APP_SECRET'), getenv('PUSHER_APP_ID'), ['cluster' => 'ap2', 'useTLS' => true]),
    ],
    'params' => $params,
    'controllerMap' => [
        'fixture' => [
            'class' => FixtureController::class,
            'namespace' => 'app\tests\fixtures',
            'fixtureDataPath' => '@tests/fixtures/data',
            'templatePath' => '@tests/fixtures/templates',
        ],
    ],
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
    ];
}

return $config;
