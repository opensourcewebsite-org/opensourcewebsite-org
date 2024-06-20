<?php

return [
    'baseUrl' => getenv('PARAM_BASE_URL') ?: 'https://opensourcewebsite.org',
    // TODO remove 'telegramProxy' => '',
    'bot' => [
        'proxy' => getenv('PARAM_BOT_PROXY') ?: '',
        'username' => getenv('PARAM_BOT_USERNAME') ?: '',
        'token' => getenv('PARAM_BOT_TOKEN') ?: '',
        'osw_logs_group_id' => getenv('PARAM_BOT_OSW_LOGS_GROUP_ID') ?: '',
        'osw_group_id' => getenv('PARAM_BOT_OSW_GROUP_ID') ?: '',
        'osw_channel_id' => getenv('PARAM_BOT_OSW_CHANNEL_ID') ?: '',
    ],
    'cookieValidationKey' => getenv('PARAM_REQUEST_COOKIEVALIDATIONKEY') ?: '',
];
