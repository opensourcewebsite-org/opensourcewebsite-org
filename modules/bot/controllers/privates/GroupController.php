<?php

namespace app\modules\bot\controllers\privates;

use Yii;
use app\modules\bot\components\Controller;
use app\modules\bot\components\helpers\Emoji;
use yii\data\Pagination;
use app\modules\bot\components\helpers\PaginationButtons;
use app\modules\bot\models\Chat;
use app\modules\bot\models\ChatMember;
use app\modules\bot\models\ChatSetting;
use app\modules\bot\models\User;
use yii\helpers\ArrayHelper;

/**
 * Class GroupController
 *
 * @package app\modules\bot\controllers\privates
 */
class GroupController extends Controller
{
    /**
     * @return array
     */
    public function actionIndex($page = 1)
    {
        $chatQuery = $this->getTelegramUser()->getAdministratedGroups();

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

        $buttons[][] = [
            'callback_data' => MenuController::createRoute(),
            'text' => Emoji::MENU,
        ];

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('index'),
                $buttons
            )
            ->build();
    }

    /**
     * @return array
     */
    public function actionView($chatId = null)
    {
        if ($chatId) {
            $chat = Chat::findOne($chatId);

            if (!isset($chat)) {
                return [];
            }

            // TODO refactoring, для того чтобы ограничить доступ к настройкам группы
            if ($this->getUpdate()->getCallbackQuery()) {
                $admins = $chat->getAdministrators()->all();

                return $this->getResponseBuilder()
                    ->editMessageTextOrSendMessage(
                        $this->render('view', [
                            'chatTitle' => $chat->title,
                            'admins' => $admins,
                        ]),
                        [
                            [
                                [
                                    'callback_data' => GroupJoinHiderController::createRoute('index', [
                                        'chatId' => $chat->id,
                                    ]),
                                    'text' => call_user_func(
                                        function () use ($chat) {
                                            $statusSetting = $chat->getSetting(ChatSetting::JOIN_HIDER_STATUS);
                                            $statusOn = ($statusSetting->value == ChatSetting::JOIN_HIDER_STATUS_ON);

                                            return ($statusOn ? '' : Emoji::INACTIVE . ' ') . Yii::t('bot', 'Join Hider');
                                        }
                                    ),
                                ],
                            ],
                            [
                                [
                                    'callback_data' => GroupJoinCaptchaController::createRoute('index', [
                                        'chatId' => $chat->id,
                                    ]),
                                    'text' => call_user_func(
                                        function () use ($chat) {
                                            $statusSetting = $chat->getSetting(ChatSetting::JOIN_CAPTCHA_STATUS);
                                            $statusOn = ($statusSetting->value == ChatSetting::JOIN_CAPTCHA_STATUS_ON);

                                            return ($statusOn ? '' : Emoji::INACTIVE . ' ') . Yii::t('bot', 'Join Captcha');
                                        }
                                    ),
                                ],
                            ],
                            [
                                [
                                    'callback_data' => GroupGreetingController::createRoute('index', [
                                        'chatId' => $chat->id,
                                    ]),
                                    'text' => call_user_func(
                                        function () use ($chat) {
                                            $statusSetting = $chat->getSetting(ChatSetting::GREETING_STATUS);
                                            $statusOn = ($statusSetting->value == ChatSetting::GREETING_STATUS_ON);

                                            return ($statusOn ? '' : Emoji::INACTIVE . ' ') . Yii::t('bot', 'Greeting');
                                        }
                                    ),
                                ],
                            ],
                            [
                                [
                                    'callback_data' => GroupMessageFilterController::createRoute('index', [
                                        'chatId' => $chat->id,
                                    ]),
                                    'text' => call_user_func(
                                        function () use ($chat) {
                                            $statusSetting = $chat->getSetting(ChatSetting::FILTER_STATUS);
                                            $statusOn = ($statusSetting->value == ChatSetting::FILTER_STATUS_ON);

                                            return ($statusOn ? '' : Emoji::INACTIVE . ' ') . Yii::t('bot', 'Message Filter');
                                        }
                                    ),
                                ],
                            ],
                            [
                                [
                                    'callback_data' => GroupStarTopController::createRoute('index', [
                                        'chatId' => $chat->id,
                                    ]),
                                    'text' => call_user_func(
                                        function () use ($chat) {
                                            $statusSetting = $chat->getSetting(ChatSetting::STAR_TOP_STATUS);
                                            $statusOn = ($statusSetting->value == ChatSetting::STAR_TOP_STATUS_ON);

                                            return ($statusOn ? '' : Emoji::INACTIVE . ' ') . Yii::t('bot', 'Karma');
                                        }
                                    ),
                                ],
                            ],
                            [
                                [
                                    'callback_data' => GroupVoteBanController::createRoute('index', [
                                        'chatId' => $chat->id,
                                    ]),
                                    'text' => call_user_func(
                                        function () use ($chat) {
                                            $statusSetting = $chat->getSetting(ChatSetting::VOTE_BAN_STATUS);
                                            $statusOn = ($statusSetting->value == ChatSetting::VOTE_BAN_STATUS_ON);

                                            return ($statusOn ? '' : Emoji::INACTIVE . ' ') . Yii::t('bot', 'Vote Ban');
                                        }
                                    ),
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
