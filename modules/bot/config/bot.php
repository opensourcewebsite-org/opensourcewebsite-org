<?php

use app\modules\bot\components\CommandRouter;
use app\modules\bot\components\RequestMessage;
use app\modules\bot\components\ResponseMessage;
use app\modules\bot\components\BotClient;

return [
    'components' => [
        'commandRouter' => [
            'class' => CommandRouter::className(),
            'invalidRouteRedirect' => 'default/command-not-found',
            'rules' => [
                '/my_language_<language:\w+>' => 'my_language/index',
                '@language_list' => 'my_language/language-list',
                '@language_list_<page:\d+>' => 'my_language/language-list',

				'/my_currency_<currency:\w+>' => 'my_currency/index',
                '@currency_list' => 'my_currency/currency-list',
                '@currency_list_<page:\d+>' => 'my_currency/currency-list',

				'/<controller:\w+>' => '<controller>/index',
                '/<controller:\w+> <text:.+>' => '<controller>/index',
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
