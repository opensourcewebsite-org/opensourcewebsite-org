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

        $chatMember = $chat->getChatMemberByUserId();

        $buttons = [];

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
        }

        $buttons[] = [
            [
                'callback_data' => GroupGuestFaqController::createRoute('word-list', [
                    'chatId' => $chat->id,
                ]),
                'text' => Yii::t('bot', 'FAQ'),
                'visible' => ($chat->faq_status == ChatSetting::STATUS_ON),
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
}
