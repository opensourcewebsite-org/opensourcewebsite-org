<?php

use app\modules\bot\components\GroupRouteResolver;

$config = [
    'components' => [
        'commandRouteResolver' => [
            'class' => GroupRouteResolver::class,
            'rules' => [
                '/start(@<botname:[\w_]+bot>)?( <message:.+>)?' => 'hello/index',
                '/help(@<botname:[\w_]+bot>)?( <message:.+>)?' => 'hello/index',
                '/my_face(@<botname:[\w_]+bot>)?( <message:.+>)?' => 'my-fake-face/index',
                '/my_cat(@<botname:[\w_]+bot>)?( <message:.+>)?' => 'my-fake-cat/index',
                '/my_art(@<botname:[\w_]+bot>)?( <message:.+>)?' => 'my-fake-art/index',
                '/my_horse(@<botname:[\w_]+bot>)?( <message:.+>)?' => 'my-fake-horse/index',
                '/<controller:\w+>__<action:\w+>(@<botname:[\w_]+bot>)?(\?<query:(&?\w+=[^&]*)*>)?( <message:.+>)?' => '<controller>/<action>',
                '/<controller:\w+>(@<botname:[\w_]+bot>)?(\?<query:(&?\w+=[^&]*)*>)?( <message:.+>)?' => '<controller>/index',
            ],
            'controllers' => [
                'hello',
                'message',
                'system_message',
                'tip',
                'premium_members',
                'id',
            ],
            'actions' => [
                'view',
                'index',
                'list',
                'create',
                'edit',
                'update',
                'delete',
            ],
        ],
    ],
];

return $config;
