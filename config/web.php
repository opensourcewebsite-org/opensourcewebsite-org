<?php

use yii\web\View;
use yii\log\EmailTarget;

$params = require __DIR__ . '/params.php';
$settingValidations = require __DIR__ . '/setting_validations.php';
$params = array_merge($params, $settingValidations);
$db = require __DIR__ . '/db.php';

$config = [
    'id' => 'basic',
    'name' => 'OpenSourceWebsite',
    'layout' => 'adminlte-main',
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
        'comment' => [
            'class' => 'app\modules\comment\Module'
        ],
    ],
    'components' => [
        'assetManager' => [
            'class' => 'app\components\AssetManager',
            'linkAssets' => true,
            'appendTimestamp' => true,
            'bundles' => [
                'yii\bootstrap\BootstrapAsset' => [
                    'css' => ['plugins/bootstrap/css/bootstrap.css'],
                    'js' => ['plugins/popper/umd/popper.min.js'],
                    'sourcePath' => '@vendor/almasaeed2010/adminlte',
                ],
                'yii\bootstrap\BootstrapThemeAsset' => [
                    'css' => [],
                ],
                'yii\bootstrap\BootstrapPluginAsset' => [
                    'js' => ['plugins/bootstrap/js/bootstrap.js'],
                    'sourcePath' => '@vendor/almasaeed2010/adminlte',
                ],
                'yii\web\JqueryAsset' => [
                    'js' => ['//ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js'],
                    'jsOptions' => ['position' => View::POS_HEAD],
                ],
            ],
        ],
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'je4dpj7-SEqGW0z6eo4nc8ezzyLGYwNm',
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
            'targets' => [
                'file' => [
                    'class' => 'yii\log\FileTarget',
                    'logFile' => '@runtime/logs/web.log',
                    'levels' => ['error', 'warning'],
                    'logVars' => ['_GET', '_POST', '_FILES'],
                    'maxFileSize' => 1024,
                    'maxLogFiles' => 10,
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
                    'categories' => ['yii\web\HttpException:400'],
                    'logFile' => '@runtime/logs/bad-requests.log',
                ],
                'mail' => [
                    'class' => EmailTarget::class,
                    'enabled' => isset($params['securityEmail']) && $params['securityEmail'] && getenv('YII_ENV') !== 'dev' && getenv('YII_DEBUG') !== true,
                    'levels' => ['error'],
                    'logVars' => ['_GET', '_POST', '_FILES'],
                    'message' => [
                        'from' => $params['adminEmail'],
                        'subject' => 'OpenSourceWebsite bug log',
                        'to' => $params['securityEmail'],
                    ],
                    'except' => [
                        'yii\web\HttpException:400',
                        'yii\web\HttpException:403',
                        'yii\web\HttpException:404',
                        'yii\web\HttpException:429',
                        'yii\i18n\*',
                    ],
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
            'rules' => [
                '<action:(signup|login|participation|donate|road-map|team|terms-of-use|privacy-policy|account)>' => 'site/<action>',
                'wikipedia-pages' => 'wikipedia-pages/index',
                'wikinews-pages' => 'wikinews-pages/index',
                'wikinews-pages/create' => 'wikinews-pages/create',
                'cron-job' => 'cron-job/index',
                'cron-job/view/<id:[\d]+>' => 'cron-job/view',
                'referrals' => 'referrals/index',
                'wikipedia-page/view/<code>/<all>' => 'wikipedia-pages/view',
                'wikipedia-page/view/<code>' => 'wikipedia-pages/view',
                'wikipedia-page/recommended/<code>' => 'wikipedia-pages/recommended',
                'invite/<id>' => 'site/invite',
                'webhook/telegram/<token>' => 'webhook/telegram',
                'website-settings' => 'setting/index',
                'support-groups/clients-languages/<id:[\d]+>' => 'support-groups/clients-languages',
                'support-groups/clients-list/<id:[\d]+>/<language:[\w]+>' => 'support-groups/clients-list',
                'support-groups/clients-list/<id:[\d]+>' => 'support-groups/clients-list',
                'support-groups/clients-view/<id:[\d]+>' => 'support-groups/clients-view',
                'u/<id>' => 'user/profile',
//              '<action:(design-list|design-add|design-edit|design-view)>' => 'moqup/<action>',
            ],
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
}

return $config;
