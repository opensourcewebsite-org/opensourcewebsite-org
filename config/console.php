<?php

use yii\helpers\ArrayHelper;

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';
$common = require __DIR__ . '/common.php';

$config = [
    'id' => 'basic-console',
    'basePath' => dirname(__DIR__),
    'bootstrap' => [
        'log',
        'maintenanceMode',
    ],
    'controllerNamespace' => 'app\commands',
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm' => '@vendor/npm-asset',
        '@tests' => '@app/tests',
        '@bot' => '@app/modules/bot',
    ],
    'modules' => [
        'dataGenerator' => [
            'class' => 'app\modules\dataGenerator\Module',
        ],
        'bot' => [
            'class' => 'app\modules\bot\Module',
        ],
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
            'flushInterval' => 1,
            'targets' => [
                'file' => [
                    'class' => 'yii\log\FileTarget',
                    'exportInterval' => 1,
                    'logFile' => '@runtime/logs/console.log',
                    'levels' => ['error'],
                    'logVars' => [],
                    'except' => [
                        'yii\i18n\PhpMessageSource',
                    ],
                ],
            ],
        ],
        'db' => $db,
        'urlManager' => [
            'baseUrl' => $params['baseUrl'],
            'scriptUrl' => $params['baseUrl'],
            'enablePrettyUrl' => true,
            'showScriptName' => false,
        ],
        'i18n' => [
            'translations' => [
                '*' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                ],
            ],
        ],
        'settings' => [
            'class' => 'app\components\Setting',
        ],
    ],
    'timeZone' => 'UTC',
    'params' => $params,
    'controllerMap' => [
        'fixture' => [
            'class' => 'yii\faker\FixtureController',
            'namespace' => 'app\tests\fixtures',
            'fixtureDataPath' => '@tests/fixtures/data',
            'templatePath' => '@tests/fixtures/templates',
        ],
    ],
];

//$config have more priority than $common
$config = ArrayHelper::merge($common, $config);

if (YII_ENV_DEV) {
    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];

    $config['components']['log']['targets']['file']['levels'] = ['error', 'warning'];
}

return $config;
