<?php

use app\modules\bot\components\ChannelRouteResolver;

$config = [
    'components' => [
        'commandRouteResolver' => [
            'class' => ChannelRouteResolver::class,
            'rules' => [
                '/<controller:\w+>__<action:\w+>(@<botname:[\w_]+bot>)?(\?<query:(&?\w+=[^&]*)*>)?( <message:.+>)?' => '<controller>/<action>',
                '/<controller:\w+>(@<botname:[\w_]+bot>)?(\?<query:(&?\w+=[^&]*)*>)?( <message:.+>)?' => '<controller>/index',
            ],
        ],
    ],
];

return $config;
