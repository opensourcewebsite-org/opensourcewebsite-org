<?php

namespace app\modules\bot\controllers\privates;

use app\modules\bot\components\Controller;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\helpers\MessageWithEntitiesConverter;
use app\modules\bot\models\Chat;
use app\modules\bot\models\ChatMember;
use app\modules\bot\models\ChatMemberReview;
use app\modules\bot\models\ChatTip;
use app\modules\bot\models\User;
use Yii;

/**
 * Class MemberController
 *
 * @package app\modules\bot\controllers\privates
 */
class MemberController extends Controller
{
    /**
     * @return array
     */
    public function actionIndex($userId = null, $chatId = null)
    {
        if (!isset($userId) || !isset($chatId)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $viewUser = User::findOne([
            'provider_user_id' => $userId,
            'is_bot' => 0,
        ]);

        if (!isset($viewUser)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $chat = Chat::find()
            ->where([
                'chat_id' => $chatId,
            ])
            ->group()
            ->hasUsername()
            ->one();

        if (!isset($chat)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $chatMember = ChatMember::findOne([
            'chat_id' => $chat->id,
            'user_id' => $viewUser->id,
        ]);

        if (!isset($chatMember)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        return $this->runAction('id', [
            'id' => $chatMember->id,
        ]);
    }

    /**
     * @return array
     */
    public function actionId($id = null, $chatTipId = null)
    {
        if ($id) {
            $memberId = $id;
        } elseif ($text = $this->getMessage()->getText()) {
            if (preg_match('/(?:^(?:[0-9]+))/i', $text, $matches)) {
                $memberId = $matches[0];
            }
        }

        if (!isset($memberId)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $chatMember = ChatMember::findOne([
            'id' => $memberId,
        ]);

        if (!isset($chatMember)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $viewUser = $chatMember->user;
        $user = $this->getTelegramUser();
        $chat = $chatMember->chat;

        if ($user->id == $viewUser->id) {
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

        $this->getState()->setName(json_encode($chatTipId));

        $chatMemberReview = ChatMemberReview::findOne([
            'user_id' => $user->id,
            'member_id' => $chatMember->id,
        ]);

        $chatTip = ChatTip::findOne($chatTipId);

        if ($chatTip) {
            $state = $this->getState();
            $state->setIntermediateModel($chatTip);
        }

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('index', [
                    'user' => $viewUser,
                    'contact' => $viewUser->globalUser->contact ?: $viewUser->globalUser->newContact,
                    'chat' => $chatMember->chat,
                    'chatMember' => $chatMember,
                    'review' => $chatMemberReview,
                ]),
                [
                    [
                        [
                            'callback_data' => WalletController::createRoute('index', [
                                'useState' => true,
                            ]),
                            'text' => Yii::t('bot', 'Send a Tip'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => MemberReviewController::createRoute('index', [
                                'id' => $chatMember->id,
                            ]),
                            'text' => Yii::t('bot', 'Reviews') . ($chatMember->getPositiveReviewsCount() ? ' ' . Emoji::LIKE . ' ' . $chatMember->getPositiveReviewsCount() : '') . ($chatMember->getNegativeReviewsCount() ? ' ' . Emoji::DISLIKE . ' ' . $chatMember->getNegativeReviewsCount() : ''),
                            'visible' => $chatMember->getActiveReviews()->exists(),
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('my-review', [
                                'id' => $chatMember->id,
                            ]),
                            'text' => Yii::t('bot', 'Your public review'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => UserController::createRoute('id', [
                                'id' => $viewUser->provider_user_id,
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
     * @param int $id ChatMember->id
     *
     * @return array
     */
    public function actionSendTip($id = null)
    {
        $chatMember = ChatMember::findOne([
            'id' => $id,
        ]);

        if (!isset($chatMember)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $chatTipId = json_decode($this->getState()->getName());
        $chatTip = ChatTip::findOne($chatTipId);

        if (!isset($chatTip)) {
            $chatTip = new ChatTip([
                'chat_id' => $chatMember->chat_id,
                'to_user_id' => $chatMember->user_id,
            ]);

            $chatTip->save();
        }

        return $this->run('send-group-tip/index', [
            'chatTipId' => $chatTip->id,
        ]);
    }

    /**
     * @param int $id ChatMember->id
     * @return array
     */
    public function actionMyReview($id = null)
    {
        $chatMember = ChatMember::findOne([
            'id' => $id,
        ]);

        if (!isset($chatMember)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $user = $this->getTelegramUser();

        $chatMemberReview = ChatMemberReview::findOne([
            'user_id' => $user->id,
            'member_id' => $chatMember->id,
        ]);

        if (!isset($chatMemberReview)) {
            return $this->actionInputText([
                'id' => $chatMember->id,
            ]);
        }

        $this->getState()->setName(null);

        $statusButtons = [];

        foreach (ChatMemberReview::getStatusLabels() as $key => $value) {
            $statusButtons[] = [
                'callback_data' => self::createRoute('set-status', [
                    'id' => $chatMember->id,
                    'v' => $key,
                ]),
                'text' => $value,
            ];
        }

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('my-review', [
                    'review' => $chatMemberReview,
                ]),
                [
                    $statusButtons,
                    [
                        [
                            'callback_data' => self::createRoute('input-text', [
                                'id' => $chatMember->id,
                            ]),
                            'text' => Yii::t('bot', 'Text'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('id', [
                                'id' => $chatMember->id,
                            ]),
                            'text' => Emoji::BACK,
                        ],
                        [
                            'callback_data' => MenuController::createRoute(),
                            'text' => Emoji::MENU,
                        ],
                        [
                            'callback_data' => self::createRoute('delete', [
                                'id' => $chatMember->id,
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
     * @param int $id ChatMember->id
     * @param int $v ChatMemberReview->status
     */
    public function actionSetStatus($id = null, $v = null)
    {
        $counterChatMember = ChatMember::findOne([
            'id' => $id,
        ]);

        if (!isset($counterChatMember)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $user = $this->getTelegramUser();

        $chatMemberReview = ChatMemberReview::findOne([
            'user_id' => $user->id,
            'member_id' => $counterChatMember->id,
        ]);

        if (!isset($chatMemberReview)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        if ($chatMemberReview->status == $v) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $chatMemberReview->status = $v;

        if ($chatMemberReview->validate('status')) {
            if ($chatMemberReview->isAttributeChanged('status', false)) {
                $chatMemberReview->save(false);
                // if the review has received an active status, then notify the counter user
                if ($chatMemberReview->isActive()) {
                    $buttons = [];

                    $viewUser = $chatMemberReview->counterUser;
                    $viewUser->useLanguage();
                    // when the author of the review is a member of the group
                    if ($chatMember = $chatMemberReview->chatMember) {
                        $buttons[] = [
                            [
                                'callback_data' => self::createRoute('id', [
                                    'id' => $chatMember->id,
                                ]),
                                'text' => Yii::t('bot', 'Member View'),
                            ],
                        ];
                    }

                    $chatMemberReview->counterUser->sendMessage(
                        $this->render('notify-review', [
                            'authorUser' => $user,
                            'chat' => $chatMemberReview->chat,
                            'review' => $chatMemberReview,
                        ]),
                        $buttons
                    );

                    $user->useLanguage();
                }
            }

            return $this->runAction('my-review', [
                'id' => $counterChatMember->id,
            ]);
        }

        return $this->getResponseBuilder()
            ->answerCallbackQuery()
            ->build();
    }

    /**
     * @param int $id ChatMember->id
     * @return array
     */
    public function actionInputText($id = null)
    {
        $chatMember = ChatMember::findOne([
            'id' => $id,
        ]);

        if (!isset($chatMember)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $user = $this->getTelegramUser();

        $chatMemberReview = ChatMemberReview::findOne([
            'user_id' => $user->id,
            'member_id' => $chatMember->id,
        ]);

        if (!isset($chatMemberReview)) {
            $chatMemberReview = new ChatMemberReview();
            $chatMemberReview->user_id = $user->id;
            $chatMemberReview->member_id = $chatMember->id;
        }

        $this->getState()->setName(self::createRoute('input-text', [
            'id' => $chatMember->id,
        ]));

        if ($this->getUpdate()->getMessage()) {
            if ($text = MessageWithEntitiesConverter::toHtml($this->getUpdate()->getMessage())) {
                $chatMemberReview->text = $text;

                if ($chatMemberReview->validate('text')) {
                    $chatMemberReview->save(false);

                    return $this->runAction('my-review', [
                        'id' => $chatMember->id,
                    ]);
                }
            }
        }

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('input-text', [
                    'review' => $chatMemberReview,
                ]),
                [
                    [
                        [
                            'callback_data' => $chatMemberReview->isNewRecord
                                ? self::createRoute('id', [
                                    'id' => $chatMember->id,
                                ])
                                : self::createRoute('my-review', [
                                    'id' => $chatMember->id,
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
     * @param int $id ChatMember->id
     * @return array
     */
    public function actionDelete($id = null)
    {
        $chatMember = ChatMember::findOne([
            'id' => $id,
        ]);

        if (!isset($chatMember)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $user = $this->getTelegramUser();

        $chatMemberReview = ChatMemberReview::findOne([
            'user_id' => $user->id,
            'member_id' => $chatMember->id,
        ]);

        if ($chatMemberReview) {
            $chatMemberReview->delete();
        }

        return $this->runAction('id', [
            'id' => $chatMember->id,
        ]);
    }
}
