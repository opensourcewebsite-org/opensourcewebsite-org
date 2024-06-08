<?php

use app\modules\bot\components\ChannelRouteResolver;

$controllers = require __DIR__ . '/controllers.php';
$actions = require __DIR__ . '/actions.php';

$config = [
    'components' => [
        'commandRouteResolver' => [
            'class' => ChannelRouteResolver::class,
            'rules' => [
                '/<controller:\w+>__<action:\w+>(@<botname:[\w_]+bot>)?(\?<query:(&?\w+=[^&]*)*>)?( <message:.+>)?' => '<controller>/<action>',
                '/<controller:\w+>(@<botname:[\w_]+bot>)?(\?<query:(&?\w+=[^&]*)*>)?( <message:.+>)?' => '<controller>/index',
            ],
            'controllers' => $controllers,
            'actions' => $actions,
        ],
    ],
];

return $config;
