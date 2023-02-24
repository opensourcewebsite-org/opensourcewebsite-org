<?php

namespace app\modules\bot\controllers\privates;

use app\components\helpers\TimeHelper;
use app\modules\bot\components\Controller;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\helpers\MessageWithEntitiesConverter;
use app\modules\bot\components\helpers\PaginationButtons;
use app\modules\bot\models\Chat;
use app\modules\bot\models\ChatPublisherPost;
use app\modules\bot\models\ChatSetting;
use Yii;
use yii\data\Pagination;

/**
 * Class GroupPublisherController
 *
 * @package app\modules\bot\controllers\privates
 */
class GroupPublisherController extends Controller
{
    /**
    * @param int $id Chat->id
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
                $this->render('index', [
                    'chat' => $chat,
                ]),
                [
                    [
                        [
                            'callback_data' => self::createRoute('set-status', [
                                'id' => $chat->id,
                            ]),
                            'text' => $chat->isPublisherOn() ? Emoji::STATUS_ON . ' ON' : Emoji::STATUS_OFF . ' OFF',
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('posts', [
                                'id' => $chat->id,
                            ]),
                            'text' => Yii::t('bot', 'Posts'),
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
     */
    public function actionSetStatus($id = null)
    {
        $chat = Chat::findOne($id);

        if (!isset($chat) || !$chat->isGroup()) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        switch ($chat->publisher_status) {
            case ChatSetting::STATUS_ON:
                $chat->publisher_status = ChatSetting::STATUS_OFF;

                break;
            case ChatSetting::STATUS_OFF:
                $chatMember = $chat->getChatMemberByUserId();

                if (!$chatMember->trySetChatSetting('publisher_status', ChatSetting::STATUS_ON)) {
                    return $this->getResponseBuilder()
                        ->answerCallbackQuery(
                            $this->render('alert-status-on', [
                                'requiredRating' => $chatMember->getRequiredRatingForChatSetting('publisher_status', ChatSetting::STATUS_ON),
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
    public function actionPosts($id = null, $page = 1)
    {
        $chat = Chat::findOne($id);

        if (!isset($chat) || !$chat->isGroup()) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $this->getState()->setName(null);

        $chatMember = $chat->getChatMemberByUserId();

        if (!isset($chatMember) || !$chatMember->isActiveAdministrator()) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $query = $chat->getPublisherPosts()
            ->orderBy([
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
                    'callback_data' => self::createRoute('post', [
                        'id' => $post->id,
                    ]),
                    'text' => ($post->isActive() ? '' : Emoji::INACTIVE . ' ') . '#' . $post->id,
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
            [
                'callback_data' => self::createRoute('add', [
                    'id' => $chat->id,
                ]),
                'text' => Emoji::ADD,
            ],
        ];

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('posts'),
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

        if (!isset($chat) || !$chat->isGroup()) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $this->getState()->setName(self::createRoute('add', [
            'id' => $chat->id,
        ]));

        $chatMember = $chat->getChatMemberByUserId();

        if (!isset($chatMember) || !$chatMember->isActiveAdministrator()) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        if ($this->getUpdate()->getMessage()) {
            if ($text = MessageWithEntitiesConverter::toHtml($this->getUpdate()->getMessage())) {
                $post = new ChatPublisherPost();
                $post->chat_id = $chat->id;
                $post->text = $text;

                if ($post->save()) {
                    return $this->actionPosts($id);
                }
            }
        }

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('set-post-text', [
                    'chat' => $chat,
                ]),
                [
                    [
                        [
                            'callback_data' =>  self::createRoute('posts', [
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
     * @param int $id ChatPublisherPost->id
     * @return array
     */
    public function actionPost($id = null)
    {
        $post = ChatPublisherPost::findOne($id);

        if (!isset($post)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        if (!($chat = $post->chat) || !$chat->isGroup()) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $chatMember = $chat->getChatMemberByUserId();

        if (!isset($chatMember) || !$chatMember->isActiveAdministrator()) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $this->getState()->setName(null);

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('post', [
                    'post' => $post,
                    'chat' => $chat,
                ]),
                [
                    [
                        [
                            'callback_data' => self::createRoute('set-post-status', [
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
                            'text' => Yii::t('bot', 'Time of day') . ': ' . $post->getTimeOfDay(),
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
                            'callback_data' => self::createRoute('set-text', [
                                'id' => $post->id,
                            ]),
                            'text' => Yii::t('app', 'Text'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('send-group-message', [
                                'id' => $post->id,
                            ]),
                            'text' => Emoji::SEND . ' ' . Yii::t('bot', 'Send new post to the group'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('posts', [
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
     * @param int $id ChatPublisherPost->id
     * @return array
     */
    public function actionSetPostStatus($id = null)
    {
        $post = ChatPublisherPost::findOne($id);

        if (!isset($post)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        if ((!$chat = $post->chat) || !$chat->isGroup()) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $chatMember = $chat->getChatMemberByUserId();

        if (!isset($chatMember) || !$chatMember->isActiveAdministrator()) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $this->getState()->setName(null);

        if ($post->isActive()) {
            $post->setInactive();
            $post->save(false);
        } else {
            $post->setActive();
            $post->save(false);
        }

        return $this->actionPost($post->id);
    }

    /**
     * @param int $id ChatPublisherPost->id
     * @return array
     */
    public function actionSetTime($id = null)
    {
        $post = ChatPublisherPost::findOne($id);

        if (!isset($post)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        if ((!$chat = $post->chat) || !$chat->isGroup()) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $chatMember = $chat->getChatMemberByUserId();

        if (!isset($chatMember) || !$chatMember->isActiveAdministrator()) {
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
                    return $this->actionPost($post->id);
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
                            'callback_data' =>  self::createRoute('post', [
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
     * @param int $id ChatPublisherPost->id
     * @return array
     */
    public function actionSetSkipDays($id = null)
    {
        $post = ChatPublisherPost::findOne($id);

        if (!isset($post)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        if ((!$chat = $post->chat) || !$chat->isGroup()) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $chatMember = $chat->getChatMemberByUserId();

        if (!isset($chatMember) || !$chatMember->isActiveAdministrator()) {
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
                    return $this->actionPost($post->id);
                }
            }
        }

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('set-skip-days'),
                [
                    [
                        [
                            'callback_data' =>  self::createRoute('post', [
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
     * @param int $id ChatPublisherPost->id
     * @return array
     */
    public function actionSetText($id = null)
    {
        $post = ChatPublisherPost::findOne($id);

        if (!isset($post)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        if ((!$chat = $post->chat) || !$chat->isGroup()) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $chatMember = $chat->getChatMemberByUserId();

        if (!isset($chatMember) || !$chatMember->isActiveAdministrator()) {
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

                if ($post->validate('text')) {
                    $post->save(false);
                }
            }
        }

        $messageMarkdown = MessageWithEntitiesConverter::fromHtml($post->text ?? '');

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('set-post-text', [
                    'chat' => $chat,
                    'messageMarkdown' => $messageMarkdown,
                ]),
                [
                    [
                        [
                            'callback_data' =>  self::createRoute('post', [
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
     * @param int $id ChatPublisherPost->id
     * @return array
     */
    public function actionSendGroupMessage($id = null)
    {
        $post = ChatPublisherPost::findOne($id);

        if (!isset($post) || !$post->isActive()) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        if ((!$chat = $post->chat) || !$chat->isGroup()) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $chatMember = $chat->getChatMemberByUserId();

        if (!isset($chatMember) || !$chatMember->isActiveAdministrator()) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $thisChat = $this->chat;
        $module = Yii::$app->getModule('bot');
        $module->setChat($chat);
        $response = $module->runAction('publisher/send-message', [
            'id' => $post->id,
        ]);
        $module->setChat($thisChat);

        if ($response) {
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
     * @param int $id ChatPublisherPost->id
     * @return array
     */
    public function actionDelete($id = null)
    {
        $post = ChatPublisherPost::findOne($id);

        if (!isset($post)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        if ((!$chat = $post->chat) || !$chat->isGroup()) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $chatMember = $chat->getChatMemberByUserId();

        if (!isset($chatMember) || !$chatMember->isActiveAdministrator()) {
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

        return $this->actionPosts($chat->id);
    }
}
