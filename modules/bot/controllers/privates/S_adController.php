<?php

namespace app\modules\bot\controllers\privates;

use Yii;
use app\modules\bot\components\Controller as Controller;
use app\modules\bot\components\response\SendMessageCommand;
use \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;

/**
 * Class DefaultController
 *
 * @package app\modules\bot\controllers
 */
class S_adController extends Controller
{
    /**
     * @return string
     */
    public function actionIndex()
	{
        $update = $this->getUpdate();
        $telegramUser = $this->getTelegramUser();
        $user = $this->getUser();

        if (($telegramUser->location_lon && $telegramUser->location_lat) && $telegramUser->provider_user_name) {
            return [
            new SendMessageCommand(
                $this->getTelegramChat()->chat_id,
                $this->render('index'),
                [
                    'parseMode' => $this->textFormat,
                    'replyMarkup' => new InlineKeyboardMarkup([
                        [
                            [
                                'callback_data' => '/s_ad__found',
                                'text' => "Find ads",
                            ],
                            [
                                'callback_data' => '/s_ad__add_ads',
                                'text' => 'Post ads',
                            ]
                        ],
                        [
                            [
                                'callback_data' => '/s_ad__my_ads',
                                'text' => 'My ads',
                            ],
                            [
                                'callback_data' => '/s_ad__my_searches',
                                'text' => 'My searches',
                            ]
                        ],
                        [
                            [
                                'url' => 'https://github.com/opensourcewebsite-org/opensourcewebsite-org/blob/master/DONATE.md',
                                'text' => '👼 ' . Yii::t('bot', 'Donate'),
                            ],
                            [
                                'url' => 'https://github.com/opensourcewebsite-org/opensourcewebsite-org/blob/master/CONTRIBUTING.md',
                                'text' => '👨‍🚀 ' . Yii::t('bot', 'Contribution'),
                            ],
                        ],
                        [
                            [
                                'callback_data' => '/notifications_edit',
                                'text' => '🔔',
                            ],
                        ],
                        [
                            [
                                'callback_data' => '/services',
                                'text' => '🔙',
                            ],
                        ],
                    ]),
                ]
            ),
        ];
        } else {
            return [
                new SendMessageCommand(
                    $this->getTelegramChat()->chat_id,
                    $this->render('no-requirements'),
                    [
                        'parseMode' => $this->textFormat,
                        'replyMarkup' => new InlineKeyboardMarkup([
                            [
                                [
                                    'callback_data' => '/services',
                                    'text' => '🔙',
                                ],
                                [
                                    'callback_data' => '/menu',
                                    'text' => '📱',
                                ],
                            ],
                        ]),
                    ]
                ),
            ];
        }
    }

    /**
     * @return string
     */
    public function actionMy_ads()
    {
        return [
            new SendMessageCommand(
                $this->getTelegramChat()->chat_id,
                $this->render('my_ads'),
                [
                    'parseMode' => $this->textFormat,
                    'replyMarkup' => new InlineKeyboardMarkup([
                        [
                            [
                                'callback_data' => '/services',
                                'text' => '✏',
                            ],
                            [
                                'callback_data' => '/menu',
                                'text' => '🔃',
                            ],
                        ],
                        [
                            [
                                'callback_data' => '/services',
                                'text' => '🗑',
                            ],
                            [
                                'callback_data' => '/menu',
                                'text' => 'Withdraw',
                            ],
                        ],
                    ]),
                ]
            ),
        ];
    }

    /**
     * @return string
     */
    public function actionFound()
    {
        return [
            new SendMessageCommand(
                $this->getTelegramChat()->chat_id,
                $this->render('found'),
                [
                    'parseMode' => $this->textFormat,
                    'replyMarkup' => new InlineKeyboardMarkup([
                        [
                            [
                                'callback_data' => '/services',
                                'text' => 'Sale',
                            ],
                        ],
                        [
                            [
                                'callback_data' => '/menu',
                                'text' => 'Rend',
                            ],
                        ],
                        [
                            [
                                'callback_data' => '/services',
                                'text' => 'Services',
                            ],
                        ],
                        [
                            [
                                'callback_data' => '/s_ad',
                                'text' => '🔙',
                            ],
                        ]
                    ]),
                ]
            ),
        ];
    }

    /**
     * @return string
     */
    public function actionMy_searches()
    {
        return [
            new SendMessageCommand(
                $this->getTelegramChat()->chat_id,
                $this->render('my_searches'),
                [
                    'parseMode' => $this->textFormat,
                    'replyMarkup' => new InlineKeyboardMarkup([
                        [
                            [
                                'callback_data' => '/services',
                                'text' => '✏',
                            ],
                            [
                                'callback_data' => '/menu',
                                'text' => '🔃',
                            ],
                        ],
                        [
                            [
                                'callback_data' => '/services',
                                'text' => '🗑',
                            ],
                            [
                                'callback_data' => '/menu',
                                'text' => 'Withdraw',
                            ],
                        ],
                    ]),
                ]
            ),
        ];
    }

    /**
     * @return string
     */
    public function actionAdd_ads()
    {
        return [
            new SendMessageCommand(
                $this->getTelegramChat()->chat_id,
                $this->render('add_ads'),
                [
                    'parseMode' => $this->textFormat,
                    'replyMarkup' => new InlineKeyboardMarkup([
                        [
                            [
                                'callback_data' => '/services',
                                'text' => 'Sale',
                            ],
                        ],
                        [
                            [
                                'callback_data' => '/menu',
                                'text' => 'Rend',
                            ],
                        ],
                        [
                            [
                                'callback_data' => '/services',
                                'text' => 'Services',
                            ],
                        ],
                        [
                            [
                                'callback_data' => '/s_ad',
                                'text' => '🔙',
                            ],
                        ]
                    ]),
                ]
            ),
        ];
    }
}
 