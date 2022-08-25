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
                '/<controller:\w+>__<action:\w+>(@<botname:[\w_]+bot>)?(\?<query:(&?\w+=[^&]*)*>)?( <message:.+>)?' => '<controller>/<action>',
                '/<controller:\w+>(@<botname:[\w_]+bot>)?(\?<query:(&?\w+=[^&]*)*>)?( <message:.+>)?' => '<controller>/index',
            ],
            'controllers' => [
                1 => 'hello',
                2 => 'message',
                3 => 'system_message',
                4 => 'premium_members'
            ],
            'actions' => [
                1 => 'index',
                2 => 'list',
                3 => 'view',
                4 => 'create',
                5 => 'edit',
                6 => 'update',
                7 => 'delete',
            ],
        ],
    ],
];

return $config;
