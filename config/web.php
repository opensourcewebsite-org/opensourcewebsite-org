<?php

use yii\base\Event;
use yii\helpers\ArrayHelper;
use yii\web\Application;
use yii\web\View;

$params = require __DIR__ . '/params.php';
$params['bsVersion'] = '4.x'; // this will set globally `bsVersion` to Bootstrap 4.x for all Krajee Extensions
$params['currency'] = 'USD';
$params['defaultScheme'] = 'https';

$db = require __DIR__ . '/db.php';
$common = require __DIR__ . '/common.php';
$events = require __DIR__ . "/events.php";

$config = [
    'id' => 'basic',
    'defaultRoute' => 'site/login',
    'layout' => 'adminlte-user',
    'basePath' => dirname(__DIR__),
    'bootstrap' => [
        'log',
        [
            'class' => 'app\components\LanguageSelector',
        ],
        'maintenanceMode',
    ],
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm' => '@vendor/npm-asset',
        '@bot' => '@app/modules/bot',
        '@api' => '@app/modules/api',
    ],
    'modules' => [
        'bot' => [
            'class' => 'app\modules\bot\Module',
        ],
        'api' => [
            'class' => 'app\modules\api\Module',
        ],
    ],
    'components' => [
        'request' => [
            'cookieValidationKey' => $params['cookieValidationKey'],
        ],
        'assetManager' => [
            'class' => 'app\components\AssetManager',
            'linkAssets' => true,
            'appendTimestamp' => true,
            'bundles' => [
                'yii\bootstrap4\BootstrapAsset' => [
                    'css' => [
                        '//cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.1/css/bootstrap.min.css',
                        // TODO '//cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.1.3/css/bootstrap.min.css',
                    ],
                ],
                'yii\bootstrap4\BootstrapPluginAsset' => [
                    'js' => [
                        '//cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.1/js/bootstrap.bundle.min.js',
                        // TODO '//cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.1.3/js/bootstrap.bundle.min.js',
                    ],
                ],
                'yii\web\JqueryAsset' => [
                    'sourcePath' => null,
                    'js' => [
                        '//cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js',
                        '//cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.4.1/jquery.easing.min.js',
                    ],
                    'jsOptions' => [
                        'position' => View::POS_END,
                    ],
                ],
                'yii\jui\JuiAsset' => [
                    'css' => [],
                    'js' => [
                        '//cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js',
                    ],
                ],
                'dosamigos\leaflet\LeafLetAsset' => [
                    'sourcePath' => null,
                    'css' => [
                        '//cdnjs.cloudflare.com/ajax/libs/leaflet/1.8.0/leaflet.css',
                    ],
                    'js' => [
                        '//cdnjs.cloudflare.com/ajax/libs/leaflet/1.8.0/leaflet-src.js',
                    ],
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
            'rules' => require (__DIR__ . '/routes.php'),
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
];

//$config have more priority than $common
$config = ArrayHelper::merge($common, array_merge($config, $events));

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        // 'allowedIPs' => ['127.0.0.1', '::1', $_SERVER['REMOTE_ADDR']],
        // 'allowedIPs' => ['*'],
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        // 'allowedIPs' => ['127.0.0.1', '::1', $_SERVER['REMOTE_ADDR']],
        // 'allowedIPs' => ['*'],
    ];

    $config['components']['log']['targets']['file']['levels'] = ['error', 'warning'];
}

return $config;
