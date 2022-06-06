<?php

namespace app\modules\bot\controllers\privates;

use app\modules\bot\components\Controller;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\helpers\ExternalLink;
use app\modules\bot\components\helpers\PaginationButtons;
use app\modules\bot\models\Chat;
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
    public function actionView($chatId = null)
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
                $this->render('view', [
                    'chat' => $chat,
                    'chatMember' => $chat->getChatMemberByUserId(),
                ]),
                [
                    [
                        [
                            'callback_data' => GroupGuestFaqController::createRoute('word-list', [
                                'chatId' => $chat->id,
                            ]),
                            'text' => Yii::t('bot', 'FAQ'),
                            'visible' => ($chat->faq_status == ChatSetting::STATUS_ON),
                        ],
                    ],
                    [
                        [
                            'callback_data' => MenuController::createRoute(),
                            'text' => Emoji::MENU,
                        ],
                        [
                            'url' => ExternalLink::getTelegramAccountLink($chat->getUsername()),
                            'text' => Yii::t('bot', 'Group'),
                            'visible' => (bool)$chat->getUsername(),
                        ],
                    ]
                ],
                [
                    'disablePreview' => true,
                ]
            )
            ->build();
    }
}
