<?php

namespace app\modules\bot\controllers\groups;

use app\modules\bot\components\Controller;
use app\modules\bot\controllers\privates\GroupGuestController;
use app\modules\bot\models\ChatCaptcha;
use app\modules\bot\models\ChatMember;
use app\modules\bot\models\ChatSetting;
use app\modules\bot\models\User;
use Yii;

/**
 * Class MessageController
 *
 * @package app\modules\bot\controllers\groups
 */
class MessageController extends Controller
{
    /**
     * @return array
     */
    public function actionIndex()
    {
        $telegramUser = $this->getTelegramUser();
        $chat = $this->getTelegramChat();

        $chatMember = ChatMember::findOne([
            'chat_id' => $chat->id,
            'user_id' => $telegramUser->id,
        ]);

        if (!$chatMember->isAdministrator() && $chat->isJoinCaptchaOn() && !$telegramUser->captcha_confirmed_at) {
            if ($chatMember->role == JoinCaptchaController::ROLE_VERIFIED) {
                $telegramUser->captcha_confirmed_at = time();
                $telegramUser->save(false);
            } else {
                if ($this->getMessage()) {
                    $this->getBotApi()->deleteMessage(
                        $chat->getChatId(),
                        $this->getMessage()->getMessageId()
                    );
                }

                $botCaptcha = ChatCaptcha::find()
                    ->where([
                        'chat_id' => $chat->id,
                        'provider_user_id' => $telegramUser->provider_user_id,
                    ])
                    ->one();

                // Forward to captcha if a new member
                if (!isset($botCaptcha)) {
                    return $this->run('join-captcha/show-captcha');
                }
            }
        }

        $deleteMessage = false;

        if ($chat->isLimiterOn() && !$telegramUser->isBot() && !$chatMember->isCreator()) {
            if (!$chatMember->checkLimiter()) {
                $deleteMessage = true;

                $telegramUser->sendMessage(
                    $this->render('/privates/warning-limiter', [
                        'chat' => $chat,
                        'chatMember' => $chatMember,
                    ]),
                    [
                        [
                            [
                                'callback_data' => GroupGuestController::createRoute('view', [
                                    'id' => $chat->id,
                                ]),
                                'text' => Yii::t('bot', 'Group View'),
                            ],
                        ],
                    ]
                );
            }
        }

        if (!$deleteMessage) {
            if ($chat->isMembershipOn() && !$telegramUser->isBot() && !$chatMember->isCreator()) {
                if (!$chatMember->checkMembership()) {
                    $deleteMessage = true;

                    $telegramUser->sendMessage(
                        $this->render('/privates/warning-membership', [
                            'chat' => $chat,
                            'chatMember' => $chatMember,
                        ]),
                        [
                            [
                                [
                                    'callback_data' => GroupGuestController::createRoute('view', [
                                        'id' => $chat->id,
                                    ]),
                                    'text' => Yii::t('bot', 'Group View'),
                                ],
                            ],
                        ]
                    );
                }
            }
        }

        if (!$deleteMessage) {
            if ($chat->isSlowModeOn() && $this->getMessage()->isNew() && !$telegramUser->isBot() && !$chatMember->isCreator()) {
                if (!$chatMember->checkSlowMode()) {
                    $deleteMessage = true;

                    $telegramUser->sendMessage(
                        $this->render('/privates/warning-slow-mode', [
                            'chat' => $chat,
                        ]),
                        [
                            [
                                [
                                    'callback_data' => GroupGuestController::createRoute('view', [
                                        'id' => $chat->id,
                                    ]),
                                    'text' => Yii::t('bot', 'Group View'),
                                ],
                            ],
                        ]
                    );
                } else {
                    $isSlowModeOn = true;
                }
            }
        }

        if (!$deleteMessage) {
            if (($chat->filter_status == ChatSetting::STATUS_ON) && !$chatMember->isAdministrator()) {
                if ($this->getMessage()->getText() !== null) {
                    if ($replyMessage = $this->getMessage()->getReplyToMessage()) {
                        $replyUser = User::findOne([
                            'provider_user_id' => $replyMessage->getFrom()->getId(),
                        ]);

                        if ($replyUser) {
                            $replyChatMember = ChatMember::findOne([
                                'chat_id' => $chat->id,
                                'user_id' => $replyUser->id,
                            ]);
                        }

                        if ($chat->filter_remove_reply == ChatSetting::STATUS_ON) {
                            if (!isset($replyChatMember) || !$replyChatMember->isAdministrator()) {
                                $deleteMessage = true;

                                $telegramUser->sendMessage(
                                    $this->render('/privates/warning-filter-remove-reply', [
                                        'chat' => $chat,
                                    ]),
                                    [
                                        [
                                            [
                                                'callback_data' => GroupGuestController::createRoute('view', [
                                                    'id' => $chat->id,
                                                ]),
                                                'text' => Yii::t('bot', 'Group View'),
                                            ],
                                        ],
                                    ]
                                );
                            }
                        }
                    }

                    if (!$deleteMessage) {
                        if ($chat->filter_remove_channels == ChatSetting::STATUS_ON) {
                            if ($chatMember->isAnonymousChannel()) {
                                $deleteMessage = true;
                            }
                        }
                    }

                    if (!$deleteMessage) {
                        if ($chat->filter_remove_username == ChatSetting::STATUS_ON) {
                            if (!isset($replyMessage) || !isset($replyChatMember) || !$replyChatMember->isAdministrator()) {
                                if (mb_stripos($this->getMessage()->getText(), '@') !== false) {
                                    $deleteMessage = true;

                                    $telegramUser->sendMessage(
                                        $this->render('/privates/warning-filter-remove-username', [
                                            'chat' => $chat,
                                        ]),
                                        [
                                            [
                                                [
                                                    'callback_data' => GroupGuestController::createRoute('view', [
                                                        'id' => $chat->id,
                                                    ]),
                                                    'text' => Yii::t('bot', 'Group View'),
                                                ],
                                            ],
                                        ]
                                    );
                                }
                            }
                        }
                    }

                    if (!$deleteMessage) {
                        if ($chat->filter_remove_empty_line == ChatSetting::STATUS_ON) {
                            if (!isset($replyMessage) || !isset($replyChatMember) || !$replyChatMember->isAdministrator()) {
                                if (preg_match('/(?:(\n\s))/i', $this->getMessage()->getText())) {
                                    // removes empty lines and indents, ignores spaces at the end of lines
                                    $deleteMessage = true;

                                    $telegramUser->sendMessage(
                                        $this->render('/privates/warning-filter-remove-empty-line', [
                                            'chat' => $chat,
                                        ]),
                                        [
                                            [
                                                [
                                                    'callback_data' => GroupGuestController::createRoute('view', [
                                                        'id' => $chat->id,
                                                    ]),
                                                    'text' => Yii::t('bot', 'Group View'),
                                                ],
                                            ],
                                        ]
                                    );
                                } elseif (preg_match('/(?:(( ){2,}\S))/i', $this->getMessage()->getText())) {
                                    // removes double spaces
                                    $deleteMessage = true;

                                    $telegramUser->sendMessage(
                                        $this->render('/privates/warning-filter-remove-double-spaces', [
                                            'chat' => $chat,
                                        ]),
                                        [
                                            [
                                                [
                                                    'callback_data' => GroupGuestController::createRoute('view', [
                                                        'id' => $chat->id,
                                                    ]),
                                                    'text' => Yii::t('bot', 'Group View'),
                                                ],
                                            ],
                                        ]
                                    );
                                }
                            }
                        }
                    }

                    if (!$deleteMessage) {
                        if ($chat->filter_remove_emoji == ChatSetting::STATUS_ON) {
                            if (!isset($replyMessage) || !isset($replyChatMember) || !$replyChatMember->isAdministrator()) {
                                if ($this->getMessage()->hasEmojis() || $this->getMessage()->hasCustomEmojis()) {
                                    $deleteMessage = true;

                                    $telegramUser->sendMessage(
                                        $this->render('/privates/warning-filter-remove-emoji', [
                                            'chat' => $chat,
                                        ]),
                                        [
                                            [
                                                [
                                                    'callback_data' => GroupGuestController::createRoute('view', [
                                                        'id' => $chat->id,
                                                    ]),
                                                    'text' => Yii::t('bot', 'Group View'),
                                                ],
                                            ],
                                        ]
                                    );
                                }
                            }
                        }
                    }

                    if (!$deleteMessage) {
                        if ($chat->filter_remove_styled_texts == ChatSetting::STATUS_ON) {
                            if (!isset($replyMessage) || !isset($replyChatMember) || !$replyChatMember->isAdministrator()) {
                                if ($this->getMessage()->hasStyledTexts()) {
                                    $deleteMessage = true;

                                    $telegramUser->sendMessage(
                                        $this->render('/privates/warning-filter-remove-styled-texts', [
                                            'chat' => $chat,
                                        ]),
                                        [
                                            [
                                                [
                                                    'callback_data' => GroupGuestController::createRoute('view', [
                                                        'id' => $chat->id,
                                                    ]),
                                                    'text' => Yii::t('bot', 'Group View'),
                                                ],
                                            ],
                                        ]
                                    );
                                }
                            }
                        }
                    }

                    if (!$deleteMessage) {
                        switch ($chat->filter_mode) {
                            case ChatSetting::FILTER_MODE_OFF:
                                break;
                            case ChatSetting::FILTER_MODE_BLACKLIST:
                                $phrases = $chat->getBlacklistPhrases()->all();

                                foreach ($phrases as $phrase) {
                                    if (mb_stripos($this->getMessage()->getText(), $phrase->text) !== false) {
                                        $deleteMessage = true;

                                        $telegramUser->sendMessage(
                                            $this->render('/privates/warning-filter-blacklist', [
                                                'chat' => $chat,
                                                'text' => $phrase->text,
                                            ]),
                                            [
                                                [
                                                    [
                                                        'callback_data' => GroupGuestController::createRoute('view', [
                                                            'id' => $chat->id,
                                                        ]),
                                                        'text' => Yii::t('bot', 'Group View'),
                                                    ],
                                                ],
                                            ]
                                        );

                                        break;
                                    }
                                }

                                break;
                            case ChatSetting::FILTER_MODE_WHITELIST:
                                $deleteMessage = true;

                                $phrases = $chat->getWhitelistPhrases()->all();

                                foreach ($phrases as $phrase) {
                                    if (mb_stripos($this->getMessage()->getText(), $phrase->text) !== false) {
                                        $deleteMessage = false;

                                        break;
                                    }
                                }

                                break;
                        }
                    }
                }
            }
        }

        if (!$deleteMessage) {
            if ($chat->faq_status == ChatSetting::STATUS_ON) {
                if (($text = $this->getMessage()->getText()) !== null) {
                    if (strtolower($text) == 'faq') {
                        return $this->run('faq/show-chat-link');
                    }

                    $question = $chat->getQuestionPhrases()
                        ->where([
                            'text' => $text,
                        ])
                        ->andWhere([
                            'not', ['answer' => null],
                        ])
                        ->one();

                    if (isset($question)) {
                        return $this->run('faq/show-answer', [
                                'questionId' => $question->id,
                            ]);
                    }
                }
            }
        }

        if ($deleteMessage) {
            if ($this->getMessage()) {
                $this->getBotApi()->deleteMessage(
                    $chat->getChatId(),
                    $this->getMessage()->getMessageId()
                );
            }
        } elseif (isset($isSlowModeOn) && $isSlowModeOn) {
            $chatMember->updateSlowMode($this->getMessage()->getDate());
        }

        return [];
    }
}
