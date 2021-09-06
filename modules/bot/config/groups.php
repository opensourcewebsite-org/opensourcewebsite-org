<?php

use app\modules\bot\components\GroupRouteResolver;

$config = [
    'components' => [
        'commandRouteResolver' => [
            'class' => GroupRouteResolver::class,
            'rules' => [
                '/help' => 'hello/index',
                '/my_face(@<botname:[\w_]+bot>)?' => 'my-fake-face/index',
                '/my_cat(@<botname:[\w_]+bot>)?' => 'my-fake-cat/index',
                '/my_art(@<botname:[\w_]+bot>)?' => 'my-fake-art/index',
                '/<controller:\w+>__<action:\w+>(@<botname:[\w_]+bot>)?(\?<query:(&?\w+=[^&]*)*>)?( <message:.+>)?' => '<controller>/<action>',
                '/<controller:\w+>(@<botname:[\w_]+bot>)?(\?<query:(&?\w+=[^&]*)*>)?( <message:.+>)?' => '<controller>/index',
            ],
        ],
    ],
];

return $config;
