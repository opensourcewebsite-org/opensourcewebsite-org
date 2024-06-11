<?php

return [
    '<action:(index|terms-of-use|privacy-policy)>' => 'guest/default/<action>',
    'invite/<id>' => 'guest/default/invite',
    '<action:(signup|login|change-language)>' => 'site/<action>',
    'dashboard' => 'user/dashboard',
    'account' => 'user/account',
    'user/change-<action>' => 'user/change-<action>',
    'u/<id>' => 'contact/view-user',
    'examples/<action>/<id>' => 'examples/<action>',
    'data/payment-method/<id>' => 'data/payment-method-view',
    'data/currency/<id>' => 'data/currency-view',
    'cron-job' => 'cron-job/index',
    'cron-job/view/<id:[\d]+>' => 'cron-job/view',
    'webhook/telegram/<token>' => 'webhook/telegram',
    'webhook/telegram-bot/<token>' => 'webhook/telegram-bot',
    'support-groups/clients-languages/<id:[\d]+>' => 'support-groups/clients-languages',
    'support-groups/clients-list/<id:[\d]+>/<language:[\w]+>' => 'support-groups/clients-list',
    'support-groups/clients-list/<id:[\d]+>' => 'support-groups/clients-list',
    'support-groups/clients-view/<id:[\d]+>' => 'support-groups/clients-view',

    'api/<controller:[-\w]+>/<action:[-\w]+>/<id:[\d]+>' => '<controller>/<action>',
    'api/<controller:[-\w]+>/<action:[-\w]+>/' => '<controller>/<action>',
    'api/<controller:[-\w]+>/<action:[-\w]+>' => '<controller>/<action>',

    '<controller:[-\w]+>/<action:[-\w]+>/<id:[\d]+>' => '<controller>/<action>',
    '<controller:[-\w]+>/<action:[-\w]+>/' => '<controller>/<action>',
    '<controller:[-\w]+>/<action:[-\w]+>' => '<controller>/<action>',
];
