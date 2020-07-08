<?php

use yii\web\View;
use yii\helpers\ArrayHelper;

$params = require __DIR__ . '/params.php';
$params['bsVersion'] = '4.x'; // this will set globally `bsVersion` to Bootstrap 4.x for all Krajee Extensions
$params['currency'] = 'USD';
$params['defaultScheme'] = 'https';

$settingValidations = require __DIR__ . '/setting_validations.php';
$params = array_merge($params, $settingValidations);
$db = require __DIR__ . '/db.php';
$common = require __DIR__ . '/common.php';

$config = [
    'id' => 'basic',
    'name' => 'OpenSourceWebsite',
    'defaultRoute' => 'guest/default',
    'layout' => 'adminlte-user',
    'basePath' => dirname(__DIR__),
    'bootstrap' => [
        'log',
        ['class' => 'app\components\LanguageSelector'],
        'maintenanceMode',
    ],
    'language' => 'en',
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm' => '@vendor/npm-asset',
    ],
    'modules' => [
        'guest' => [
            'class' => 'app\modules\guest\Module',
        ],
        'comment' => [
            'class' => 'app\modules\comment\Module',
        ],
        'bot' => [
            'class' => 'app\modules\bot\Module',
        ],
    ],
    'components' => [
        'assetManager' => [
            'class' => 'app\components\AssetManager',
            'linkAssets' => true,
            'appendTimestamp' => true,
            'bundles' => [
                'yii\bootstrap4\BootstrapAsset' => [
                    'css' => ['//cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.4.1/css/bootstrap.min.css'],
                ],
                'yii\bootstrap4\BootstrapPluginAsset' => [
                    'js' => [
                        '//cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.4.1/js/bootstrap.bundle.min.js',
                    ],
                ],
                'yii\web\JqueryAsset' => [
                    'js' => [
                        '//cdnjs.cloudflare.com/ajax/libs/jquery/3.5.0/jquery.min.js',
                        '//cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.4.1/jquery.easing.min.js',
                    ],
                    'jsOptions' => ['position' => View::POS_HEAD],
                ],
                'yii\jui\JuiAsset' => [
                    'css' => [],
                    'js' => ['//cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js'],
                ],
            ],
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => true,
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            // send all mails to a file by default. You have to set
            // 'useFileTransport' to false and configure a transport
            // for the mailer to send real emails.
            'useFileTransport' => YII_ENV_DEV,
        ],
        'log' => [
            'flushInterval' => 1,
            'targets' => [
                'file' => [
                    'class' => 'yii\log\FileTarget',
                    'exportInterval' => 1,
                    'logFile' => '@runtime/logs/web.log',
                    'levels' => ['error'],
                    'logVars' => [],
                    'except' => [
                        'yii\web\HttpException:400',
                        'yii\web\HttpException:403',
                        'yii\web\HttpException:404',
                        'yii\web\HttpException:429',
                        'yii\i18n\PhpMessageSource',
                    ],
                ],
                'bad-requests' => [
                    'class' => 'yii\log\FileTarget',
                    'exportInterval' => 1,
                    'categories' => ['yii\web\HttpException:400'],
                    'logFile' => '@runtime/logs/bad-requests.log',
                    'logVars' => [],
                ],
            ],
        ],
        'formatter' => [
            'class' => 'yii\i18n\Formatter',
            'nullDisplay' => '',
            'datetimeFormat' => 'php:Y-m-d H:i:s',
            'dateFormat' => 'php:Y-m-d',
            'sizeFormatBase' => 1000,
        ],
        'session' => [
            'class' => 'yii\web\Session',
            'gCProbability' => 1,
        ],
        'maintenanceMode' => [
            'class' => '\brussens\maintenance\MaintenanceMode',
            'layoutPath' => 'maintenance',
            'viewPath' => '/maintenance/index',
            'enabled' => false,
            'statusCode' => 503,
        ],
        'db' => $db,
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => require(__DIR__ . '/routes.php'),
        ],
        'i18n' => [
            'translations' => [
                '*' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                ],
            ],
        ],
    ],
    'timeZone' => 'UTC',
    'params' => $params,
    'as ConfirmEmail' => [
        'class' => '\app\behaviors\ConfirmEmailBehavior',
    ],
];

//$config have more priority than $common
$config = ArrayHelper::merge($common, $config);

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];

    $config['components']['log']['targets']['file']['levels'] = ['error', 'warning'];
}

return $config;
