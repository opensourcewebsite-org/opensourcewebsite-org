<?php

namespace app\modules\bot\controllers\privates;

use app\modules\bot\components\Controller;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\helpers\ExternalLink;
use app\modules\bot\components\helpers\MessageWithEntitiesConverter;
use app\modules\bot\filters\GroupActiveAdministratorAccessFilter;
use app\modules\bot\models\Chat;
use app\modules\bot\models\ChatSetting;
use Yii;

/**
 * Class GroupJoinCaptchaController
 *
 * @package app\modules\bot\controllers\privates
 */
class GroupJoinCaptchaController extends Controller
{
    public const APPROVE = 1;
    public const DUMMY = 3;
    public const DECLINE = 2;

    public function behaviors()
    {
        return [
            'groupActiveAdministratorAccess' => [
                'class' => GroupActiveAdministratorAccessFilter::class,
                'except' => ['show-captcha', 'pass-captcha'],
            ],
        ];
    }

    /**
    * @param int $id Chat->id
    * @return array
    */
    public function actionIndex($id = null)
    {
        $chat = Yii::$app->cache->get('chat');

        $this->getState()->clearInputRoute();

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('index', [
                    'chat' => $chat,
                ]),
                [
                    [
                        [
                            'callback_data' => self::createRoute('set-status', [
                                'id' => $chat->id,
                            ]),
                            'text' => $chat->isJoinCaptchaOn() ? Emoji::STATUS_ON . ' ON' : Emoji::STATUS_OFF . ' OFF',
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('set-message', [
                                'id' => $chat->id,
                            ]),
                            'text' => Emoji::EDIT . ' ' . Yii::t('bot', 'Message'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('set-link-to-rules', [
                                'id' => $chat->id,
                            ]),
                            'text' => Emoji::EDIT . ' ' . Yii::t('bot', 'Link to Rules'),
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
                    ],
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
        $chat = Yii::$app->cache->get('chat');
        $chatMember = Yii::$app->cache->get('chatMember');

        switch ($chat->join_captcha_status) {
            case ChatSetting::STATUS_ON:
                $chat->join_captcha_status = ChatSetting::STATUS_OFF;

                break;
            case ChatSetting::STATUS_OFF:
                if (!$chatMember->trySetChatSetting('join_captcha_status', ChatSetting::STATUS_ON)) {
                    return $this->getResponseBuilder()
                        ->answerCallbackQuery(
                            $this->render('alert-status-on', [
                                'requiredRating' => $chatMember->getRequiredRatingForChatSetting('join_captcha_status', ChatSetting::STATUS_ON),
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
    public function actionSetMessage($id = null)
    {
        $chat = Yii::$app->cache->get('chat');

        $this->getState()->setInputRoute(self::createRoute('set-message', [
                'id' => $chat->id,
            ]));

        if ($this->getUpdate()->getMessage()) {
            if ($text = MessageWithEntitiesConverter::toHtml($this->getUpdate()->getMessage())) {
                if ($chat->validateSettingValue('join_captcha_message', $text)) {
                    $chat->join_captcha_message = $text;

                    return $this->runAction('index', [
                         'id' => $chat->id,
                     ]);
                }
            }
        }

        $messageMarkdown = MessageWithEntitiesConverter::fromHtml($chat->join_captcha_message ?? '');

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('set-message', [
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

    /**
    * @param int $id Chat->id
    * @return array
    */
    public function actionSetLinkToRules($id = null)
    {
        $chat = Yii::$app->cache->get('chat');

        $this->getState()->setInputRoute(self::createRoute('set-link-to-rules', [
                'id' => $chat->id,
            ]));

        if ($this->getUpdate()->getMessage()) {
            if ($text = $this->getUpdate()->getMessage()->getText()) {
                if ($chat->validateSettingValue('join_captcha_link_to_rules', $text)) {
                    $chat->join_captcha_link_to_rules = $text;

                    return $this->runAction('index', [
                         'id' => $chat->id,
                     ]);
                }
            }
        }

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('set-link-to-rules'),
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

    /**
     * Action shows captcha
     *
     * @param integer $id Chat->chatId
     * @return array|boolean
     */
    public function actionShowCaptcha($id = null)
    {
        if (isset($id)) {
            $groupChat = Chat::findOne([
                'chat_id' => $id,
            ]);

            if (!isset($groupChat)) {
                return [];
            }

            $rowButtons = [
                [
                    'callback_data' => self::createRoute('pass-captcha', [
                        'id' => $groupChat->getChatId(),
                        'choice' => self::APPROVE,
                    ]),
                    'text' => '👍',
                ],
                [
                    'callback_data' => self::createRoute('pass-captcha', [
                        'id' => $groupChat->getChatId(),
                        'choice' => self::DUMMY,
                    ]),
                    'text' => '👌',
                ],
                [
                    'callback_data' => self::createRoute('pass-captcha', [
                        'id' => $groupChat->getChatId(),
                        'choice' => self::DECLINE,
                    ]),
                    'text' => '👎',
                ],
            ];
            shuffle($rowButtons);

            $buttons[] = $rowButtons;

            $buttons[] = [
                [
                    'url' => $groupChat->join_captcha_link_to_rules,
                    'text' => Yii::t('bot', 'RULES'),
                    'visible' => (bool)$groupChat->join_captcha_link_to_rules,
                ],
            ];

            $buttons[] = [
                [
                    'url' => ExternalLink::getTelegramAccountLink($groupChat->getUsername()),
                    'text' => Yii::t('bot', 'Group'),
                    'visible' => (bool)$groupChat->getUsername(),
                ],
            ];

            $this->getResponseBuilder()
                ->sendMessage(
                    $this->render('show-captcha', [
                        'chat' => $groupChat,
                        'message' => $groupChat->join_captcha_message,
                    ]),
                    $buttons
                )
                ->send();
        } else {
            return [];
        }

        return true;
    }

    /**
     * Action allows user to pass captcha. This actions checks if joined user is interracting.
     *
     * @param integer $id Chat->chatId
     * @param integer $choice
     * @return boolean
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionPassCaptcha($id, $choice)
    {
        if (isset($id)) {
            $groupChat = Chat::findOne([
                'chat_id' => $id,
            ]);

            if (!isset($groupChat)) {
                return [];
            }

            $thisChat = $this->getTelegramChat();
            $user = $this->getTelegramUser();

            switch ($choice) {
                case self::APPROVE:
                    // Approve chat join request
                    $this->getBotApi()->approveChatJoinRequest(
                        $groupChat->getChatId(),
                        $user->getProviderUserId()
                    );
                    // Remove captcha message
                    $this->getBotApi()->deleteMessage(
                        $thisChat->getChatId(),
                        $this->getMessage()->getMessageId()
                    );

                    break;
                case self::DECLINE:
                    // Decline chat join request
                    $this->getBotApi()->declineChatJoinRequest(
                        $groupChat->getChatId(),
                        $user->getProviderUserId()
                    );
                    // Remove captcha message
                    $this->getBotApi()->deleteMessage(
                        $thisChat->getChatId(),
                        $this->getMessage()->getMessageId()
                    );

                    break;
                default:
                    return false;

                    break;
            }
        } else {
            return [];
        }

        return true;
    }
}
