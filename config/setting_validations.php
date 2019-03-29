<?php

return [
    'settings' => [
        'issue_quantity_value_per_one_rating' => [
            'type' => 'fractional',
            'validation' => ['sign' => 'positive'],
        ],
        'days_count_to_calculate_active_rating' => [
            'type' => 'fractional',
            'validation' => ['sign' => 'positive'],
        ],
        'moqup_quantity_value_per_one_rating' => [
            'type' => 'fractional',
            'validation' => ['sign' => 'positive'],
        ],
        'moqup_html_field_max_value' => [
            'type' => 'integer',
            'validation' => ['sign' => 'positive'],
        ],
        'moqup_css_field_max_value' => [
            'type' => 'integer',
            'validation' => ['sign' => 'positive'],
        ],
        'issue_text_field_max_value' => [
            'type' => 'integer',
            'validation' => ['sign' => 'positive'],
        ],
        'website_setting_min_vote_percent_to_apply_change' => [
            'type' => 'integer',
            'validation' => ['sign' => 'positive', 'max' => 100],
        ],
        'support_group_quantity_value_per_one_rating' => [
            'type' => 'fractional',
            'validation' => ['sign' => 'positive'],
        ],
        'support_group_bot_quantity_value_per_one_rating' => [
            'type' => 'fractional',
            'validation' => ['sign' => 'positive'],
        ],
        'support_group_member_quantity_value_per_one_rating' => [
            'type' => 'fractional',
            'validation' => ['sign' => 'positive'],
        ],
    ],
];
