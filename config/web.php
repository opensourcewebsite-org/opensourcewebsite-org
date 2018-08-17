<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

$config = [
    'id' => 'basic',
    'name' => 'OpenSourceWebsite',
    'layout'=>'adminlte-main',
    'basePath' => dirname(__DIR__),
    'bootstrap' => [
    	'log',
    	['class' => 'app\components\LanguageSelector'],
    ],
    'language' => 'en',
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm' => '@vendor/npm-asset',
    ],
    'components' => [
        'assetManager' => [
            'class' => 'app\components\AssetManager',
            'linkAssets' => true,
            'appendTimestamp' => true,
            'bundles' => [
                'yii\bootstrap\BootstrapAsset' => [
                    'css' => [
                        YII_ENV_DEV ? 'css/bootstrap.css' : 'css/bootstrap.min.css',
                    ],
                ],
                'yii\bootstrap\BootstrapThemeAsset' => [
                    'css' => [
                        YII_ENV_DEV ? 'css/bootstrap-theme.css' : 'css/bootstrap-theme.min.css',
                    ],
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
            ],
        ],
        'formatter' => [
            'class' => 'yii\i18n\Formatter',
            'nullDisplay' => '',
            'datetimeFormat' => 'php:Y-m-d H:i:s',
            'dateFormat' => 'php:Y-m-d',
            'sizeFormatBase' => 1000,
        ],
        'db' => $db,

        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                '<action:(signup|login|contact|donate|team|terms-of-use|privacy-policy|account)>' => 'site/<action>',
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
