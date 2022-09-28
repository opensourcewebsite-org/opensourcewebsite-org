<?php

namespace app\modules\bot\controllers\privates;

use app\modules\bot\components\Controller;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\helpers\PaginationButtons;
use app\modules\bot\models\Chat;
use app\modules\bot\models\ChatMember;
use app\modules\bot\models\ChatSetting;
use Yii;
use yii\data\Pagination;

/**
* Class GroupSlowModeController
*
* @package app\modules\bot\controllers\privates
*/
class GroupSlowModeController extends Controller
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
                $this->render('index', compact('chat')),
                [
                    [
                        [
                            'callback_data' => self::createRoute('set-status', [
                                'id' => $chat->id,
                            ]),
                            'text' => $chat->isSlowModeOn() ? Emoji::STATUS_ON . ' ON' : Emoji::STATUS_OFF . ' OFF',
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('members', [
                                'id' => $chat->id,
                            ]),
                            'text' => Yii::t('bot', 'Members with exceptions'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('set-messages-limit', [
                                'id' => $chat->id,
                            ]),
                            'text' => Yii::t('bot', 'Limit of messages'),
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

        if (!isset($chat) || !$chat->isGroup()) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        switch ($chat->slow_mode_status) {
            case ChatSetting::STATUS_ON:
                $chat->slow_mode_status = ChatSetting::STATUS_OFF;

                break;
            case ChatSetting::STATUS_OFF:
                $chatMember = $chat->getChatMemberByUserId();

                if (!$chatMember->trySetChatSetting('slow_mode_status', ChatSetting::STATUS_ON)) {
                    return $this->getResponseBuilder()
                        ->answerCallbackQuery(
                            $this->render('alert-status-on', [
                                'requiredRating' => $chatMember->getRequiredRatingForChatSetting('slow_mode_status', ChatSetting::STATUS_ON),
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
    public function actionSetMessagesLimit($id = null)
    {
        $chat = Chat::findOne($id);

        if (!isset($chat) || !$chat->isGroup()) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $this->getState()->setName(self::createRoute('set-messages-limit', [
            'id' => $chat->id,
        ]));

        if ($this->getUpdate()->getMessage()) {
            if ($text = (int)$this->getUpdate()->getMessage()->getText()) {
                if ($chat->validateSettingValue('slow_mode_messages_limit', $text)) {
                    $chat->slow_mode_messages_limit = $text;

                    return $this->runAction('index', [
                        'id' => $chat->id,
                    ]);
                }
            }
        }

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('set-messages-limit'),
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
    * @param int $page
    * @return array
    */
    public function actionMembers($id = null, $page = 1)
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

        $query = ChatMember::find()
            ->where([
                'chat_id' => $chat->id,
            ])
            ->andWhere([
                'not', ['slow_mode_messages_limit' => null],
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

        $paginationButtons = PaginationButtons::build($pagination, function ($page) use ($chat) {
            return self::createRoute('members', [
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
                    'text' => $member->slow_mode_messages_limit . ' - ' . $member->user->getDisplayName(),
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
                $this->render('members', [
                    'chat' => $chat,
                ]),
                $buttons
            )
            ->build();
    }

    /**
    * @param int $id Chat->id
    * @return array
    */
    public function actionInputMember($id = null)
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

        if (!$member->slow_mode_messages_limit) {
            $member->slow_mode_messages_limit = 1;
            $member->save(false);
        }

        return $this->runAction('member', [
            'id' => $member->id,
         ]);
    }

    /**
    * @param int $id ChatMember->id
    * @return array
    */
    public function actionMember($id = null)
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

        $this->getState()->setName(self::createRoute('input-member-messages-limit', [
            'id' => $member->id,
        ]));

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('member', [
                    'chat' => $chat,
                    'chatMember' => $member,
                ]),
                [
                    [
                        [
                            'callback_data' => self::createRoute('members', [
                                'id' => $chat->id,
                            ]),
                            'text' => Emoji::BACK,
                        ],
                        [
                            'callback_data' => MenuController::createRoute(),
                            'text' => Emoji::MENU,
                        ],
                        [
                            'callback_data' => self::createRoute('delete-member-messages-limit', [
                                'id' => $member->id,
                            ]),
                            'text' => Emoji::DELETE,
                        ],
                    ]
                ]
            )
            ->build();
    }

    /**
    * @param int $id ChatMember->id
    * @return array
    */
    public function actionInputMemberMessagesLimit($id = null)
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

        if ($this->getUpdate()->getMessage()) {
            if ($text = $this->getUpdate()->getMessage()->getText()) {
                $member->slow_mode_messages_limit = $text;

                if ($member->validate('slow_mode_messages_limit')) {
                    $member->save(false);

                    return $this->runAction('member', [
                        'id' => $member->id,
                     ]);
                }
            }
        }

        return $this->getResponseBuilder()
            ->answerCallbackQuery()
            ->build();
    }

    /**
    * @param int $id ChatMember->id
    * @return array
    */
    public function actionDeleteMemberMessagesLimit($id = null)
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

        $member->slow_mode_messages_limit = null;
        $member->save(false);

        return $this->runAction('members', [
             'id' => $chat->id,
         ]);
    }
}
