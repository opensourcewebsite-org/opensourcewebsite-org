<?php

namespace app\modules\bot\controllers\privates;

use Yii;
use app\modules\bot\components\Controller;
use app\modules\bot\components\helpers\PaginationButtons;
use app\modules\bot\components\actions\privates\wordlist\WordlistComponent;
use app\modules\bot\models\Chat;
use app\modules\bot\models\ChatSetting;
use app\modules\bot\models\ChatFaqQuestion;
use yii\data\Pagination;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\models\ChatMember;

/**
 * Class GroupFaqController
 *
 * @package app\modules\bot\controllers\privates
 */
class GroupFaqController extends Controller
{
    public function actions()
    {
        return array_merge(
            parent::actions(),
            Yii::createObject([
                'class' => WordlistComponent::class,
                'wordModelClass' => ChatFaqQuestion::class,
                'buttons' => [
                    [
                        'field' => 'answer',
                        'text' => Yii::t('bot', 'Answer'),
                    ],
                ]
            ])->actions()
        );
    }

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
                            'text' => $chat->faq_status == ChatSetting::STATUS_ON ? Emoji::STATUS_ON . ' ON' : Emoji::STATUS_OFF . ' OFF',
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('word-list', [
                                'chatId' => $chatId,
                            ]),
                            'text' => Yii::t('bot', 'Questions'),
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
                ],
                [
                    'disablePreview' => true,
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

        switch ($chat->faq_status) {
            case ChatSetting::STATUS_ON:
                $chat->faq_status = ChatSetting::STATUS_OFF;

                break;
            case ChatSetting::STATUS_OFF:
                $chatMember = $chat->getChatMemberByUserId();

                 if (!$chatMember->trySetChatSetting('faq_status', ChatSetting::STATUS_ON)) {
                     return $this->getResponseBuilder()
                         ->answerCallbackQuery(
                             $this->render('alert-status-on', [
                                 'requiredRating' => $chatMember->getRequiredRatingForChatSetting('faq_status', ChatSetting::STATUS_ON),
                             ]),
                             true
                         )
                         ->build();
                 }

                break;
        }

        return $this->actionIndex($chatId);
    }
}
