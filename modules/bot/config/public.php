<?php

use app\modules\bot\components\CommandRouteResolver;
use app\modules\bot\components\request\CallbackQueryCommandResolver;
use app\modules\bot\components\request\MessageCommandResolver;
use app\modules\bot\components\request\SystemMessageCommandResolver;

return [
    'components' => [
        'commandRouteResolver' => [
            'class' => CommandRouteResolver::class,
            'rules' => [
                '/<controller:\w+>__<action:\w+>(\?<query:(&?\w+=[^&]*)*>)?( <message:.+>)?' => '<controller>/<action>',
                '/<controller:\w+>(\?<query:(&?\w+=[^&]*)*>)?( <message:.+>)?' => '<controller>/index',
            ],
            'commandResolvers' => [
                new SystemMessageCommandResolver(),
                new MessageCommandResolver(),
                new CallbackQueryCommandResolver(),
            ],
        ],
    ],
];
