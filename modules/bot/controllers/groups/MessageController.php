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
        $user = $this->getTelegramUser();
        $chat = $this->getTelegramChat();

        $chatMember = $chat->getChatMemberByUserId();

        if ($user->isBot()) {
            if ($chat->isMessageFilterOn()) {
                if ($chat->filter_remove_channels == ChatSetting::STATUS_ON) {
                    if ($chatMember->isAnonymousChannel()) {
                        if ($this->getMessage()) {
                            $this->getBotApi()->deleteMessage(
                                $chat->getChatId(),
                                $this->getMessage()->getMessageId()
                            );
                        }
                    }
                }
            }
        } elseif (!$chatMember->isCreator()) {
            if (!$chatMember->isAdministrator()) {
                if ($chat->isJoinCaptchaOn() && !$user->captcha_confirmed_at) {
                    if ($chatMember->role == JoinCaptchaController::ROLE_VERIFIED) {
                        $user->captcha_confirmed_at = time();
                        $user->save(false);
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
                                'provider_user_id' => $user->provider_user_id,
                            ])
                            ->one();
                        // Forward to captcha if a new member
                        if (!isset($botCaptcha)) {
                            return $this->run('join-captcha/show-captcha');
                        }
                    }
                }
            }

            $deleteMessage = false;

            if ($chatMember->isAdministrator() && $chat->isMembershipOn()) {
                if ($chatMember->hasExpiredMembership()) {
                    $deleteMessage = true;

                    $user->sendMessage(
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
                } elseif ($chatMember->hasExpiredVerification()) {
                    $deleteMessage = true;

                    $user->sendMessage(
                        $this->render('/privates/warning-verification', [
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
                if ($this->getMessage()->isNew() && $chat->isSlowModeOn()) {
                    if (!$chatMember->checkSlowMode()) {
                        $deleteMessage = true;

                        $user->sendMessage(
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
                if (!$chatMember->isActiveAdministrator() && (!$chat->isMembershipOn() || ($chat->isMembershipOn() && !$chatMember->hasActiveMembership()))) {
                    if ($chat->isMessageFilterOn()) {
                        if (($this->getMessage()->getText() !== null) || ($this->getMessage()->getLocation() !== null)) {
                            if ($replyMessage = $this->getMessage()->getReplyToMessage()) {
                                if (!$replyMessage->getForumTopicCreated()) {
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
                                        if (!isset($replyChatMember) || !($replyChatMember->isAdministrator() || $replyChatMember->hasActiveMembership())) {
                                            $deleteMessage = true;

                                            $user->sendMessage(
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
                            }

                            if (!$deleteMessage) {
                                if ($chat->filter_remove_username == ChatSetting::STATUS_ON) {
                                    if (!isset($replyMessage) || !isset($replyChatMember) || !($replyChatMember->isAdministrator() || $replyChatMember->hasActiveMembership())) {
                                        if (mb_stripos($this->getMessage()->getText(), '@') !== false) {
                                            $deleteMessage = true;

                                            $user->sendMessage(
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
                                    if (!isset($replyMessage) || !isset($replyChatMember) || !($replyChatMember->isAdministrator() || $replyChatMember->hasActiveMembership())) {
                                        if (preg_match('/(?:(\n\s))/i', $this->getMessage()->getText())) {
                                            // removes empty lines and indents, ignores spaces at the end of lines
                                            $deleteMessage = true;

                                            $user->sendMessage(
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

                                            $user->sendMessage(
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
                                    if (!isset($replyMessage) || !isset($replyChatMember) || !($replyChatMember->isAdministrator() || $replyChatMember->hasActiveMembership())) {
                                        if ($this->getMessage()->hasEmojis() || $this->getMessage()->hasCustomEmojis()) {
                                            $deleteMessage = true;

                                            $user->sendMessage(
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
                                if ($chat->filter_remove_locations == ChatSetting::STATUS_ON) {
                                    if (!isset($replyMessage) || !isset($replyChatMember) || !($replyChatMember->isAdministrator() || $replyChatMember->hasActiveMembership())) {
                                        if ($this->getMessage()->getLocation() !== null) {
                                            $deleteMessage = true;

                                            $user->sendMessage(
                                                $this->render('/privates/warning-filter-remove-locations', [
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
                                    if (!isset($replyMessage) || !isset($replyChatMember) || !($replyChatMember->isAdministrator() || $replyChatMember->hasActiveMembership())) {
                                        if ($this->getMessage()->hasStyledTexts()) {
                                            $deleteMessage = true;

                                            $user->sendMessage(
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

                                                $user->sendMessage(
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
            }

            if (!$deleteMessage) {
                if ($chat->isFaqOn()) {
                    if (($text = $this->getMessage()->getText()) !== null) {
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
        }

        return [];
    }
}
