<?php

namespace app\modules\bot\controllers\privates;

use app\models\User as GlobalUser;
use app\modules\bot\components\Controller;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\helpers\PaginationButtons;
use app\modules\bot\models\Chat;
use app\modules\bot\models\User;
use app\modules\bot\models\ChatMember;
use app\modules\bot\models\ChatSetting;
use Yii;
use yii\data\Pagination;
use yii\validators\DateValidator;

/**
* Class GroupLimiterController
*
* @package app\modules\bot\controllers\privates
*/
class GroupLimiterController extends Controller
{
    /**
    * @return array
    */
    public function actionIndex($chatId = null)
    {
        $chat = Chat::findOne($chatId);

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
                                'chatId' => $chatId,
                            ]),
                            'text' => $chat->limiter_status == ChatSetting::STATUS_ON ? Emoji::STATUS_ON . ' ON' : Emoji::STATUS_OFF . ' OFF',
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('members', [
                                'chatId' => $chatId,
                            ]),
                            'text' => Yii::t('bot', 'Members'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => GroupController::createRoute('view', [
                                'chatId' => $chatId,
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

    public function actionSetStatus($chatId = null)
    {
        $chat = Chat::findOne($chatId);

        if (!isset($chat)) {
            return [];
        }

        switch ($chat->limiter_status) {
            case ChatSetting::STATUS_ON:
                $chat->limiter_status = ChatSetting::STATUS_OFF;

                break;
            case ChatSetting::STATUS_OFF:
                $chatMember = $chat->getChatMemberByUserId();

                 if (!$chatMember->trySetChatSetting('limiter_status', ChatSetting::STATUS_ON)) {
                     return $this->getResponseBuilder()
                         ->answerCallbackQuery(
                             $this->render('alert-status-on', [
                                 'requiredRating' => $chatMember->getRequiredRatingForChatSetting('limiter_status', ChatSetting::STATUS_ON),
                             ]),
                             true
                         )
                         ->build();
                 }

                break;
        }

        return $this->actionIndex($chatId);
    }

    public function actionMembers($page = 1, $chatId = null): array
    {
        $chat = Chat::findOne($chatId);

        if (!isset($chat) || !$chat->isGroup()) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $this->getState()->setName(self::createRoute('input-member', [
            'chatId' => $chat->id,
        ]));

        $query = ChatMember::find()
            ->where([
                'chat_id' => $chat->id,
            ])
            ->andWhere([
                'not', ['limiter_date' => null],
            ])
            ->orderBy([
                'limiter_date' => SORT_ASC,
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
                'chatId' => $chat->id,
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
                        'chatId' => $chatId,
                        'memberId' => $member->id,
                    ]),
                    'text' => $member->limiter_date . ' - ' . ($member->user->provider_user_name ? '@' . $member->user->provider_user_name . ' - ' : '') . $member->user->getFullName(),
                ];
            }

            if ($paginationButtons) {
                $buttons[] = $paginationButtons;
            }
        }

        $buttons[] = [
            [
                'callback_data' => self::createRoute('index', [
                    'chatId' => $chatId,
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

    public function actionInputMember($chatId = null): array
    {
        $chat = Chat::findOne($chatId);

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
                'chat_id' => $chatId,
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

        $member->limiter_date = Yii::$app->formatter->asDate('tomorrow');
        $member->save(false);

        return $this->runAction('member', [
            'chatId' => $chatId,
            'memberId' => $member->id,
         ]);
    }

    public function actionMember($memberId = null, $chatId = null): array
    {
        $chat = Chat::findOne($chatId);

        if (!isset($chat) || !$chat->isGroup()) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $member = ChatMember::findOne([
            'id' => $memberId,
            'chat_id' => $chatId,
        ]);

        if (!isset($member)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $this->getState()->setName(self::createRoute('input-date', [
            'chatId' => $chat->id,
            'memberId' => $memberId,
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
                                'chatId' => $chatId,
                            ]),
                            'text' => Emoji::BACK,
                        ],
                        [
                            'callback_data' => MenuController::createRoute(),
                            'text' => Emoji::MENU,
                        ],
                        [
                            'callback_data' => self::createRoute('delete-date', [
                                'chatId' => $chatId,
                                'memberId' => $memberId,
                            ]),
                            'text' => Emoji::DELETE,
                        ],
                    ]
                ]
            )
            ->build();
    }

    public function actionInputDate($memberId = null, $chatId = null): array
    {
        $chat = Chat::findOne($chatId);

        if (!isset($chat) || !$chat->isGroup()) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $member = ChatMember::findOne([
            'id' => $memberId,
            'chat_id' => $chatId,
        ]);

        if (!isset($member)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        if ($this->getUpdate()->getMessage()) {
            if ($text = $this->getUpdate()->getMessage()->getText()) {
                $dateValidator = new DateValidator();

                if ($dateValidator->validate($text)) {
                    $member->limiter_date = Yii::$app->formatter->format($text, 'date');
                    $member->save();

                    return $this->runAction('member', [
                        'chatId' => $chatId,
                        'memberId' => $member->id,
                     ]);
                }
            }
        }

        return $this->getResponseBuilder()
            ->answerCallbackQuery()
            ->build();
    }

    public function actionDeleteDate($memberId = null, $chatId = null): array
    {
        $chat = Chat::findOne($chatId);

        if (!isset($chat) || !$chat->isGroup()) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $member = ChatMember::findOne([
            'id' => $memberId,
            'chat_id' => $chatId,
        ]);

        if (!isset($member)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $member->limiter_date = null;
        $member->save(false);

        return $this->runAction('members', [
             'chatId' => $chatId,
         ]);
    }
}
