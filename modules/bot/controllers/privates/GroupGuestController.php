<?php

namespace app\modules\bot\controllers\privates;

use app\modules\bot\components\Controller;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\helpers\ExternalLink;
use app\modules\bot\components\helpers\MessageWithEntitiesConverter;
use app\modules\bot\components\helpers\PaginationButtons;
use app\modules\bot\models\Chat;
use app\modules\bot\models\ChatMember;
use app\modules\bot\models\ChatSetting;
use Yii;
use yii\data\Pagination;

/**
 * Class GroupGuestController
 *
 * @package app\modules\bot\controllers\privates
 */
class GroupGuestController extends Controller
{
    /**
     * @param int $id Chat->id
     * @return array
     */
    public function actionView($id = null)
    {
        $chat = Chat::findOne($id);

        if (!isset($chat) || !$chat->isGroup()) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $this->getState()->setName(null);

        $buttons = [];

        $chatMember = $chat->getChatMemberByUserId();

        if ($chatMember) {
            $buttons[] = [
                [
                    'callback_data' => self::createRoute('input-intro-text', [
                        'id' => $chatMember->id,
                    ]),
                    'text' => Yii::t('bot', 'Your public intro'),
                ],
            ];

            $buttons[] = [
                [
                    'callback_data' => MemberReviewController::createRoute('index', [
                        'id' => $chatMember->id,
                    ]),
                    'text' => Yii::t('bot', 'Reviews') . ($chatMember->getPositiveReviewsCount() ? ' ' . Emoji::LIKE . ' ' . $chatMember->getPositiveReviewsCount() : '') . ($chatMember->getNegativeReviewsCount() ? ' ' . Emoji::DISLIKE . ' ' . $chatMember->getNegativeReviewsCount() : ''),
                    'visible' => $chatMember->getActiveReviews()->exists(),
                ],
            ];

            if ($chatMember->canUseMarketplace() || $chatMember->isCreator()) {
                $buttons[] = [
                    [
                        'callback_data' => GroupGuestMarketplaceController::createRoute('index', [
                            'id' => $chat->id,
                        ]),
                        'text' => Yii::t('bot', 'Your posts'),
                    ],
                ];
            }
        }

        if ($chat->faq_status == ChatSetting::STATUS_ON) {
            $buttons[] = [
                [
                    'callback_data' => GroupGuestFaqController::createRoute('word-list', [
                        'chatId' => $chat->id,
                    ]),
                    'text' => Yii::t('bot', 'FAQ'),
                ],
            ];
        }

        $buttons[] = [
            [
                'callback_data' => self::createRoute('premium-members', [
                    'id' => $chat->id,
                ]),
                'text' => Yii::t('bot', 'Premium members'),
                'visible' => ($chat->membership_status == ChatSetting::STATUS_ON),
            ],
        ];

        $buttons[] = [
            [
                'callback_data' => self::createRoute('members-with-intro', [
                    'id' => $chat->id,
                ]),
                'text' => Yii::t('bot', 'Members with intro'),
                'visible' => (bool)$chat->getUsername(),
            ],
        ];

        $buttons[] = [
            [
                'callback_data' => self::createRoute('members-with-reviews', [
                    'id' => $chat->id,
                ]),
                'text' => Yii::t('bot', 'Members with reviews'),
                'visible' => (bool)$chat->getUsername(),
            ],
        ];

        $buttons[] = [
            [
                'callback_data' => MenuController::createRoute(),
                'text' => Emoji::MENU,
            ],
            [
                'url' => ExternalLink::getTelegramAccountLink($chat->getUsername()),
                'text' => Yii::t('bot', 'Group'),
                'visible' => (bool)$chat->getUsername(),
            ],
        ];

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('view', [
                    'chat' => $chat,
                    'user' => $this->getTelegramUser(),
                    'chatMember' => $chatMember,
                ]),
                $buttons,
                [
                    'disablePreview' => true,
                ]
            )
            ->build();
    }

    /**
     * @param int $id ChatMember->id
     * @return array
     */
    public function actionInputIntroText($id = null)
    {
        $chatMember = ChatMember::findOne([
            'id' => $id,
        ]);

        if (!isset($chatMember)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $this->getState()->setName(self::createRoute('input-intro-text', [
                'id' => $chatMember->id,
            ]));

        if ($this->getUpdate()->getMessage()) {
            if ($text = MessageWithEntitiesConverter::toHtml($this->getUpdate()->getMessage())) {
                $chatMember->intro = $text;

                if ($chatMember->validate('intro')) {
                    $chatMember->save(false);

                    return $this->runAction('view', [
                         'id' => $chatMember->getChatId(),
                     ]);
                }
            }
        }

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('input-intro-text', [
                    'chatMember' => $chatMember,
                ]),
                [
                    [
                        [
                            'callback_data' => self::createRoute('view', [
                                'id' => $chatMember->getChatId(),
                            ]),
                            'text' => Emoji::BACK,
                        ],
                        [
                            'callback_data' => self::createRoute('delete-intro', [
                                'id' => $chatMember->id,
                            ]),
                            'text' => Emoji::DELETE,
                            'visible' => (bool)$chatMember->intro,
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
     * @param int $id ChatMember->id
     * @return array
     */
    public function actionDeleteIntro($id = null)
    {
        $chatMember = ChatMember::findOne([
            'id' => $id,
        ]);

        if (!isset($chatMember)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $chatMember->intro = null;
        $chatMember->save(false);

        return $this->runAction('view', [
             'id' => $chatMember->getChatId(),
         ]);
    }

    /**
     * @param int $page
     * @param int $id Chat->id
     * @return array
     */
    public function actionPremiumMembers($page = 1, $id = null)
    {
        $chat = Chat::findOne($id);

        if (!isset($chat) || !$chat->isGroup()) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $chatMember = $chat->getChatMemberByUserId();

        if (!$chat->hasUsername() && !$chatMember) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $this->getState()->setName(null);

        $query = $chat->getPremiumChatMembers();

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
            return self::createRoute('premium-members', [
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
                $memberUser = $member->user;
                $contact = $memberUser->globalUser->contact;

                $buttons[][] = [
                    'callback_data' => MemberController::createRoute('id', [
                        'id' => $member->id,
                    ]),
                    'text' => ($contact ? $contact->getTelegramDisplayName() : $memberUser->getDisplayName()),
                ];
            }

            if ($paginationButtons) {
                $buttons[] = $paginationButtons;
            }
        }

        $buttons[] = [
            [
                'callback_data' => self::createRoute('view', [
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
                $this->render('premium-members'),
                $buttons
            )
            ->build();
    }

    /**
     * @param int $page
     * @param int $id Chat->id
     * @return array
     */
    public function actionMembersWithIntro($page = 1, $id = null)
    {
        $chat = Chat::findOne($id);

        if (!isset($chat) || !$chat->isGroup()) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $chatMember = $chat->getChatMemberByUserId();

        if (!$chat->hasUsername() && !$chatMember) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $this->getState()->setName(null);

        $query = $chat->getChatMembersWithIntro();

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
            return self::createRoute('members-with-intro', [
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
                $memberUser = $member->user;
                $contact = $memberUser->globalUser->contact;

                $buttons[][] = [
                    'callback_data' => MemberController::createRoute('id', [
                        'id' => $member->id,
                    ]),
                    'text' => ($contact ? $contact->getTelegramDisplayName() : $memberUser->getDisplayName()),
                ];
            }

            if ($paginationButtons) {
                $buttons[] = $paginationButtons;
            }
        }

        $buttons[] = [
            [
                'callback_data' => self::createRoute('view', [
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
                $this->render('members-with-intro'),
                $buttons
            )
            ->build();
    }

    /**
     * @param int $page
     * @param int $id Chat->id
     * @return array
     */
    public function actionMembersWithReviews($page = 1, $id = null)
    {
        $chat = Chat::findOne($id);

        if (!isset($chat) || !$chat->isGroup()) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $chatMember = $chat->getChatMemberByUserId();

        if (!$chat->hasUsername() && !$chatMember) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $this->getState()->setName(null);

        $query = $chat->getChatMembersWithPositiveReviews();

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
            return self::createRoute('members-with-reviews', [
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
                $memberUser = $member->user;
                $contact = $memberUser->globalUser->contact;

                $buttons[][] = [
                    'callback_data' => MemberController::createRoute('id', [
                        'id' => $member->id,
                    ]),
                    'text' => ($member->getPositiveReviewsCount() ? ' ' . Emoji::LIKE . ' ' . $member->getPositiveReviewsCount() . ' - ' : '') . ($contact ? $contact->getTelegramDisplayName() : $memberUser->getDisplayName()),
                ];
            }

            if ($paginationButtons) {
                $buttons[] = $paginationButtons;
            }
        }

        $buttons[] = [
            [
                'callback_data' => self::createRoute('view', [
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
                $this->render('members-with-reviews'),
                $buttons
            )
            ->build();
    }
}
