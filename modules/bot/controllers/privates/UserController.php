<?php

namespace app\modules\bot\controllers\privates;

use app\models\Contact;
use app\modules\bot\components\Controller;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\helpers\PaginationButtons;
use app\modules\bot\models\Chat;
use app\modules\bot\models\User;
use Yii;
use yii\data\Pagination;

/**
 * Class UserController
 *
 * @package app\modules\bot\controllers\privates
 */
class UserController extends Controller
{
    /**
     * @return array
     */
    public function actionMessage()
    {
        if ($forwardFromUser = $this->getMessage()->getForwardFrom()) {
            $providerUserId = $forwardFromUser->getId();
        }

        if (!isset($providerUserId)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        return $this->runAction('id', [
            'id' => $providerUserId,
        ]);
    }

    /**
     * @return array
     */
    public function actionId($id = null)
    {
        if ($id) {
            $providerUserId = $id;
        } elseif ($text = $this->getMessage()->getText()) {
            if (preg_match('/(?:^(?:[0-9]+))/i', $text, $matches)) {
                $providerUserId = $matches[0];
            }
        }

        if (!isset($providerUserId)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $this->getState()->setName(null);

        $user = $this->getTelegramUser();

        if ($user->provider_user_id == $providerUserId) {
            return $this->run('my-profile/index');
        }

        $viewUser = User::findOne([
            'provider_user_id' => $providerUserId,
            'is_bot' => 0,
        ]);

        if (!isset($viewUser)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('index', [
                    'user' => $viewUser,
                    'contact' => $viewUser->globalUser->contact ?: $viewUser->globalUser->newContact,
                ]),
                [
                    [
                        [
                            'callback_data' => self::createRoute('public-groups', [
                                'id' => $viewUser->provider_user_id,
                            ]),
                            'text' => Yii::t('bot', 'Public groups'),
                            'visible' => $viewUser->getPublicGroups()->exists(),
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('input-name', [
                                'id' => $viewUser->provider_user_id,
                            ]),
                            'text' => Yii::t('user', 'Name'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('select-is-real', [
                                'id' => $viewUser->provider_user_id,
                            ]),
                            'text' => Yii::t('app', 'Personal identification'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('select-relation', [
                                'id' => $viewUser->provider_user_id,
                            ]),
                            'text' => Yii::t('app', 'Personal relation'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => ContactController::createRoute(),
                            'text' => Emoji::BACK,
                        ],
                        [
                            'callback_data' => MenuController::createRoute(),
                            'text' => Emoji::MENU,
                        ],
                        [
                            'callback_data' => self::createRoute('refresh', [
                                'id' => $viewUser->provider_user_id,
                            ]),
                            'text' => Emoji::REFRESH,
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
     * @return array
     */
    public function actionUsername()
    {
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

        $user = $this->getTelegramUser();

        if ($user->provider_user_name == $username) {
            return $this->run('my-profile/index');
        }

        $viewUser = User::findOne([
            'provider_user_name' => $username,
            'is_bot' => 0,
        ]);

        if (isset($viewUser)) {
            return $this->runAction('id', [
                'id' => $viewUser->provider_user_id,
            ]);
        } else {
            $chat = Chat::findOne([
                'username' => $username,
            ]);

            if (isset($chat)) {
                if ($chat->isGroup()) {
                    return $this->run('group-guest/view', [
                        'id' => $chat->id,
                    ]);
                } elseif ($chat->isChannel()) {
                    return $this->run('channel-guest/view', [
                        'id' => $chat->id,
                    ]);
                }
            }
        }

        return $this->getResponseBuilder()
            ->answerCallbackQuery()
            ->build();
    }

    /**
     * @return array
     */
    public function actionRefresh($id = null)
    {
        if (!$id) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $viewUser = User::findOne([
            'provider_user_id' => $id,
            'is_bot' => 0,
        ]);

        if (!isset($viewUser)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        foreach ($viewUser->chatMembers as $chatMember) {
            $botApiChatMember = $this->getBotApi()->getChatMember(
                $chatMember->chat->getChatId(),
                $viewUser->provider_user_id
            );

            if ($botApiChatMember) {
                $botApiUser = $botApiChatMember->getUser();

                $viewUser->setAttributes([
                    'provider_user_name' => $botApiUser->getUsername(),
                    'provider_user_first_name' => $botApiUser->getFirstName(),
                    'provider_user_last_name' => $botApiUser->getLastName(),
                ]);

                $viewUser->save(false);

                break;
            }
        }

        return $this->runAction('id', [
            'id' => $id,
        ]);
    }

    /**
     * @return array
     */
    public function actionInputName($id = null)
    {
        if (!$id) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $viewUser = User::findOne([
            'provider_user_id' => $id,
            'is_bot' => 0,
        ]);

        if (!isset($viewUser)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $this->getState()->setName(self::createRoute('input-name', [
            'id' => $id,
        ]));

        if ($this->getUpdate()->getMessage()) {
            if ($text = $this->getUpdate()->getMessage()->getText()) {
                $contact = $viewUser->globalUser->contact ?: $viewUser->globalUser->newContact;
                $contact->name = $text;

                if ($contact->validate('name')) {
                    $contact->save(false);

                    return $this->actionId([
                        'id' => $id,
                    ]);
                }
            }
        }

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('input-name'),
                [
                    [
                        [
                            'callback_data' => self::createRoute('id', [
                                'id' => $id,
                            ]),
                            'text' => Emoji::BACK,
                        ],
                        [
                            'callback_data' => MenuController::createRoute(),
                            'text' => Emoji::MENU,
                        ],
                        [
                            'callback_data' => self::createRoute('delete-name', [
                                'id' => $id,
                            ]),
                            'text' => Emoji::DELETE,
                        ],
                    ]
                ]
            )
            ->build();
    }

    /**
     * @return array
     */
    public function actionDeleteName($id = null)
    {
        if (!$id) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $viewUser = User::findOne([
            'provider_user_id' => $id,
            'is_bot' => 0,
        ]);

        if (!isset($viewUser)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $contact = $viewUser->globalUser->contact;

        if (!isset($contact)) {
            return $this->getResponseBuilder()
            ->answerCallbackQuery()
            ->build();
        }

        $contact->name = null;
        $contact->save(false);

        return $this->actionId([
            'id' => $id,
        ]);
    }

    /**
     * @return array
     */
    public function actionSelectIsReal($id = null, $v = null)
    {
        if (!$id) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $viewUser = User::findOne([
            'provider_user_id' => $id,
            'is_bot' => 0,
        ]);

        if (!isset($viewUser)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        if (isset($v)) {
            $contact = $viewUser->globalUser->contact ?: $viewUser->globalUser->newContact;
            $contact->is_real = $v;

            if ($contact->validate('is_real')) {
                $contact->save(false);

                return $this->actionId([
                    'id' => $id,
                ]);
            }
        }

        $buttons = [];

        foreach (Contact::getIsRealLabels() as $key => $name) {
            $buttons[][] = [
                'callback_data' => self::createRoute('select-is-real', [
                    'id' => $id,
                    'v' => $key,
                ]),
                'text' => Yii::t('bot', $name),
            ];
        }

        $buttons[] = [
            [
                'callback_data' => self::createRoute('id', [
                    'id' => $id,
                ]),
                'text' => Emoji::BACK,
            ],
        ];

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('select-is-real'),
                $buttons
            )
            ->build();
    }

    /**
     * @return array
     */
    public function actionSelectRelation($id = null, $v = null)
    {
        if (!$id) {
            return $this->getResponseBuilder()
                 ->answerCallbackQuery()
                 ->build();
        }

        $viewUser = User::findOne([
             'provider_user_id' => $id,
             'is_bot' => 0,
         ]);

        if (!isset($viewUser)) {
            return $this->getResponseBuilder()
                 ->answerCallbackQuery()
                 ->build();
        }

        if (isset($v)) {
            $contact = $viewUser->globalUser->contact ?: $viewUser->globalUser->newContact;
            $contact->relation = $v;

            if ($contact->validate('relation')) {
                $contact->save(false);

                return $this->actionId([
                     'id' => $id,
                 ]);
            }
        }

        $buttons = [];

        foreach (Contact::getRelationLabels() as $key => $name) {
            $buttons[][] = [
                 'callback_data' => self::createRoute('select-relation', [
                     'id' => $id,
                     'v' => $key,
                 ]),
                 'text' => Yii::t('bot', $name),
             ];
        }

        $buttons[] = [
             [
                 'callback_data' => self::createRoute('id', [
                     'id' => $id,
                 ]),
                 'text' => Emoji::BACK,
             ],
         ];

        return $this->getResponseBuilder()
             ->editMessageTextOrSendMessage(
                 $this->render('select-relation'),
                 $buttons
             )
             ->build();
    }

    /**
     * @param int $page
     * @param int|null $id User->provider_user_id
     * @return array
     */
    public function actionPublicGroups($page = 1, $id = null)
    {
        if (!$id) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $viewUser = User::findOne([
            'provider_user_id' => $id,
            'is_bot' => 0,
        ]);

        if (!isset($viewUser)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $this->getState()->setName(null);

        $query = $viewUser->getPublicGroups()
            ->orderByCreatorRank();

        $pagination = new Pagination([
            'totalCount' => $query->count(),
            'pageSize' => 9,
            'params' => [
                'page' => $page,
            ],
            'pageSizeParam' => false,
            'validatePage' => true,
        ]);

        $chats = $query->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();

        $paginationButtons = PaginationButtons::build($pagination, function ($page) {
            return self::createRoute('public-groups', [
                'page' => $page,
            ]);
        });

        $buttons = [];

        if ($chats) {
            foreach ($chats as $chat) {
                $chatMember = $chat->getChatMemberByUserId($viewUser->id);
                $buttons[][] = [
                    'callback_data' => MemberController::createRoute('id', [
                        'id' => $chatMember->id,
                    ]),
                    'text' => $chat->title,
                ];
            }

            if ($paginationButtons) {
                $buttons[] = $paginationButtons;
            }
        } else {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $buttons[] = [
            [
                'callback_data' => self::createRoute('id', [
                    'id' => $id,
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
                $this->render('index', [
                    'user' => $viewUser,
                    'contact' => $viewUser->globalUser->contact ?: $viewUser->globalUser->newContact,
                ]),
                $buttons,
                [
                    'disablePreview' => true,
                ]
            )
            ->build();
    }
}
