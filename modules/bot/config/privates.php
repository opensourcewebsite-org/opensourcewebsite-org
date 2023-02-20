<?php

use app\modules\bot\components\PrivateRouteResolver;

//use yii\helpers\ArrayHelper;

//$common = require __DIR__ . '/common.php';
$controllers = require __DIR__ . '/controllers.php';
$actions = require __DIR__ . '/actions.php';

$config = [
    'components' => [
        'commandRouteResolver' => [
            'class' => PrivateRouteResolver::class,
            'rules' => [
                '/start( <start:.+>)?' => 'start/index',
                '/hello' => 'start/index',
                '/sos' => 'start/index',
                '/my_rank' => 'my-rating/index',
                '/group_reload(@<botname:[\w_]+bot>)?( <message:.+>)?' => 'group-refresh/index',
                '/<controller:\w+>__<action:\w+>(\?<query:(&?\w+=[^&]*)*>)?( <message:.+>)?' => '<controller>/<action>',
                '/<controller:\w+>(\?<query:(&?\w+=[^&]*)*>)?( <message:.+>)?' => '<controller>/index',
            ],
            'controllers' => $controllers,
            'actions' => $actions,
        ],
    ],
];

//$config have more priority than $common
//$config = ArrayHelper::merge($common, $config);

return $config;
