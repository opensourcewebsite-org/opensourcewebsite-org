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
                'telegram' => [
                    'class' => 'app\modules\bot\components\TelegramLogTarget',
                    'exportInterval' => 1,
                    'enabled' => !empty(getenv('PARAM_BOT_TOKEN')) && !empty(getenv('PARAM_BOT_OSW_LOGS_GROUP_ID')),
                    'levels' => ['error'],
                    'logVars' => [],
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
