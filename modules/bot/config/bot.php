<?php

use app\modules\bot\components\CommandRouter;
use app\modules\bot\components\RequestMessage;
use app\modules\bot\components\ResponseMessage;
use app\modules\bot\components\BotClient;

return [
    'components' => [
        'commandRouter' => [
            'class' => CommandRouter::className(),
            'invalidRouteRedirect' => 'error/command-not-found',
            'rules' => [
                '/my_language_<language:\w+>' => 'profile/language',
                '/my_currency_<currency:\w+>' => 'profile/currency',

                '/my_<action:\w+>' => 'profile/<action>',

                '/<controller:\w+>' => '<controller>/index',

                '@language_list' => 'profile/language-list',
                '@language_list_<page:\d+>' => 'profile/language-list',

                '@currency_list' => 'profile/currency-list',
                '@currency_list_<page:\d+>' => 'profile/currency-list',
            ],
        ],
        'requestMessage' => [
            'class' => RequestMessage::className(),
        ],
        'responseMessage' => [
            'class' => ResponseMessage::className(),
        ],
        'botClient' => [
            'class' => BotClient::className(),
        ],
    ],
];