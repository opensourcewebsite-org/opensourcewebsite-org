<?php

namespace app\modules\bot\controllers\privates;

use app\modules\bot\components\Controller;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\helpers\ExternalLink;
use app\modules\bot\components\helpers\PaginationButtons;
use app\modules\bot\models\Chat;
use app\modules\bot\models\ChatMember;
use app\modules\bot\models\ChatSetting;
use app\modules\bot\models\User;
use Yii;
use yii\data\Pagination;
use yii\helpers\ArrayHelper;

/**
 * Class GroupController
 *
 * @package app\modules\bot\controllers\privates
 */
class GroupController extends Controller
{
    /**
     * @param int $page
     * @return array
     */
    public function actionIndex($page = 1)
    {
        $this->getState()->setName(null);

        $chatQuery = $this->getTelegramUser()->getActiveAdministratedGroups();

        $pagination = new Pagination([
            'totalCount' => $chatQuery->count(),
            'pageSize' => 9,
            'params' => [
                'page' => $page,
            ],
        ]);

        $pagination->pageSizeParam = false;
        $pagination->validatePage = true;

        $chats = $chatQuery->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();

        $paginationButtons = PaginationButtons::build($pagination, function ($page) {
            return self::createRoute('index', [
                'page' => $page,
            ]);
        });

        $buttons = [];

        if ($chats) {
            foreach ($chats as $chat) {
                $buttons[][] = [
                    'callback_data' => GroupController::createRoute('view', [
                        'chatId' => $chat->id,
                    ]),
                    'text' => $chat->title,
                ];
            }

            if ($paginationButtons) {
                $buttons[] = $paginationButtons;
            }
        }

        $buttons[] = [
            [
                'callback_data' => TelegramAdminController::createRoute(),
                'text' => Emoji::BACK,
            ],
            [
                'callback_data' => MenuController::createRoute(),
                'text' => Emoji::MENU,
            ],
            [
                'url' => ExternalLink::getBotToAddGroupLink(),
                'text' => Emoji::ADD,
            ],
        ];

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('index'),
                $buttons
            )
            ->build();
    }

    /**
     * @param int|null $chatId
     * @return array
     */
    public function actionView($chatId = null)
    {
        $this->getState()->setName(null);

        if ($chatId) {
            $chat = Chat::findOne($chatId);

            if (!isset($chat) || !$chat->isGroup()) {
                return $this->getResponseBuilder()
                    ->answerCallbackQuery()
                    ->build();
            }

            $user = $this->getTelegramUser();

            $chatMember = ChatMember::findOne([
                'chat_id' => $chat->id,
                'user_id' => $user->id,
            ]);

            if (!isset($chatMember)) {
                return $this->getResponseBuilder()
                    ->answerCallbackQuery()
                    ->build();
            }

            // TODO refactoring, для того чтобы ограничить доступ к настройкам группы
            if ($this->getUpdate()->getCallbackQuery()) {
                $administrators = $chat->getActiveAdministrators()->all();

                return $this->getResponseBuilder()
                    ->editMessageTextOrSendMessage(
                        $this->render('view', [
                            'chat' => $chat,
                            'administrators' => $administrators,
                        ]),
                        [
                            [
                                [
                                    'callback_data' => GroupAdministratorsController::createRoute('index', [
                                        'chatId' => $chat->id,
                                    ]),
                                    'text' => Yii::t('bot', 'Administrators'),
                                    'visible' => $chatMember->isCreator(),
                                ],
                            ],
                            [
                                [
                                    'callback_data' => GroupTimezoneController::createRoute('index', [
                                        'chatId' => $chat->id,
                                    ]),
                                    'text' => Yii::t('bot', 'Timezone'),
                                ],
                            ],
                            [
                                [
                                    'callback_data' => GroupBasicCommandsController::createRoute('index', [
                                        'chatId' => $chat->id,
                                    ]),
                                    'text' => ($chat->basic_commands_status == ChatSetting::STATUS_ON ? Emoji::STATUS_ON : Emoji::STATUS_OFF) . ' ' . Yii::t('bot', 'Basic Commands'),
                                ],
                            ],
                            [
                                [
                                    'callback_data' => GroupJoinHiderController::createRoute('index', [
                                        'chatId' => $chat->id,
                                    ]),
                                    'text' => ($chat->join_hider_status == ChatSetting::STATUS_ON ? Emoji::STATUS_ON : Emoji::STATUS_OFF) . ' ' . Yii::t('bot', 'Join Hider'),
                                ],
                            ],
                            [
                                [
                                    'callback_data' => GroupJoinCaptchaController::createRoute('index', [
                                        'chatId' => $chat->id,
                                    ]),
                                    'text' => ($chat->join_captcha_status == ChatSetting::STATUS_ON ? Emoji::STATUS_ON : Emoji::STATUS_OFF) . ' ' . Yii::t('bot', 'Join Captcha'),
                                ],
                            ],
                            [
                                [
                                    'callback_data' => GroupGreetingController::createRoute('index', [
                                        'chatId' => $chat->id,
                                    ]),
                                    'text' => ($chat->greeting_status == ChatSetting::STATUS_ON ? Emoji::STATUS_ON : Emoji::STATUS_OFF) . ' ' . Yii::t('bot', 'Greeting'),
                                ],
                            ],
                            [
                                [
                                    'callback_data' => GroupMembershipController::createRoute('index', [
                                        'chatId' => $chat->id,
                                    ]),
                                    'text' => ($chat->membership_status == ChatSetting::STATUS_ON ? Emoji::STATUS_ON : Emoji::STATUS_OFF) . ' ' . Yii::t('bot', 'Membership'),
                                ],
                            ],
                            [
                                [
                                    'callback_data' => GroupSlowModeController::createRoute('index', [
                                        'chatId' => $chat->id,
                                    ]),
                                    'text' => ($chat->slow_mode_status == ChatSetting::STATUS_ON ? Emoji::STATUS_ON : Emoji::STATUS_OFF) . ' ' . Yii::t('bot', 'Slow Mode'),
                                ],
                            ],
                            [
                                [
                                    'callback_data' => GroupMessageFilterController::createRoute('index', [
                                        'chatId' => $chat->id,
                                    ]),
                                    'text' => ($chat->filter_status == ChatSetting::STATUS_ON ? Emoji::STATUS_ON : Emoji::STATUS_OFF) . ' ' . Yii::t('bot', 'Message Filter'),
                                ],
                            ],
                            [
                                [
                                    'callback_data' => GroupLimiterController::createRoute('index', [
                                        'chatId' => $chat->id,
                                    ]),
                                    'text' => ($chat->limiter_status == ChatSetting::STATUS_ON ? Emoji::STATUS_ON : Emoji::STATUS_OFF) . ' ' . Yii::t('bot', 'Time Limiter'),
                                ],
                            ],
                            [
                                [
                                    'callback_data' => GroupFaqController::createRoute('index', [
                                        'chatId' => $chat->id,
                                    ]),
                                    'text' => ($chat->faq_status == ChatSetting::STATUS_ON ? Emoji::STATUS_ON : Emoji::STATUS_OFF) . ' ' . Yii::t('bot', 'FAQ'),
                                ],
                            ],
                            [
                                [
                                    'callback_data' => GroupStellarController::createRoute('index', [
                                        'chatId' => $chat->id,
                                    ]),
                                    'text' => ($chat->stellar_status == ChatSetting::STATUS_ON ? Emoji::STATUS_ON : Emoji::STATUS_OFF) . ' Stellar',
                                ],
                            ],
                            [
                                [
                                    'callback_data' => GroupController::createRoute(),
                                    'text' => Emoji::BACK,
                                ],
                                [
                                    'callback_data' => MenuController::createRoute(),
                                    'text' => Emoji::MENU,
                                ],
                                [
                                    'callback_data' => GroupRefreshController::createRoute('index', [
                                        'chatId' => $chat->id,
                                    ]),
                                    'text' => Emoji::REFRESH,
                                ],
                            ],
                        ]
                    )
                    ->build();
            }

            return [];
        }
    }
}
