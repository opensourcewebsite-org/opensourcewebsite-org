<?php

use app\modules\bot\components\PrivateRouteResolver;

//use yii\helpers\ArrayHelper;

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
            'controllers' => [
                1 => 'start',
                2 => 'menu',
                3 => 'help',
                4 => 'user',
                5 => 'language',
                6 => 'services',
                7 => 'my_account',
                8 => 'my_profile',
                9 => 'group_guest',
                10 => 'member_review',
                11 => 'group_guest_faq',
                12 => 'member',
                13 => 'group_timezone',
                14 => 'group_currency',
                15 => 'channel_guest_marketplace',
                16 => 'group_guest_marketplace',
                17 => 'channel_marketplace',
                18 => 'group_marketplace',
                19 => 'group_membership',
                20 => 'group_limiter',
                21 => 'group_slow_mode',
                22 => 'channel',
                23 => 'group',
            ],
            'actions' => [
                1 => 'index',
                2 => 'list',
                3 => 'view',
                4 => 'create',
                5 => 'edit',
                6 => 'update',
                7 => 'delete',
                8 => 'select',
                9 => 'input_intro_text',
                10 => 'delete_intro',
                11 => 'public_groups',
                12 => 'members_with_intro',
                13 => 'members_with_reviews',
                14 => 'word_list',
                15 => 'premium_members',
                16 => 'id',
                17 => 'input',
                18 => 'set_status',
                19 => 'set_title',
                20 => 'set_text',
                21 => 'set_text_hint',
                22 => 'set_mode',
                23 => 'set_tag',
                24 => 'tag_list_word_list',
                25 => 'update_post',
                26 => 'send_post',
                27 => 'set_messages_limit',
                28 => 'input_member_messages_limit',
                29 => 'delete_member_messages_limit',
                30 => 'set_member_note',
                31 => 'delete_member_note',
                32 => 'input-member-date',
                33 => 'delete-member-date',
                34 => 'member',
            ],
        ],
    ],
];

//$config have more priority than $common
//$config = ArrayHelper::merge($common, $config);

return $config;
