<?php

use app\modules\bot\components\CommandRouteResolver;
use app\modules\bot\components\request\CallbackQueryCommandResolver;
use app\modules\bot\components\request\MessageCommandResolver;
use app\modules\bot\components\request\LocationCommandResolver;
use app\modules\bot\components\request\Request;

return [
    'components' => [
        'commandRouteResolver' => [
            'class' => CommandRouteResolver::class,
            'rules' => [
                '/hello' => 'start/index',
                '/<controller:\w+>__<action:\w+>(\?<query:(&?\w+=[^&]*)*>)?( <message:.+>)?' => '<controller>/<action>',
                '/<controller:\w+>(\?<query:(&?\w+=[^&]*)*>)?( <message:.+>)?' => '<controller>/index',
            ],
            'commandResolvers' => [
                new LocationCommandResolver(),
                new MessageCommandResolver(),
                new CallbackQueryCommandResolver(),
            ],
        ],
    ],
];
