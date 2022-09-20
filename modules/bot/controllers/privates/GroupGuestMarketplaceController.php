<?php

namespace app\modules\bot\controllers\privates;

use app\components\helpers\ArrayHelper;
use app\components\helpers\TimeHelper;
use app\modules\bot\components\actions\privates\wordlist\WordlistComponent;
use app\modules\bot\components\Controller;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\helpers\MessageWithEntitiesConverter;
use app\modules\bot\components\helpers\PaginationButtons;
use app\modules\bot\models\Chat;
use app\modules\bot\models\ChatMarketplacePost;
use app\modules\bot\models\ChatPhrase;
use app\modules\bot\models\ChatSetting;
use Yii;
use yii\data\Pagination;

/**
 * Class GroupGuestMarketplaceController
 *
 * @package app\modules\bot\controllers\privates
 */
class GroupGuestMarketplaceController extends Controller
{
    public function actions()
    {
        return array_merge(
            parent::actions(),
            Yii::createObject([
                'class' => WordlistComponent::class,
                'wordModelClass' => ChatPhrase::class,
                'modelAttributes' => [
                    'type' => ChatPhrase::TYPE_MARKETPLACE_TAGS,
                ],
                'actionGroupName' => 'tags',
                'options' => [
                    'actions' => [
                        'select' => true,
                        'insert' => false,
                        'update' => false,
                        'delete' => false,
                    ],
                    'listBackRoute' => [
                        'controller' => 'app\modules\bot\controllers\privates\GroupGuestMarketplaceController',
                        'action' => 'index',
                    ],
                ],
            ])->actions()
        );
    }

