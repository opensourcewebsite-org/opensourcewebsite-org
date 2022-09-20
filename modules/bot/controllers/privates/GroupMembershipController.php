<?php

namespace app\modules\bot\controllers\privates;

use app\models\User as GlobalUser;
use app\modules\bot\components\Controller;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\helpers\PaginationButtons;
use app\modules\bot\controllers\groups\PremiumMembersController;
use app\modules\bot\models\Chat;
use app\modules\bot\models\ChatMember;
use app\modules\bot\models\ChatSetting;
use app\modules\bot\models\User;
use Yii;
use yii\data\Pagination;
use yii\validators\DateValidator;

/**
* Class GroupMembershipController
*
* @package app\modules\bot\controllers\privates
*/
class GroupMembershipController extends Controller
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
                            'text' => $chat->isMembershipOn() ? Emoji::STATUS_ON . ' ON' : Emoji::STATUS_OFF . ' OFF',
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('members', [
                                'id' => $chat->id,
                            ]),
                            'text' => Yii::t('bot', 'Members'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('set-tag', [
                                'id' => $chat->id,
                            ]),
                            'text' => Yii::t('bot', 'Tag for members'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('send-message', [
                                'id' => $chat->id,
                            ]),
                            'text' => Emoji::SEND . ' ' . Yii::t('bot', 'Send new message to the group'),
                            'visible' => $chat->getPremiumChatMembers()->exists(),
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

        switch ($chat->membership_status) {
            case ChatSetting::STATUS_ON:
                $chat->membership_status = ChatSetting::STATUS_OFF;

                break;
            case ChatSetting::STATUS_OFF:
                $chatMember = $chat->getChatMemberByUserId();

                if (!$chatMember->trySetChatSetting('membership_status', ChatSetting::STATUS_ON)) {
                    return $this->getResponseBuilder()
                        ->answerCallbackQuery(
                            $this->render('alert-status-on', [
                                'requiredRating' => $chatMember->getRequiredRatingForChatSetting('membership_status', ChatSetting::STATUS_ON),
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
    public function actionSetTag($id = null)
    {
        $chat = Chat::findOne($id);

        if (!isset($chat) || !$chat->isGroup()) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $this->getState()->setName(self::createRoute('set-tag', [
            'id' => $chat->id,
        ]));

        if ($this->getUpdate()->getMessage()) {
            if ($text = $this->getUpdate()->getMessage()->getText()) {
                if ($chat->validateSettingValue('membership_tag', $text)) {
                    $chat->membership_tag = $text;

                    return $this->runAction('index', [
                        'id' => $chat->id,
                    ]);
                }
            }
        }

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('set-tag'),
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
    public function actionMembers($id = null, $page = 1): array
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
                'not', ['membership_date' => null],
            ])
            ->orderBy([
                'membership_date' => SORT_ASC,
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
                    'text' => $member->membership_date . ' - ' . $member->user->getDisplayName(),
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

        if (!$member->membership_date) {
            $member->membership_date = Yii::$app->formatter->asDate('tomorrow');
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
    public function actionMember($id = null): array
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

        $this->getState()->setName(self::createRoute('input-member-date', [
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
                            'callback_data' => self::createRoute('set-member-note', [
                                'id' => $member->id,
                            ]),
                            'text' => Yii::t('bot', 'Note'),
                        ],
                    ],
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
                            'callback_data' => self::createRoute('delete-member-date', [
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
    public function actionInputMemberDate($id = null): array
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
                $dateValidator = new DateValidator();

                if ($dateValidator->validate($text)) {
                    $member->membership_date = Yii::$app->formatter->format($text, 'date');
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
    public function actionDeleteMemberDate($id = null): array
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

        $member->membership_date = null;
        $member->save(false);

        return $this->runAction('members', [
             'id' => $chat->id,
         ]);
    }

    /**
    * @param int $id ChatMember->id
    * @return array
    */
    public function actionSetMemberNote($id = null): array
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

        $this->getState()->setName(self::createRoute('set-member-note', [
            'id' => $member->id,
        ]));

        if ($this->getUpdate()->getMessage()) {
            if ($text = $this->getUpdate()->getMessage()->getText()) {
                $member->membership_note = $text;
                if ($member->validate('membership_note')) {
                    $member->save(false);

                    return $this->runAction('member', [
                        'id' => $member->id,
                     ]);
                }
            }
        }

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('set-member-note'),
                [
                    [
                        [
                            'callback_data' => self::createRoute('member', [
                                'id' => $member->id,
                            ]),
                            'text' => Emoji::BACK,
                        ],
                        [
                            'callback_data' => self::createRoute('delete-member-note', [
                                'id' => $member->id,
                            ]),
                            'text' => Emoji::DELETE,
                            'visible' => (bool)$member->getMembershipNote(),
                        ],
                    ],
                ]
            )
            ->build();
    }

    /**
    * @param int $id ChatMember->id
    * @return array
    */
    public function actionDeleteMemberNote($id = null): array
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

        $member->membership_note = null;
        $member->save(false);

        return $this->runAction('member', [
             'id' => $member->id,
         ]);
    }

    /**
     * @param int $id Chat->id
     * @return array
     */
    public function actionSendMessage($id = null)
    {
        $chat = Chat::findOne($id);

        if (!isset($chat) || !$chat->isGroup()) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $response = $this->getResponseBuilder()
            ->setChatId($chat->getChatId())
            ->sendMessage(
                $this->render('send-message'),
                [
                    [
                        [
                            'callback_data' => PremiumMembersController::createRoute(),
                            'text' => 'OK',
                        ],
                    ],
                ]
            )
            ->send();

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
}
