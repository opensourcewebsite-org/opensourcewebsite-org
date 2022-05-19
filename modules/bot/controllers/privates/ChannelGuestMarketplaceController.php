<?php

namespace app\modules\bot\controllers\privates;

use Yii;
use app\modules\bot\components\Controller;
use app\modules\bot\models\Chat;
use app\modules\bot\models\ChatSetting;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\models\BotChatMarketplacePost;
use yii\data\Pagination;
use app\modules\bot\components\helpers\PaginationButtons;
use app\modules\bot\components\helpers\MessageWithEntitiesConverter;

/**
 * Class ChannelGuestMarketplaceController
 *
 * @package app\modules\bot\controllers\privates
 */
class ChannelGuestMarketplaceController extends Controller
{
    /**
     * @return array
     */
    public function actionIndex($chatId = null, $page = 1)
    {
        $this->getState()->setName(null);

        $chat = Chat::findOne($chatId);

        if (!isset($chat) || !$chat->isChannel() || ($chat->marketplace_status != ChatSetting::STATUS_ON)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $query = BotChatMarketplacePost::find()
            ->where([
                'chat_id' => $chat->id,
                'user_id' => $this->user->id,
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
        ]);

        $pagination->pageSizeParam = false;
        $pagination->validatePage = true;

        $posts = $query->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();

        $paginationButtons = PaginationButtons::build($pagination, function ($page) use ($chat) {
            return self::createRoute('index', [
                'chatId' => $chat->id,
                'page' => $page,
            ]);
        });

        $buttons = [];

        if ($posts) {
            foreach ($posts as $post) {
                $buttons[][] = [
                    'callback_data' => self::createRoute('view', [
                        'postId' => $post->id,
                    ]),
                    'text' => ($post->isActive() ? '' : Emoji::INACTIVE . ' ') . ($post->title ?: '#' . $post->id),
                ];
            }

            if ($paginationButtons) {
                $buttons[] = $paginationButtons;
            }
        }

        $buttons[] = [
            [
                'callback_data' => ChannelGuestController::createRoute('view', [
                    'chatId' => $chat->id,
                ]),
                'text' => Emoji::BACK,
            ],
            [
                'callback_data' => MenuController::createRoute(),
                'text' => Emoji::MENU,
            ],
            [
                'callback_data' => self::createRoute('add', [
                    'chatId' => $chatId,
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

    public function actionAdd($chatId = null)
    {
        $chat = Chat::findOne($chatId);

        if (!isset($chat) || ($chat->marketplace_status != ChatSetting::STATUS_ON)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $this->getState()->setName(self::createRoute('add', [
            'chatId' => $chat->id,
        ]));

        if ($this->getUpdate()->getMessage()) {
            if ($text = MessageWithEntitiesConverter::toHtml($this->getUpdate()->getMessage())) {
                $post = new BotChatMarketplacePost();
                $post->user_id = $this->user->id;
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
                                'chatId' => $chat->id,
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

    public function actionView($postId = null)
    {
        $this->getState()->setName(null);

        $post = BotChatMarketplacePost::find()
            ->where([
                'id' => $postId,
                'user_id' => $this->user->id,
            ])
            ->one();

        if (!isset($post)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        if ((!$chat = $post->chat) || ($chat->marketplace_status != ChatSetting::STATUS_ON)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('view', [
                    'chat' => $chat,
                    'post' => $post,
                    'user' => $this->telegramUser,
                ]),
                [
                    [
                        [
                            'callback_data' => self::createRoute('set-status', [
                                'postId' => $post->id,
                            ]),
                            'text' => $post->isActive() ? Emoji::STATUS_ON . ' ON' : Emoji::STATUS_OFF . ' OFF',
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('set-title', [
                                'postId' => $post->id,
                            ]),
                            'text' => Yii::t('app', 'Title'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('set-text', [
                                'postId' => $post->id,
                            ]),
                            'text' => Yii::t('app', 'Text'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('index', [
                                'chatId' => $chat->id,
                            ]),
                            'text' => Emoji::BACK,
                        ],
                        [
                            'callback_data' => MenuController::createRoute(),
                            'text' => Emoji::MENU,
                        ],
                        [
                            'callback_data' => self::createRoute('delete', [
                                'postId' => $post->id,
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

    public function actionSetStatus($postId = null)
    {
        $this->getState()->setName(null);

        $post = BotChatMarketplacePost::find()
            ->where([
                'id' => $postId,
                'user_id' => $this->user->id,
            ])
            ->one();

        if (!isset($post)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        if ((!$chat = $post->chat) || ($chat->marketplace_status != ChatSetting::STATUS_ON)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        if ($post->isActive()) {
            $post->setInactive();

            if ($post->getProviderMessageId()) {
                $response = $this->getBotApi()->deleteMessage(
                    $post->chat->getChatId(),
                    $post->getProviderMessageId()
                );

                $post->provider_message_id = null;
            }
        } else {
            $activePostsCount = BotChatMarketplacePost::find()
                ->where([
                    'user_id' => $this->user->id,
                    'status' => BotChatMarketplacePost::STATUS_ON,
                ])
                ->count();

            if ($activePostsCount >= $chat->marketplace_active_post_limit_per_member) {
                return $this->getResponseBuilder()
                    ->answerCallbackQuery(
                        $this->render('alert-post-limit', [
                            'chat' => $chat,
                        ]),
                        true
                    )
                    ->build();
            }

            $post->setActive();

            if (!$post->getProviderMessageId()) {
                if ($post->canRepost()) {
                    $response = $this->getResponseBuilder()
                        ->setChatId($chat->getChatId())
                        ->sendMessage(
                            $this->render('channel-view', [
                                'post' => $post,
                                'user' => $this->telegramUser,
                            ]),
                            [],
                            [
                                'disablePreview' => true,
                            ]
                        )
                        ->send();

                    if ($response) {
                        $post->sent_at = time();
                        $post->provider_message_id = $response->getMessageId();
                    }
                } else {
                    return $this->getResponseBuilder()
                        ->answerCallbackQuery(
                            $this->render('alert-time-repost', [
                                'post' => $post,
                            ]),
                            true
                        )
                        ->build();
                }
            }
        }

        $post->save();

        return $this->actionView($post->id);
    }

    public function actionSetTitle($postId = null)
    {
        $post = BotChatMarketplacePost::find()
            ->where([
                'id' => $postId,
                'user_id' => $this->user->id,
            ])
            ->one();

        if (!isset($post)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        if ((!$chat = $post->chat) || ($chat->marketplace_status != ChatSetting::STATUS_ON)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $this->getState()->setName(self::createRoute('set-title', [
            'postId' => $post->id,
        ]));

        if ($this->getUpdate()->getMessage()) {
            if ($text = $this->getUpdate()->getMessage()->getText()) {
                $post->title = $text;

                if ($post->save()) {
                    $this->getState()->setName(null);

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
                                'postId' => $post->id,
                            ]),
                            'text' => Emoji::BACK,
                        ],
                    ],
                ]
            )
            ->build();
    }

    public function actionSetText($postId = null)
    {
        $post = BotChatMarketplacePost::find()
            ->where([
                'id' => $postId,
                'user_id' => $this->user->id,
            ])
            ->one();

        if (!isset($post)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        if ((!$chat = $post->chat) || ($chat->marketplace_status != ChatSetting::STATUS_ON)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $this->getState()->setName(self::createRoute('set-text', [
            'postId' => $post->id,
        ]));

        if ($this->getUpdate()->getMessage()) {
            if ($text = MessageWithEntitiesConverter::toHtml($this->getUpdate()->getMessage())) {
                $post->text = $text;

                if ($post->save()) {
                    if ($post->isActive() && $post->getProviderMessageId()) {
                        $response = $this->getResponseBuilder()
                            ->setChatId($chat->getChatId())
                            ->editMessage(
                                $post->getProviderMessageId(),
                                $this->render('channel-view', [
                                    'post' => $post,
                                    'user' => $this->telegramUser,
                                ]),
                                [],
                                [
                                    'disablePreview' => true,
                                ]
                            )
                            ->send();
                    }

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
                                'postId' => $post->id,
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

    public function actionDelete($postId = null): array
    {
        $post = BotChatMarketplacePost::find()
            ->where([
                'id' => $postId,
                'user_id' => $this->user->id,
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

        $chatId = $post->chat->id;
        $post->delete();

        return $this->actionIndex($chatId);
    }
}
