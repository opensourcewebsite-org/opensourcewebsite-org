<?php

use app\modules\bot\components\CommandRouteResolver;
use app\modules\bot\components\request\CallbackQueryCommandResolver;
use app\modules\bot\components\request\MessageCommandResolver;
use app\modules\bot\components\request\SystemMessageCommandResolver;
use app\modules\bot\components\request\AliasCommandResolver;

return [
    'components' => [
        'commandRouteResolver' => [
            'class' => CommandRouteResolver::class,
            'rules' => [
                '/help' => 'hello/index',
                '/<controller:\w+>__<action:\w+>(@<botname:[\w_]+bot>)?(\?<query:(&?\w+=[^&]*)*>)?( <message:.+>)?' => '<controller>/<action>',
                '/<controller:\w+>(@<botname:[\w_]+bot>)?(\?<query:(&?\w+=[^&]*)*>)?( <message:.+>)?' => '<controller>/index',
            ],
            'commandResolvers' => [
                new SystemMessageCommandResolver(),
                new AliasCommandResolver(),
                new MessageCommandResolver(),
                new CallbackQueryCommandResolver(),
            ],
        ],
    ],
];
