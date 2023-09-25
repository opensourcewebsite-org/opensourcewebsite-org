<?php

namespace app\modules\bot\controllers\privates;

use app\modules\bot\components\Controller;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\helpers\ExternalLink;
use app\modules\bot\components\helpers\PaginationButtons;
use app\modules\bot\filters\GroupActiveAdministratorAccessFilter;
use Yii;
use yii\data\Pagination;

/**
 * Class GroupController
 *
 * @package app\modules\bot\controllers\privates
 */
class GroupController extends Controller
{
    public function behaviors()
    {
        return [
            'groupActiveAdministratorAccess' => [
                'class' => GroupActiveAdministratorAccessFilter::class,
                'only' => ['view'],
            ],
        ];
    }

    /**
     * @param int $page
     * @return array
     */
    public function actionIndex($page = 1)
    {
        $this->getState()->clearInputRoute();

        $query = $this->getTelegramUser()->getActiveAdministratedGroups();

        $pagination = new Pagination([
            'totalCount' => $query->count(),
            'pageSize' => 9,
            'params' => [
                'page' => $page,
            ],
            'pageSizeParam' => false,
            'validatePage' => true,
        ]);

        $buttons = [];

        $chats = $query->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();

        if ($chats) {
            foreach ($chats as $chat) {
                $buttons[][] = [
                    'callback_data' => self::createRoute('view', [
                        'chatId' => $chat->id,
                    ]),
                    'text' => $chat->title,
                ];
            }

            $paginationButtons = PaginationButtons::build($pagination, function ($page) {
                return self::createRoute('index', [
                    'page' => $page,
                ]);
            });

            if ($paginationButtons) {
                $buttons[] = $paginationButtons;
            }
        }

        $buttons[] = [
            [
                'callback_data' => TelegramController::createRoute(),
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
     * @param int $chatId Chat->id
     * @return array
     */
    public function actionView($chatId = null)
    {
        $chat = Yii::$app->cache->get('chat');
        $chatMember = Yii::$app->cache->get('chatMember');

        $this->getState()->clearInputRoute();

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
                                'id' => $chat->id,
                            ]),
                            'text' => Yii::t('bot', 'Administrators'),
                            'visible' => $chatMember->isCreator(),
                        ],
                    ],
                    [
                        [
                            'callback_data' => GroupTimezoneController::createRoute('index', [
                                'id' => $chat->id,
                            ]),
                            'text' => Emoji::EDIT . ' ' . Yii::t('bot', 'Timezone') . ': ' . $chat->getTimezoneName(),
                        ],
                    ],
                    [
                        [
                            'callback_data' => GroupLanguageController::createRoute('index', [
                                'id' => $chat->id,
                            ]),
                            'text' => Emoji::EDIT . ' ' . Yii::t('bot', 'Language') . (($language = $chat->language) ? ': ' . strtoupper($language->code) : ''),
                        ],
                    ],
                    [
                        [
                            'callback_data' => GroupCurrencyController::createRoute('index', [
                                'id' => $chat->id,
                            ]),
                            'text' => Emoji::EDIT . ' ' . Yii::t('bot', 'Currency') . (($currency = $chat->currency) ? ': ' . $currency->code : ''),
                        ],
                    ],
                    [
                        [
                            'callback_data' => GroupBasicCommandsController::createRoute('index', [
                                'id' => $chat->id,
                            ]),
                            'text' => ($chat->isBasicCommandsOn() ? Emoji::STATUS_ON : Emoji::STATUS_OFF) . ' ' . Yii::t('bot', 'Basic Commands'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => GroupJoinHiderController::createRoute('index', [
                                'id' => $chat->id,
                            ]),
                            'text' => ($chat->isJoinHiderOn() ? Emoji::STATUS_ON : Emoji::STATUS_OFF) . ' ' . Yii::t('bot', 'Notification Filter'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => GroupMessageFilterController::createRoute('index', [
                                'id' => $chat->id,
                            ]),
                            'text' => ($chat->isMessageFilterOn() ? Emoji::STATUS_ON : Emoji::STATUS_OFF) . ' ' . Yii::t('bot', 'Message Filter'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => GroupJoinCaptchaController::createRoute('index', [
                                'id' => $chat->id,
                            ]),
                            'text' => ($chat->isJoinCaptchaOn() ? Emoji::STATUS_ON : Emoji::STATUS_OFF) . ' ' . Yii::t('bot', 'Captcha'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => GroupGreetingController::createRoute('index', [
                                'id' => $chat->id,
                            ]),
                            'text' => ($chat->isGreetingOn() ? Emoji::STATUS_ON : Emoji::STATUS_OFF) . ' ' . Yii::t('bot', 'Greeting'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => GroupSlowModeController::createRoute('index', [
                                'id' => $chat->id,
                            ]),
                            'text' => ($chat->isSlowModeOn() ? Emoji::STATUS_ON : Emoji::STATUS_OFF) . ' ' . Yii::t('bot', 'Slow Mode'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => GroupMembershipController::createRoute('index', [
                                'id' => $chat->id,
                            ]),
                            'text' => ($chat->isMembershipOn() ? Emoji::STATUS_ON : Emoji::STATUS_OFF) . ' ' . Yii::t('bot', 'Membership'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => GroupInviterController::createRoute('index', [
                                'id' => $chat->id,
                            ]),
                            'text' => ($chat->isInviterOn() ? Emoji::STATUS_ON : Emoji::STATUS_OFF) . ' ' . Yii::t('bot', 'Inviter'),
                            'visible' => $chatMember->isCreator(),
                        ],
                    ],
                    [
                        [
                            'callback_data' => GroupPublisherController::createRoute('index', [
                                'id' => $chat->id,
                            ]),
                            'text' => ($chat->isPublisherOn() ? Emoji::STATUS_ON : Emoji::STATUS_OFF) . ' ' . Yii::t('bot', 'Publisher'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => GroupFaqController::createRoute('index', [
                                'id' => $chat->id,
                            ]),
                            'text' => ($chat->isFaqOn() ? Emoji::STATUS_ON : Emoji::STATUS_OFF) . ' ' . Yii::t('bot', 'Help Center'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => GroupNotifyNameChangeController::createRoute('index', [
                                'id' => $chat->id,
                            ]),
                            'text' => ($chat->isNotifyNameChangeOn() ? Emoji::STATUS_ON : Emoji::STATUS_OFF) . ' ' . Yii::t('bot', 'Notifier'),
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
                                'id' => $chat->id,
                            ]),
                            'text' => Emoji::REFRESH,
                        ],
                        [
                            'callback_data' => GroupGuestController::createRoute('view', [
                                'id' => $chat->id,
                            ]),
                            'text' => Emoji::EYE,
                        ],
                        [
                            'url' => ExternalLink::getTelegramAccountLink($chat->getUsername()),
                            'text' => Yii::t('bot', 'Group'),
                            'visible' => (bool)$chat->getUsername(),
                        ],
                    ],
                ],
                [
                    'disablePreview' => true,
                ]
            )
            ->build();
    }
}
