<?php

namespace app\modules\bot\controllers\privates;

use app\modules\bot\components\actions\privates\wordlist\WordlistComponent;
use app\modules\bot\components\Controller;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\helpers\MessageWithEntitiesConverter;
use app\modules\bot\components\helpers\PaginationButtons;
use app\modules\bot\models\Chat;
use app\modules\bot\models\ChatMarketplaceLink;
use app\modules\bot\models\ChatMember;
use app\modules\bot\models\ChatPhrase;
use app\modules\bot\models\ChatSetting;
use Yii;
use yii\data\Pagination;

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
                            'callback_data' => self::createRoute('members-with-buttons', [
                                'id' => $chat->id,
                            ]),
                            'text' => Yii::t('bot', 'Members with buttons'),
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

    /**
    * @param int $id Chat->id
    */
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

    /**
    * @param int $id Chat->id
    * @return array
    */
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

    /**
    * @param int $id Chat->id
    * @return array
    */
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

    /**
    * @param int $page
    * @param int $id Chat->id
    * @return array
    */
    public function actionMembersWithButtons($page = 1, $id = null): array
    {
        $chat = Chat::findOne($id);

        if (!isset($chat) || !$chat->isGroup()) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $this->getState()->setName(self::createRoute('input-member', [
            'id' => $chat->id,
        ]));

        $query = $chat->getChatMembersWithMarketplaceLinks();

        $pagination = new Pagination([
            'totalCount' => $query->count(),
            'pageSize' => 9,
            'params' => [
                'page' => $page,
            ],
            'pageSizeParam' => false,
            'validatePage' => true,
        ]);

        $paginationButtons = PaginationButtons::build($pagination, function ($page) use ($chat) {
            return self::createRoute('members-with-buttons', [
                'id' => $chat->id,
                'page' => $page,
            ]);
        });

        $buttons = [];

        $members = $query->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();

        if ($members) {
            foreach ($members as $member) {
                $buttons[][] = [
                    'callback_data' => self::createRoute('member', [
                        'id' => $member->id,
                    ]),
                    'text' => $member->user->getDisplayName(),
                ];
            }

            if ($paginationButtons) {
                $buttons[] = $paginationButtons;
            }
        }

        $buttons[] = [
            [
                'callback_data' => self::createRoute('index', [
                    'id' => $chat->id,
                ]),
                'text' => Emoji::BACK,
            ],
            [
                'callback_data' => MenuController::createRoute(),
                'text' => Emoji::MENU,
            ],
        ];

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('members-with-buttons', [
                    'chat' => $chat,
                ]),
                $buttons
            )
            ->build();
    }

    /**
    * @param int $id Chat->id
    */
    public function actionInputMember($id = null): array
    {
        $chat = Chat::findOne($id);

        if (!isset($chat) || !$chat->isGroup()) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        if ($text = $this->getMessage()->getText()) {
            if (preg_match('/(?:^@(?:[A-Za-z0-9][_]{0,1})*[A-Za-z0-9]+)/i', $text, $matches)) {
                $username = ltrim($matches[0], '@');
            }
        }

        if (!isset($username)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $member = ChatMember::find()
            ->where([
                'chat_id' => $chat->id,
            ])
            ->joinWith('user')
            ->andWhere([
                '{{%bot_user}}.provider_user_name' => $username,
            ])
            ->one();

        if (!isset($member)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        return $this->runAction('member', [
            'id' => $member->id,
         ]);
    }

    /**
    * @param int $id ChatMember->id
    * @param int $page
    */
    public function actionMember($id = null, $page = 1): array
    {
        $member = ChatMember::findOne($id);

        if (!isset($member)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $chat = $member->chat;

        if (!isset($chat) || !$chat->isGroup()) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $this->getState()->setName(null);

        $query = $member->getMarketplaceLinks();

        $pagination = new Pagination([
            'totalCount' => $query->count(),
            'pageSize' => 9,
            'params' => [
                'page' => $page,
            ],
            'pageSizeParam' => false,
            'validatePage' => true,
        ]);

        $paginationButtons = PaginationButtons::build($pagination, function ($page) use ($member) {
            return self::createRoute('member', [
                'id' => $member->id,
                'page' => $page,
            ]);
        });

        $buttons = [];

        $links = $query->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();

        if ($links) {
            foreach ($links as $link) {
                $buttons[][] = [
                    'callback_data' => self::createRoute('link', [
                        'id' => $link->id,
                    ]),
                    'text' => $link->title ?: '#' . $link->id,
                ];
            }

            if ($paginationButtons) {
                $buttons[] = $paginationButtons;
            }
        }

        $buttons[] = [
            [
                'callback_data' => self::createRoute('members-with-buttons', [
                    'id' => $chat->id,
                ]),
                'text' => Emoji::BACK,
            ],
            [
                'callback_data' => MenuController::createRoute(),
                'text' => Emoji::MENU,
            ],
            [
                'callback_data' => self::createRoute('add-link', [
                    'id' => $member->id,
                ]),
                'text' => Emoji::ADD,
            ],
        ];

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('member', [
                    'chat' => $chat,
                    'chatMember' => $member,
                ]),
                $buttons
            )
            ->build();
    }

    /**
    * @param int $id ChatMember->id
    */
    public function actionAddLink($id = null): array
    {
        $member = ChatMember::findOne($id);

        if (!isset($member)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $chat = $member->chat;

        if (!isset($chat) || !$chat->isGroup()) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $this->getState()->setName(null);

        $link = new ChatMarketplaceLink();
        $link->member_id = $member->id;

        if ($link->save()) {
            return $this->actionLink($link->id);
        }

        return $this->getResponseBuilder()
            ->answerCallbackQuery()
            ->build();
    }

    /**
    * @param int $id ChatMarketplaceLink->id
    */
    public function actionLink($id = null): array
    {
        $link = ChatMarketplaceLink::findOne($id);

        if (!isset($link)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $chat = $link->chat;

        if (!isset($chat) || !$chat->isGroup()) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $this->getState()->setName(null);

        $chatMember = $link->chatMember;

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('link', [
                    'link' => $link,
                    'chat' => $chat,
                    'chatMember' => $chatMember,
                ]),
                [
                    [
                        [
                            'callback_data' => self::createRoute('set-link-title', [
                                'id' => $link->id,
                            ]),
                            'text' => Yii::t('bot', 'Title'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('set-link-url', [
                                'id' => $link->id,
                            ]),
                            'text' => Yii::t('bot', 'Url'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('member', [
                                'id' => $chatMember->id,
                            ]),
                            'text' => Emoji::BACK,
                        ],
                        [
                            'callback_data' => MenuController::createRoute(),
                            'text' => Emoji::MENU,
                        ],
                        [
                            'callback_data' => self::createRoute('delete-link', [
                                'id' => $link->id,
                            ]),
                            'text' => Emoji::DELETE,
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
    * @param int $id ChatMarketplaceLink->id
    * @return array
    */
    public function actionSetLinkTitle($id = null)
    {
        $link = ChatMarketplaceLink::findOne($id);

        if (!isset($link)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $chat = $link->chat;

        if (!isset($chat) || !$chat->isGroup()) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $this->getState()->setName(self::createRoute('set-link-title', [
            'id' => $link->id,
        ]));

        if ($this->getUpdate()->getMessage()) {
            if ($text = $this->getUpdate()->getMessage()->getText()) {
                $link->title = $text;

                if ($link->validate('title') && $link->save(false)) {
                    return $this->actionLink($link->id);
                }
            }
        }

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('set-link-title'),
                [
                    [
                        [
                            'callback_data' => self::createRoute('link', [
                                'id' => $link->id,
                            ]),
                            'text' => Emoji::BACK,
                        ],
                    ],
                ]
            )
            ->build();
    }

    /**
    * @param int $id ChatMarketplaceLink->id
    * @return array
    */
    public function actionSetLinkUrl($id = null)
    {
        $link = ChatMarketplaceLink::findOne($id);

        if (!isset($link)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $chat = $link->chat;

        if (!isset($chat) || !$chat->isGroup()) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $this->getState()->setName(self::createRoute('set-link-url', [
            'id' => $link->id,
        ]));

        if ($this->getUpdate()->getMessage()) {
            if ($text = $this->getUpdate()->getMessage()->getText()) {
                $link->url = $text;

                if ($link->validate('url') && $link->save(false)) {
                    return $this->actionLink($link->id);
                }
            }
        }

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('set-link-url'),
                [
                    [
                        [
                            'callback_data' => self::createRoute('link', [
                                'id' => $link->id,
                            ]),
                            'text' => Emoji::BACK,
                        ],
                    ],
                ]
            )
            ->build();
    }

    /**
     * @param int $id ChatMarketplaceLink->id
     * @return array
     */
    public function actionDeleteLink($id = null)
    {
        $link = ChatMarketplaceLink::findOne($id);

        if (!isset($link)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $chatMemberId = $link->chatMember->id;
        $link->delete();

        return $this->actionMember([
            'id' => $chatMemberId,
        ]);
    }
}
