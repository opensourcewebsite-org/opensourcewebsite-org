<?php

namespace app\modules\bot\controllers\privates;

use app\modules\bot\components\Controller;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\helpers\MessageWithEntitiesConverter;
use app\modules\bot\components\helpers\PaginationButtons;
use app\modules\bot\models\Chat;
use app\modules\bot\models\ChatMarketplacePost;
use app\modules\bot\models\ChatSetting;
use Yii;
use yii\data\Pagination;

/**
 * Class ChannelGuestMarketplaceController
 *
 * @package app\modules\bot\controllers\privates
 */
class ChannelGuestMarketplaceController extends Controller
{
    /**
     * @param int $id Chat->id
     * @param int $page
     * @return array
     */
    public function actionIndex($id = null, $page = 1)
    {
        $chat = Chat::findOne($id);

        if (!isset($chat) || !$chat->isChannel() || ($chat->marketplace_status != ChatSetting::STATUS_ON)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $this->getState()->setName(null);

        $query = ChatMarketplacePost::find()
            ->where([
                'chat_id' => $chat->id,
                'user_id' => $this->globalUser->id,
            ])
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
                'callback_data' => ChannelGuestController::createRoute('view', [
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

        if (!isset($chat) || !$chat->isChannel() || ($chat->marketplace_status != ChatSetting::STATUS_ON)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $this->getState()->setName(self::createRoute('add', [
            'id' => $chat->id,
        ]));

        if ($this->getUpdate()->getMessage()) {
            if ($text = MessageWithEntitiesConverter::toHtml($this->getUpdate()->getMessage())) {
                $post = new ChatMarketplacePost();
                $post->user_id = $this->globalUser->id;
                $post->chat_id = $chat->id;
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
        $post = ChatMarketplacePost::find()
            ->where([
                'id' => $id,
                'user_id' => $this->globalUser->id,
            ])
            ->one();

        if (!isset($post)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        if ((!$chat = $post->chat) || !$chat->isChannel() || ($chat->marketplace_status != ChatSetting::STATUS_ON)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $this->getState()->setName(null);

        $user = $this->getTelegramUser();
        $chatMember = $chat->getChatMemberByUserId();

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('view', [
                    'chat' => $chat,
                    'post' => $post,
                    'chatMember' => $chatMember,
                    'user' => $user,
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
                    // TODO update and send a post (look GroupGuestMarketplaceController)
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
        $post = ChatMarketplacePost::find()
            ->where([
                'id' => $id,
                'user_id' => $this->globalUser->id,
            ])
            ->one();

        if (!isset($post)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        if ((!$chat = $post->chat) || !$chat->isChannel() || ($chat->marketplace_status != ChatSetting::STATUS_ON)) {
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
                    'user_id' => $this->globalUser->id,
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
        $post = ChatMarketplacePost::find()
            ->where([
                'id' => $id,
                'user_id' => $this->globalUser->id,
            ])
            ->one();

        if (!isset($post)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        if ((!$chat = $post->chat) || !$chat->isChannel() || ($chat->marketplace_status != ChatSetting::STATUS_ON)) {
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

                if ($post->save()) {
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
        $post = ChatMarketplacePost::find()
            ->where([
                'id' => $id,
                'user_id' => $this->globalUser->id,
            ])
            ->one();

        if (!isset($post)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        if ((!$chat = $post->chat) || !$chat->isChannel() || ($chat->marketplace_status != ChatSetting::STATUS_ON)) {
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

                if ($post->save()) {
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
    public function actionDelete($id = null)
    {
        $post = ChatMarketplacePost::find()
            ->where([
                'id' => $id,
                'user_id' => $this->globalUser->id,
            ])
            ->one();

        if (!isset($post)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        if ($post->getProviderMessageId()) {
            $this->getBotApi()->deleteMessage(
                $post->chat->getChatId(),
                $post->getProviderMessageId()
            );
        }

        $chatId = $post->getChatId();
        $post->delete();

        return $this->actionIndex($chatId);
    }
}
