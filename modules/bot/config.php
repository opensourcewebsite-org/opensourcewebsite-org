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
                '/<controller:\w+>__<action:\w+>' => '<controller>/<action>',
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

                '/my_gender_<gender:\w+>' => 'my_gender/index',

                '/update_location' => 'my_location/update',

                '/admin_<page:\d+>' => 'admin',

                '/admin_chat <chatId:\d+>' => 'admin_chat',
                '/admin_message_filter <chatId:\d+>' => 'admin_message_filter',
                '/admin_message_filter_change_mode <chatId:\d+>' => 'admin_message_filter/update',
                '/admin_message_filter_change_status <chatId:\d+>' => 'admin_message_filter/status',
                '/admin_message_filter_whitelist <chatId:\d+>' => 'admin_message_filter_whitelist',
                '/admin_message_filter_whitelist <chatId:\d+> <page:\d+>' => 'admin_message_filter_whitelist',
                '/admin_message_filter_blacklist <chatId:\d+>' => 'admin_message_filter_blacklist',
                '/admin_message_filter_blacklist <chatId:\d+> <page:\d+>' => 'admin_message_filter_blacklist',
                '/admin_message_filter_newphrase <type:\w+> <chatId:\d+>' => 'admin_message_filter_newphrase/index',
                '/admin_message_filter_set_newphrase <type:\w+> <chatId:\d+>' => 'admin_message_filter_newphrase/update',

                '/admin_message_filter_phrase <phraseId:\d+>' => 'admin_message_filter_phrase/index',
                '/admin_message_filter_delete_phrase <phraseId:\d+>' => 'admin_message_filter_phrase/delete',
                '/admin_message_filter_change_phrase <phraseId:\d+>' => 'admin_message_filter_phrase/create',
                '/admin_message_filter_update_phrase <phraseId:\d+>' => 'admin_message_filter_phrase/update',

                '/admin_join_hider <chatId:\d+>' => 'admin_join_hider',
                '/admin_join_hider_change_status <chatId:\d+>' => 'admin_join_hider/update',

                '/create_company' => 'companies/create',
                '/update_company <id:\d+>' => 'companies/update',
                '/set_company_name' => 'companies/set-name',
                '/set_company_url' => 'companies/set-url',
                '/set_company_address' => 'companies/set-address',
                '/set_company_description' => 'companies/set-description',
                '/company <id:\d+>' => 'companies/show',

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
