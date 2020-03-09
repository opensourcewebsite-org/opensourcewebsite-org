<?php

use app\modules\bot\components\CommandRouteResolver;
use app\modules\bot\components\request\SystemMessageRequestHandler;
use app\modules\bot\components\request\MessageRequestHandler;
use app\modules\bot\components\request\CallbackQueryRequestHandler;
use app\modules\bot\components\request\LocationRequestHandler;

return [
    'components' => [
        'commandRouteResolver' => [
            'class' => CommandRouteResolver::className(),
            'rules' => [
                '⚙️' => 'help/index',

                '/my_language_<language:\w+>' => 'my_language/index',
                '/language_list' => 'my_language/language-list',
                '/language_list_<page:\d+>' => 'my_language/language-list',

                '/my_currency_<currency:\w+>' => 'my_currency/index',
                '/currency_list' => 'my_currency/currency-list',
                '/currency_list_<page:\d+>' => 'my_currency/currency-list',

                '/set_email' => 'my_email/create',
                '/change_email' => 'my_email/update',
                '/merge_accounts' => 'my_email/merge-accounts',
                '/discard_merge_request <mergeAccountsRequestId:\d+>' => 'my_email/discard-merge-request',

                '/my_birthday_create' => 'my_birthday/create',
                '/my_birthday_update' => 'my_birthday/update',

                '/my_gender_update' => 'my_gender/update',
                '/my_gender_<gender:\w+>' => 'my_gender/index',

                '/update_location' => 'my_location/update',

                '/admin_filter_chat <chatId:\d+>' => 'admin_filter_chat',
                '/admin_filter_filterchat <chatId:\d+>' => 'admin_filter_filterchat',
                '/admin_filter_change_filter_mode <chatId:\d+>' => 'admin_filter_filterchat/update',
                '/admin_filter_change_filter_on <chatId:\d+>' => 'admin_filter_filterchat/status',
                '/admin_filter_whitelist <chatId:\d+>' => 'admin_filter_whitelist',
                '/admin_filter_blacklist <chatId:\d+>' => 'admin_filter_blacklist',
                '/admin_filter_newphrase <type:\w+> <chatId:\d+>' => 'admin_filter_newphrase/index',
                '/admin_filter_set_newphrase <type:\w+> <chatId:\d+>' => 'admin_filter_newphrase/update',

                '/admin_filter_phrase <phraseId:\d+>' => 'admin_filter_phrase/index',
                '/admin_filter_delete_phrase <phraseId:\d+>' => 'admin_filter_phrase/delete',
                '/admin_filter_change_phrase <phraseId:\d+>' => 'admin_filter_phrase/create',
                '/admin_filter_update_phrase <phraseId:\d+>' => 'admin_filter_phrase/update',

                '/admin_join_hider <chatId:\d+>' => 'admin_join_hider',
                '/admin_join_hider_change_status <chatId:\d+>' => 'admin_join_hider/update',

                '/system_message_group_to_supergroup' => 'system_message/group_to_supergroup',

                '/<controller:\w+> <message:.+>' => '<controller>/index',
                '/<controller:\w+>' => '<controller>/index',
            ],
            'requestHandlers' => [
                new SystemMessageRequestHandler(),
                new MessageRequestHandler(),
                new CallbackQueryRequestHandler(),
                new LocationRequestHandler(),
            ],
        ],
    ],
];
