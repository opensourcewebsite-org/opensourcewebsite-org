<?php

namespace app\modules\bot\controllers\privates;

use app\modules\bot\components\actions\privates\wordlist\WordlistComponent;
use app\modules\bot\components\Controller;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\helpers\MessageWithEntitiesConverter;
use app\modules\bot\models\Chat;
use app\modules\bot\models\ChatSetting;
use app\modules\bot\models\ChatPhrase;
use Yii;

/**
 * Class GroupMarketplaceController
 *
 * @package app\modules\bot\controllers\privates
 */
class GroupMarketplaceController extends Controller
{
    public function actions()
    {
        return array_merge(
            parent::actions(),
            Yii::createObject([
                'class' => WordlistComponent::class,
                'wordModelClass' => ChatPhrase::class,
                'modelAttributes' => [
                    'type' =>ChatPhrase::TYPE_MARKETPLACE_TAGS,
                ],
                'actionGroupName' => 'tags',
            ])->actions()
        );
    }

    /**
     * @return array
     */
    public function actionIndex($id = null)
    {
        $chat = Chat::findOne($id);

        if (!isset($chat) || !$chat->isGroup()) {
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
                            'text' => $chat->marketplace_status == ChatSetting::STATUS_ON ? Emoji::STATUS_ON . ' ON' : Emoji::STATUS_OFF . ' OFF',
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('set-mode', [
                                'id' => $chat->id,
                            ]),
                            'text' => Yii::t('bot', 'Mode') . ': ' . $chat->getMarketplaceModeLabel(),
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('set-limit', [
                                'id' => $chat->id,
                            ]),
                            'text' => Yii::t('bot', 'Limit of active posts'),
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
                    [
                        [
                            'callback_data' => self::createRoute('tags-word-list', [
                                'chatId' => $chat->id,
                            ]),
                            'text' => Yii::t('bot', 'Optional tags'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => GroupController::createRoute('view', [
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

    public function actionSetStatus($id = null)
    {
        $chat = Chat::findOne($id);

        if (!isset($chat) || !$chat->isGroup()) {
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

        return $this->actionIndex($id);
    }

    /**
     * @param int|null $id Chat->id
     * @return array
     */
    public function actionSetMode($id = null)
    {
        $chat = Chat::findOne($id);

        if (!isset($chat) || !$chat->isGroup()) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        switch ($chat->marketplace_mode) {
            case ChatSetting::MARKETPLACE_MODE_ALL:
                $chat->marketplace_mode = ChatSetting::MARKETPLACE_MODE_MEMBERSHIP;

                break;
            case ChatSetting::MARKETPLACE_MODE_MEMBERSHIP:
                $chat->marketplace_mode = ChatSetting::MARKETPLACE_MODE_ALL;

                break;
        }

        return $this->actionIndex($id);
    }

    public function actionSetLimit($id = null)
    {
        $chat = Chat::findOne($id);

        if (!isset($chat) || !$chat->isGroup()) {
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

    public function actionSetTextHint($id = null)
    {
        $chat = Chat::findOne($id);

        if (!isset($chat) || !$chat->isGroup()) {
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
