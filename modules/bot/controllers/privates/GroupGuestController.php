<?php

namespace app\modules\bot\controllers\privates;

use Yii;
use app\modules\bot\components\Controller;
use app\modules\bot\components\helpers\PaginationButtons;
use app\modules\bot\models\Chat;
use app\modules\bot\models\ChatSetting;
use yii\data\Pagination;
use app\modules\bot\components\helpers\Emoji;

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

        if (!isset($chat) || (!$chat->isGroup())) {
            return [];
        }

        $this->getState()->setName(null);

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('view', [
                    'chat' => $chat,
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
                    ]
                ]
            )
            ->build();
    }
}
