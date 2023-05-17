<?php

use app\modules\bot\components\GroupRouteResolver;

$controllers = require __DIR__ . '/controllers.php';
$actions = require __DIR__ . '/actions.php';

$config = [
    'components' => [
        'commandRouteResolver' => [
            'class' => GroupRouteResolver::class,
            'rules' => [
                '/start(@<botname:[\w_]+bot>)?( <message:.+>)?' => 'hello/index',
                '/help(@<botname:[\w_]+bot>)?( <message:.+>)?' => 'hello/index',
                '/reload(@<botname:[\w_]+bot>)?( <message:.+>)?' => 'refresh/index',
                '/norepeat(@<botname:[\w_]+bot>)?( <message:.+>)?' => 'repeat/off',
                '/(thank|thanks|gift|gifts|donation|charity|reward|present|tips)(@<botname:[\w_]+bot>)?( <message:.+>)?' => 'tip/index',
                '/<controller:\w+>__<action:\w+>(@<botname:[\w_]+bot>)?(\?<query:(&?\w+=[^&]*)*>)?( <message:.+>)?' => '<controller>/<action>',
                '/<controller:\w+>(@<botname:[\w_]+bot>)?(\?<query:(&?\w+=[^&]*)*>)?( <message:.+>)?' => '<controller>/index',
            ],
            'controllers' => $controllers,
            'actions' => $actions,
        ],
    ],
];

return $config;
