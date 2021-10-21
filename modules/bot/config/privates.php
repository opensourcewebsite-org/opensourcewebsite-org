<?php

use app\modules\bot\components\PrivateRouteResolver;
use yii\helpers\ArrayHelper;

//$common = require __DIR__ . '/common.php';

$config = [
    'components' => [
        'commandRouteResolver' => [
            'class' => PrivateRouteResolver::class,
            'rules' => [
                '/start( <start:.+>)?' => 'start/index',
                '/hello' => 'start/index',
                '/sos' => 'start/index',
                '/my_rank' => 'my-rating/index',
                '/<controller:\w+>__<action:\w+>(\?<query:(&?\w+=[^&]*)*>)?( <message:.+>)?' => '<controller>/<action>',
                '/<controller:\w+>(\?<query:(&?\w+=[^&]*)*>)?( <message:.+>)?' => '<controller>/index',
            ],
        ],
    ],
];

//$config have more priority than $common
//$config = ArrayHelper::merge($common, $config);

return $config;
