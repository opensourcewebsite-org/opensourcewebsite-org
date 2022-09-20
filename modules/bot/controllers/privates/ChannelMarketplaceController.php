<?php

namespace app\modules\bot\controllers\privates;

use app\modules\bot\components\Controller;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\helpers\MessageWithEntitiesConverter;
use app\modules\bot\models\Chat;
use app\modules\bot\models\ChatSetting;
use Yii;

/**
 * Class ChannelMarketplaceController
 *
 * @package app\modules\bot\controllers\privates
 */
class ChannelMarketplaceController extends Controller
{
    /**
     * @param int $id Chat->id
     * @return array
     */
    public function actionIndex($id = null)
    {
        $chat = Chat::findOne($id);

        if (!isset($chat) || !$chat->isChannel()) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $this->getState()->setName(null);

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('index', compact('chat')),
                [
                    [
                        [
                            'callback_data' => self::createRoute('set-status', [
                                'id' => $chat->id,
                            ]),
                            'text' => $chat->isMarketplaceOn() ? Emoji::STATUS_ON . ' ON' : Emoji::STATUS_OFF . ' OFF',
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('set-limit', [
                                'id' => $chat->id,
                            ]),
                            'text' => Yii::t('bot', 'Limit'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('set-text-hint', [
                                'id' => $chat->id,
                            ]),
                            'text' => Yii::t('bot', 'Hint for text'),
                        ],
                    ],
                    // TODO add optional tags (look GroupMarketplaceController)
                    [
                        [
                            'callback_data' => ChannelController::createRoute('view', [
                                'chatId' => $chat->id,
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

    /**
     * @param int $id Chat->id
     * @return array
     */
    public function actionSetStatus($id = null)
    {
        $chat = Chat::findOne($id);

        if (!isset($chat) || !$chat->isChannel()) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        switch ($chat->marketplace_status) {
            case ChatSetting::STATUS_ON:
                $chat->marketplace_status = ChatSetting::STATUS_OFF;

                break;
            case ChatSetting::STATUS_OFF:
                $chatMember = $chat->getChatMemberByUserId();

                if (!$chatMember->trySetChatSetting('marketplace_status', ChatSetting::STATUS_ON)) {
                    return $this->getResponseBuilder()
                        ->answerCallbackQuery(
                            $this->render('alert-status-on', [
                                'requiredRating' => $chatMember->getRequiredRatingForChatSetting('marketplace_status', ChatSetting::STATUS_ON),
                            ]),
                            true
                        )
                        ->build();
                }

                break;
        }

        return $this->actionIndex($chat->id);
    }

    /**
     * @param int $id Chat->id
     * @return array
     */
    public function actionSetLimit($id = null)
    {
        $chat = Chat::findOne($id);

        if (!isset($chat) || !$chat->isChannel()) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $this->getState()->setName(self::createRoute('set-limit', [
                'id' => $chat->id,
            ]));

        if ($this->getUpdate()->getMessage()) {
            if ($text = (int)$this->getUpdate()->getMessage()->getText()) {
                if ($chat->validateSettingValue('marketplace_active_post_limit_per_member', $text)) {
                    $chat->marketplace_active_post_limit_per_member = $text;

                    return $this->runAction('index', [
                        'id' => $chat->id,
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
                                'id' => $chat->id,
                            ]),
                            'text' => Emoji::BACK,
                        ],
                    ],
                ]
            )
            ->build();
    }

    /**
     * @param int $id Chat->id
     * @return array
     */
    public function actionSetTextHint($id = null)
    {
        $chat = Chat::findOne($id);

        if (!isset($chat) || !$chat->isChannel()) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $this->getState()->setName(self::createRoute('set-text-hint', [
                'id' => $chat->id,
            ]));

        if ($this->getUpdate()->getMessage()) {
            if ($text = MessageWithEntitiesConverter::toHtml($this->getUpdate()->getMessage())) {
                if ($chat->validateSettingValue('marketplace_text_hint', $text)) {
                    $chat->marketplace_text_hint = $text;

                    return $this->runAction('index', [
                        'id' => $chat->id,
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
                                'id' => $chat->id,
                            ]),
                            'text' => Emoji::BACK,
                        ],
                    ],
                ],
                [
                    'disablePreview' => true,
                ]
            )
            ->build();
    }
}