    /**
     * @param int $id Chat->id
     * @param int $page
     * @return array
     */
    public function actionIndex($id = null, $page = 1)
    {
        $chat = Chat::findOne($id);

        if (!isset($chat) || !$chat->isGroup() || ($chat->marketplace_status != ChatSetting::STATUS_ON)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $this->getState()->setName(null);

        $chatMember = $chat->getChatMemberByUserId();

        if (!$chatMember) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $query = $chatMember->getMarketplacePosts()
            ->orderBy([
                'title' => SORT_ASC,
                'id' => SORT_ASC,
            ]);

        $pagination = new Pagination([
            'totalCount' => $query->count(),
            'pageSize' => 9,
            'params' => [
                'page' => $page,
            ],
            'pageSizeParam' => false,
            'validatePage' => true,
        ]);

        $posts = $query->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();

        $paginationButtons = PaginationButtons::build($pagination, function ($page) use ($chat) {
            return self::createRoute('index', [
                'id' => $chat->id,
                'page' => $page,
            ]);
        });

        $buttons = [];

        if ($posts) {
            foreach ($posts as $post) {
                $buttons[][] = [
                    'callback_data' => self::createRoute('view', [
                        'id' => $post->id,
                    ]),
                    'text' => ($post->isActive() ? '' : Emoji::INACTIVE . ' ') . '#' . $post->id . ' ' . $post->title,
                ];
            }

            if ($paginationButtons) {
                $buttons[] = $paginationButtons;
            }
        }

        $buttons[] = [
            [
                'callback_data' => GroupGuestController::createRoute('view', [
                    'id' => $chat->id,
                ]),
                'text' => Emoji::BACK,
            ],
            [
                'callback_data' => MenuController::createRoute(),
                'text' => Emoji::MENU,
            ],
            [
                'callback_data' => self::createRoute('add', [
                    'id' => $chat->id,
                ]),
                'text' => Emoji::ADD,
            ],
        ];

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('index', [
                    'chat' => $chat,
                ]),
                $buttons,
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
    public function actionAdd($id = null)
    {
        $chat = Chat::findOne($id);

        if (!isset($chat) || !$chat->isGroup() || ($chat->marketplace_status != ChatSetting::STATUS_ON)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $this->getState()->setName(self::createRoute('add', [
            'id' => $chat->id,
        ]));

        $chatMember = $chat->getChatMemberByUserId();

        if (!$chatMember) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        if ($this->getUpdate()->getMessage()) {
            if ($text = MessageWithEntitiesConverter::toHtml($this->getUpdate()->getMessage())) {
                $post = new ChatMarketplacePost();
                $post->member_id = $chatMember->id;
                $post->text = $text;

                if ($post->save()) {
                    return $this->actionView($post->id);
                }
            }
        }

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('set-text', [
                    'chat' => $chat,
                ]),
                [
                    [
                        [
                            'callback_data' =>  self::createRoute('index', [
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
     * @param int $id ChatMarketplacePost->id
     * @return array
     */
    public function actionView($id = null)
    {
        $post = ChatMarketplacePost::findOne($id);

        if (!isset($post)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        if ((!$chat = $post->chat) || !$chat->isGroup() || ($chat->marketplace_status != ChatSetting::STATUS_ON)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $chatMember = $chat->getChatMemberByUserId();

        if (!$chatMember || ($post->getChatMemberId() != $chatMember->id)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $this->getState()->setName(null);

        $user = $this->getTelegramUser();

        $tags = [];

        if ($chatMember) {
            $tags = ArrayHelper::getColumn($chatMember->getPhrases(ChatPhrase::TYPE_MARKETPLACE_TAGS)->asArray()->all(), 'text');

            if ($membershipTag = $chatMember->getMembershipTag()) {
                $tags = ArrayHelper::merge([
                    $membershipTag,
                ], $tags);
            }
        }

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('view', [
                    'chat' => $chat,
                    'post' => $post,
                    'chatMember' => $chatMember,
                    'user' => $user,
                    'tags' => $tags,
                ]),
                [
                    [
                        [
                            'callback_data' => self::createRoute('set-status', [
                                'id' => $post->id,
                            ]),
                            'text' => $post->isActive() ? Emoji::STATUS_ON . ' ON' : Emoji::STATUS_OFF . ' OFF',
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('set-time', [
                                'id' => $post->id,
                            ]),
                            'text' => Yii::t('bot', 'Time of day') . ': ' . $post->getTimeOfDay() . ' (' . TimeHelper::getNameByOffset($chat->timezone) . ')',
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('set-skip-days', [
                                'id' => $post->id,
                            ]),
                            'text' => Yii::t('bot', 'Skip days') . ': ' . $post->getSkipDays(),
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('set-title', [
                                'id' => $post->id,
                            ]),
                            'text' => Yii::t('app', 'Title'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('set-text', [
                                'id' => $post->id,
                            ]),
                            'text' => Yii::t('app', 'Text'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('tags-word-list', [
                                'chatId' => $chat->id,
                            ]),
                            'text' => Yii::t('bot', 'Tags'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('update-post', [
                                'id' => $post->id,
                            ]),
                            'text' => Emoji::REFRESH . ' ' . Yii::t('bot', 'Update last post in the group'),
                            'visible' => (bool)$post->getProviderMessageId(),
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('send-post', [
                                'id' => $post->id,
                            ]),
                            'text' => Emoji::SEND . ' ' . Yii::t('bot', 'Send new post to the group'),
                        ],
                    ],
                    [
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
                        [
                            'callback_data' => self::createRoute('delete', [
                                'id' => $post->id,
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
     * @param int $id ChatMarketplacePost->id
     * @return array
     */
    public function actionSetStatus($id = null)
    {
        $post = ChatMarketplacePost::findOne($id);

        if (!isset($post)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        if ((!$chat = $post->chat) || !$chat->isGroup() || ($chat->marketplace_status != ChatSetting::STATUS_ON)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $chatMember = $chat->getChatMemberByUserId();

        if (!$chatMember || ($post->getChatMemberId() != $chatMember->id)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $this->getState()->setName(null);

        if ($post->isActive()) {
            $post->setInactive();
            $post->save(false);
        } else {
            $activePostsCount = ChatMarketplacePost::find()
                ->where([
                    'member_id' => $chatMember->id,
                    'status' => ChatMarketplacePost::STATUS_ON,
                ])
                ->count();

            if ($activePostsCount >= $chat->marketplace_active_post_limit_per_member) {
                return $this->getResponseBuilder()
                    ->answerCallbackQuery(
                        $this->render('alert-active-posts-limit', [
                            'chat' => $chat,
                        ]),
                        true
                    )
                    ->build();
            }

            $post->setActive();
            $post->save(false);
        }

        return $this->actionView($post->id);
    }

    /**
     * @param int $id ChatMarketplacePost->id
     * @return array
     */
    public function actionSetTitle($id = null)
    {
        $post = ChatMarketplacePost::findOne($id);

        if (!isset($post)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        if ((!$chat = $post->chat) || !$chat->isGroup() || ($chat->marketplace_status != ChatSetting::STATUS_ON)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $chatMember = $chat->getChatMemberByUserId();

        if (!$chatMember || ($post->getChatMemberId() != $chatMember->id)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $this->getState()->setName(self::createRoute('set-title', [
            'id' => $post->id,
        ]));

        if ($this->getUpdate()->getMessage()) {
            if ($text = $this->getUpdate()->getMessage()->getText()) {
                $post->title = $text;

                if ($post->validate('title') && $post->save(false)) {
                    return $this->actionView($post->id);
                }
            }
        }

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('set-title'),
                [
                    [
                        [
                            'callback_data' =>  self::createRoute('view', [
                                'id' => $post->id,
                            ]),
                            'text' => Emoji::BACK,
                        ],
                    ],
                ]
            )
            ->build();
    }

    /**
     * @param int $id ChatMarketplacePost->id
     * @return array
     */
    public function actionSetText($id = null)
    {
        $post = ChatMarketplacePost::findOne($id);

        if (!isset($post)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        if ((!$chat = $post->chat) || !$chat->isGroup() || ($chat->marketplace_status != ChatSetting::STATUS_ON)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $chatMember = $chat->getChatMemberByUserId();

        if (!$chatMember || ($post->getChatMemberId() != $chatMember->id)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $this->getState()->setName(self::createRoute('set-text', [
            'id' => $post->id,
        ]));

        if ($this->getUpdate()->getMessage()) {
            if ($text = MessageWithEntitiesConverter::toHtml($this->getUpdate()->getMessage())) {
                $post->text = $text;

                if ($post->validate('text') && $post->save(false)) {
                    return $this->actionView($post->id);
                }
            }
        }

        $messageMarkdown = MessageWithEntitiesConverter::fromHtml($post->text ?? '');

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('set-text', [
                    'chat' => $chat,
                    'messageMarkdown' => $messageMarkdown,
                ]),
                [
                    [
                        [
                            'callback_data' =>  self::createRoute('view', [
                                'id' => $post->id,
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
     * @param int $id ChatMarketplacePost->id
     * @return array
     */
    public function actionSetTime($id = null)
    {
        $post = ChatMarketplacePost::findOne($id);

        if (!isset($post)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        if ((!$chat = $post->chat) || !$chat->isGroup() || ($chat->marketplace_status != ChatSetting::STATUS_ON)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $chatMember = $chat->getChatMemberByUserId();

        if (!$chatMember || ($post->getChatMemberId() != $chatMember->id)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $this->getState()->setName(self::createRoute('set-time', [
            'id' => $post->id,
        ]));

        if ($this->getUpdate()->getMessage()) {
            if (($text = TimeHelper::getMinutesByTimeOfDay($this->getUpdate()->getMessage()->getText())) !== null) {
                $post->time = $text;

                if ($post->validate('time') && $post->save(false)) {
                    return $this->actionView($post->id);
                }
            }
        }

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('set-time', [
                    'chat' => $chat,
                ]),
                [
                    [
                        [
                            'callback_data' =>  self::createRoute('view', [
                                'id' => $post->id,
                            ]),
                            'text' => Emoji::BACK,
                        ],
                    ],
                ]
            )
            ->build();
    }

    /**
     * @param int $id ChatMarketplacePost->id
     * @return array
     */
    public function actionSetSkipDays($id = null)
    {
        $post = ChatMarketplacePost::findOne($id);

        if (!isset($post)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        if ((!$chat = $post->chat) || !$chat->isGroup() || ($chat->marketplace_status != ChatSetting::STATUS_ON)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $chatMember = $chat->getChatMemberByUserId();

        if (!$chatMember || ($post->getChatMemberId() != $chatMember->id)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $this->getState()->setName(self::createRoute('set-skip-days', [
            'id' => $post->id,
        ]));

        if ($this->getUpdate()->getMessage()) {
            if (($text = $this->getUpdate()->getMessage()->getText()) !== null) {
                $post->skip_days = $text;

                if ($post->validate('skip_days') && $post->save(false)) {
                    return $this->actionView($post->id);
                }
            }
        }

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('set-skip-days'),
                [
                    [
                        [
                            'callback_data' =>  self::createRoute('view', [
                                'id' => $post->id,
                            ]),
                            'text' => Emoji::BACK,
                        ],
                    ],
                ]
            )
            ->build();
    }

    /**
     * @param int $id ChatMarketplacePost->id
     * @return array
     */
    public function actionUpdatePost($id = null)
    {
        $post = ChatMarketplacePost::findOne($id);

        if (!isset($post) || !$post->getProviderMessageId()) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        if ((!$chat = $post->chat) || !$chat->isMarketplaceOn()) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $chatMember = $chat->getChatMemberByUserId();

        if (!$chatMember || ($post->getChatMemberId() != $chatMember->id)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $user = $this->getTelegramUser();

        if (!$post->canRepost()) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery(
                    $this->render('alert-repost-seconds-limit', [
                        'post' => $post,
                    ]),
                    true
                )
                ->build();
        }

        $response = $this->prepareResponseBuilder($post, false)->send();

        if ($response) {
            $post->sent_at = $response->getDate();
            $post->save(false);

            return $this->getResponseBuilder()
                ->answerCallbackQuery(
                    $this->render('../alert-ok')
                )
                ->build();
        }

        return $this->getResponseBuilder()
            ->answerCallbackQuery()
            ->build();
    }

    /**
     * @param int $id ChatMarketplacePost->id
     * @return array
     */
    public function actionSendPost($id = null)
    {
        $post = ChatMarketplacePost::findOne($id);

        if (!isset($post)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        if ((!$chat = $post->chat) || !$chat->isMarketplaceOn()) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $chatMember = $chat->getChatMemberByUserId();

        if (!$chatMember || ($post->getChatMemberId() != $chatMember->id)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $user = $this->getTelegramUser();

        if (!$post->canRepost()) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery(
                    $this->render('alert-repost-seconds-limit', [
                        'post' => $post,
                    ]),
                    true
                )
                ->build();
        }

        if ($chat->isLimiterOn() && !$chatMember->isCreator() && !$chatMember->hasLimiter()) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery(
                    $this->render('alert-limiter', [
                        'chatMember' => $chatMember,
                    ]),
                    true
                )
                ->build();
        }

        if (!$chatMember->canUseMarketplace()) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery(
                    $this->render('alert-membership', [
                        'chatMember' => $chatMember,
                    ]),
                    true
                )
                ->build();
        }

        if ($chat->isSlowModeOn() && !$chatMember->isCreator()) {
            if (!$chatMember->checkSlowMode()) {
                return $this->getResponseBuilder()
                    ->answerCallbackQuery(
                        $this->render('alert-slow-mode', [
                            'chatMember' => $chatMember,
                        ]),
                        true
                    )
                    ->build();
            } else {
                $isSlowModeOn = true;
            }
        }

        $response = $this->prepareResponseBuilder($post)->send();

        if ($response) {
            if (isset($isSlowModeOn) && $isSlowModeOn) {
                $chatMember->updateSlowMode($response->getDate());
            }

            $post->sent_at = $response->getDate();
            $post->provider_message_id = $response->getMessageId();
            $post->save(false);

            return $this->getResponseBuilder()
                ->answerCallbackQuery(
                    $this->render('../alert-ok')
                )
                ->build();
        }

        return $this->getResponseBuilder()
            ->answerCallbackQuery()
            ->build();
    }

    /**
     * @param int $id ChatMarketplacePost->id
     * @return array
     */
    public function actionDelete($id = null)
    {
        $post = ChatMarketplacePost::findOne($id);

        if (!isset($post)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        if ((!$chat = $post->chat) || !$chat->isGroup() || ($chat->marketplace_status != ChatSetting::STATUS_ON)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $chatMember = $chat->getChatMemberByUserId();

        if (!$chatMember || ($post->getChatMemberId() != $chatMember->id)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        if ($post->getProviderMessageId()) {
            $this->getBotApi()->deleteMessage(
                $chat->getChatId(),
                $post->getProviderMessageId()
            );
        }

        $post->delete();

        return $this->actionIndex($chat->id);
    }

    /**
     * Show only accepted votings
     * https://core.telegram.org/bots/faq#broadcasting-to-users
     *
     * @return array
     */
    // public function actionSendMessage($id = null)
    // {
    //     $voting = UaLawmakingVoting::find()
    //         ->where([
    //             'sent_at' => null,
    //         ])
    //         ->andWhere(['>=', 'for', UaLawmakingVoting::MIN_ACCEPTED_VOTES])
    //         ->orderBy([
    //             'event_id' => SORT_ASC,
    //         ])
    //         ->one();
    //
    //     if (!$voting) {
    //         return [];
    //     }
    //
    //     $response = $this->prepareResponseBuilder($voting)->send();
    //
    //     if ($response) {
    //         $voting->message_id = $response->getMessageId();
    //         $voting->sent_at = time();
    //         $voting->save();
    //     }
    //
    //     return [];
    //}

    /**
     * @param ChatMarketplacePost $post
     * @param int $isNewMessage
     * @return array
     */
    private function prepareResponseBuilder(ChatMarketplacePost $post, bool $isNewMessage = true)
    {
        $chat = $post->chat;
        $chatMember = $post->chatMember;
        $user = $post->user;

        $buttons = [];
        $tags = [];

        $tags = ArrayHelper::getColumn($chatMember->getPhrases(ChatPhrase::TYPE_MARKETPLACE_TAGS)->asArray()->all(), 'text');

        if ($membershipTag = $chatMember->getMembershipTag()) {
            $tags = ArrayHelper::merge([
                $membershipTag,
            ], $tags);
        }

        $buttons[] = [
            [
                'url' => $user->getLink(),
                'text' => Yii::t('bot', 'Contact'),
            ],
        ];

        $buttons[] = [
            [
                'url' => $chatMember->getReviewsLink(),
                'text' => Yii::t('bot', 'Reviews') . ($chatMember->getPositiveReviewsCount() ? ' ' . Emoji::LIKE . ' ' . $chatMember->getPositiveReviewsCount() : '') . ($chatMember->getNegativeReviewsCount() ? ' ' . Emoji::DISLIKE . ' ' . $chatMember->getNegativeReviewsCount() : ''),
            ],
        ];

        if ($links = $chatMember->marketplaceLinks) {
            foreach ($links as $link) {
                if ($link->url && $link->title) {
                    $buttons[] = [
                        [
                            'url' => $link->url,
                            'text' => $link->title,
                        ],
                    ];
                }
            }
        }

        if ($isNewMessage) {
            return $this->getResponseBuilder()
                ->setChatId($chat->getChatId())
                ->sendMessage(
                    $this->render('public-view', [
                        'chat' => $chat,
                        'post' => $post,
                        'chatMember' => $chatMember,
                        'user' => $user,
                        'tags' => $tags,
                    ]),
                    $buttons,
                    [
                        'disablePreview' => true,
                    ]
                );
        } else {
            return $this->getResponseBuilder()
                ->setChatId($chat->getChatId())
                ->editMessage(
                    $post->getProviderMessageId(),
                    $this->render('public-view', [
                        'chat' => $chat,
                        'post' => $post,
                        'chatMember' => $chatMember,
                        'user' => $user,
                        'tags' => $tags,
                    ]),
                    $buttons,
                    [
                        'disablePreview' => true,
                    ]
                );
        }
    }
}
