<?php

return [
    'id' => 'api',
    'layout' => false,
    'defaultRoute' => 'default/index',
    'components' => [
        'urlManager' => [
            'class' => 'yii\web\UrlManager',
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => require (__DIR__ . '/routes.php'),
        ],
        'response' => [
            'class' => 'yii\web\Response',
            'on beforeSend' => function ($event) {
                $response = $event->sender;

                if ($response->data !== null) {
                    $response->data = [
                        'success' => $response->isSuccessful,
                        'data' => $response->data,
                    ];

                    $response->statusCode = 200;
                }
            },
        ],
        'cors' => [
            'class' => 'yii\filters\Cors',
            'cors' => [
                // Allow sources (domains)
                'Origin' => ['*'],
                // Allow methods
                'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'],
                // Allow headers
                'Access-Control-Request-Headers' => ['*'],
                // Allow credentials (cookies, authorization headers, etc.) to be exposed to the browser
                'Access-Control-Allow-Credentials' => true,
                // Allow OPTIONS caching, sec
                'Access-Control-Max-Age' => 60 * 60,
                // Allow the X-Pagination-Current-Page header to be exposed to the browser
                'Access-Control-Expose-Headers' => ['X-Pagination-Current-Page'],
            ],
        ],
        // TODO error handler
        'errorHandler' => [
            'class' => 'yii\web\errorHandler',
            'errorAction' => 'api/error/index',
        ],
    ],
];
