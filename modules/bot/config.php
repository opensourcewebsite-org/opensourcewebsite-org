<?php

use app\modules\bot\components\CommandRouteResolver;
use app\modules\bot\components\request\SystemMessageRequestHandler;
use app\modules\bot\components\request\MessageRequestHandler;
use app\modules\bot\components\request\CallbackQueryRequestHandler;
use app\modules\bot\components\request\LocationRequestHandler;

return [
    'components' => [
        'commandRouteResolver' => [
            'class' => CommandRouteResolver::class,
            'rules' => [
                '/hello' => 'start/index',

                '/companies <page:\d+>' => 'companies/index',
                '/<controller:\w+>_update <id:\d+>' => '<controller>/update',
                '/<controller:\w+>_create <id:\d+>' => '<controller>/create',
                '/<controller:\w+>_set_<property:\w+>( <id:\d+>)?' => '<controller>/set-property',
                '/<controller:\w+>_show <id:\d+>( <page:\d+>)?' => '<controller>/show',

                '/<controller:\w+>__<action:\w+>(\?<query:(&?\w+=[^&]*)*>)?( <message:.+>)?' => '<controller>/<action>',
                '/<controller:\w+>(\?<query:(&?\w+=[^&]*)*>)?( <message:.+>)?' => '<controller>/index',
            ],
            'requestHandlers' => [
                new SystemMessageRequestHandler(),
                new MessageRequestHandler(),
                new CallbackQueryRequestHandler(),
                new LocationRequestHandler(),
            ],
        ],
    ],
];
