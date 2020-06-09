<?php

return [
    '<action:(index|terms-of-use|privacy-policy)>' => 'guest/default/<action>',
    'invite/<id>' => 'guest/default/invite',
    '<action:(signup|login|account|change-language)>' => 'site/<action>',
    'wikipedia-pages' => 'wikipedia-pages/index',
    'wikinews-pages' => 'wikinews-pages/index',
    'wikinews-pages/create' => 'wikinews-pages/create',
    'cron-job' => 'cron-job/index',
    'cron-job/view/<id:[\d]+>' => 'cron-job/view',
    'referrals' => 'referrals/index',
    'wikipedia-page/view/<code>/<all>' => 'wikipedia-pages/view',
    'wikipedia-page/view/<code>' => 'wikipedia-pages/view',
    'wikipedia-page/recommended/<code>' => 'wikipedia-pages/recommended',
    'webhook/telegram/<token>' => 'webhook/telegram',
    'webhook/telegram-bot/<token>' => 'webhook/telegram-bot',
    'website-settings' => 'setting/index',
    'support-groups/clients-languages/<id:[\d]+>' => 'support-groups/clients-languages',
    'support-groups/clients-list/<id:[\d]+>/<language:[\w]+>' => 'support-groups/clients-list',
    'support-groups/clients-list/<id:[\d]+>' => 'support-groups/clients-list',
    'support-groups/clients-view/<id:[\d]+>' => 'support-groups/clients-view',
    'u/<id>' => 'user/profile',
    'user/change-<action>' => 'user/change-<action>',
    'examples/<action>/<id>' => 'examples/<action>',
//              '<action:(design-list|design-add|design-edit|design-view)>' => 'moqup/<action>',
    'data/payment-method/<id>' => 'data/payment-method-show'
];
