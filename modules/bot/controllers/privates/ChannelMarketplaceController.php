<?php

namespace app\modules\bot\controllers\privates;

use Yii;
use app\modules\bot\components\Controller;
use app\modules\bot\models\Chat;
use app\modules\bot\models\ChatSetting;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\helpers\MessageWithEntitiesConverter;

/**
 * Class ChannelMarketplaceController
 *
 * @package app\modules\bot\controllers\privates
 */
class ChannelMarketplaceController extends Controller
{
    /**
     * @return array
     */
    public function actionIndex($chatId = null)
    {
        $this->getState()->setName(null);

        $chat = Chat::findOne($chatId);

        if (!isset($chat)) {
            return [];
        }

        $statusOn = ($chat->marketplace_status == ChatSetting::STATUS_ON);

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('index', compact('chat')),
                [
                    [
                        [
                            'callback_data' => self::createRoute('set-status', [
                                'chatId' => $chatId,
                            ]),
                            'text' => $statusOn ? Emoji::STATUS_ON . ' ON' : Emoji::STATUS_OFF . ' OFF',
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('set-limit', [
                                'chatId' => $chatId,
                            ]),
                            'text' => Yii::t('bot', 'Limit'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('set-text-hint', [
                                'chatId' => $chatId,
                            ]),
                            'text' => Yii::t('bot', 'Hint for text'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => ChannelController::createRoute('view', [
                                'chatId' => $chatId,
                            ]),
                            'text' => Emoji::BACK,
                        ],
                        [
                            'callback_data' => MenuController::createRoute(),
                            'text' => Emoji::MENU,
                        ],
                    ]
                ],
                [
                    'disablePreview' => true,
                ]
            )
            ->build();
    }

    public function actionSetStatus($chatId = null)
    {
        $chat = Chat::findOne($chatId);

        if (!isset($chat)) {
            return [];
        }

        if ($chat->marketplace_status == ChatSetting::STATUS_ON) {
            $chat->marketplace_status = ChatSetting::STATUS_OFF;
        } else {
            $chat->marketplace_status = ChatSetting::STATUS_ON;
        }

        return $this->actionIndex($chatId);
    }

    public function actionSetLimit($chatId = null)
    {
        $chat = Chat::findOne($chatId);

        if (!isset($chat)) {
            return [];
        }

        $this->getState()->setName(self::createRoute('set-limit', [
                'chatId' => $chatId,
            ]));

        if ($this->getUpdate()->getMessage()) {
            if ($text = (int)$this->getUpdate()->getMessage()->getText()) {
                if ($chat->validateSettingValue('marketplace_active_post_limit_per_member', $text)) {
                    $chat->marketplace_active_post_limit_per_member = $text;

                    return $this->runAction('index', [
                        'chatId' => $chatId,
                    ]);
                }
            }
        }

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('set-limit'),
                [
                    [
                        [
                            'callback_data' => self::createRoute('index', [
                                'chatId' => $chatId,
                            ]),
                            'text' => Emoji::BACK,
                        ],
                    ],
                ]
            )
            ->build();
    }

    public function actionSetTextHint($chatId = null)
    {
        $chat = Chat::findOne($chatId);

        if (!isset($chat)) {
            return [];
        }

        $this->getState()->setName(self::createRoute('set-text-hint', [
                'chatId' => $chatId,
            ]));

        if ($this->getUpdate()->getMessage()) {
            if ($text = MessageWithEntitiesConverter::toHtml($this->getUpdate()->getMessage())) {
                if ($chat->validateSettingValue('marketplace_text_hint', $text)) {
                    $chat->marketplace_text_hint = $text;

                    return $this->runAction('index', [
                        'chatId' => $chatId,
                    ]);
                }
            }
        }

        $messageMarkdown = MessageWithEntitiesConverter::fromHtml($chat->marketplace_text_hint ?? '');

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('set-text-hint', [
                    'messageMarkdown' => $messageMarkdown,
                ]),
                [
                    [
                        [
                            'callback_data' => self::createRoute('index', [
                                'chatId' => $chatId,
                            ]),
                            'text' => Emoji::BACK,
                        ],
                    ],
                ]
            )
            ->build();
    }
}
