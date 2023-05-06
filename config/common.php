<?php

use app\models\matchers\ModelLinker;

return [
    'name' => 'OpenSourceWebsite',
    'language' => 'en',
    'container' => [
        'definitions' => [
            ModelLinker::class => ModelLinker::class
        ]
    ],
    'components' => [
        'log' => [
            'targets' => [
                'mail' => [
                    'class' => 'yii\log\EmailTarget',
                    'exportInterval' => 1,
                    'enabled' => !empty($params['securityEmail']) && getenv('YII_ENV') !== 'dev' && getenv('YII_DEBUG') !== true,
                    'levels' => ['error'],
                    'logVars' => [],
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
        'mutex' => [
            'class' => 'yii\mutex\MysqlMutex',
        ]
    ],
];
