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
 * Class ChannelGuestController
 *
 * @package app\modules\bot\controllers\privates
 */
class ChannelGuestController extends Controller
{
    /**
     * @param int $id Chat->id
     * @return array
     */
    public function actionView($id = null)
    {
        $chat = Chat::findOne($id);

        if (!isset($chat) || !$chat->isChannel()) {
            return [];
        }

        $this->getState()->setName(null);

        $buttons = [];

        $chatMember = $chat->getChatMemberByUserId();

        if ($chatMember) {
            if ($chatMember->canUseMarketplace()) {
                $buttons[] = [
                    [
                        'callback_data' => ChannelGuestMarketplaceController::createRoute('index', [
                            'id' => $chat->id,
                        ]),
                        'text' => Yii::t('bot', 'Your posts'),
                    ],
                ];
            }
        }

        $buttons[] = [
            [
                'callback_data' => MenuController::createRoute(),
                'text' => Emoji::MENU,
            ],
            [
                'url' => ExternalLink::getTelegramAccountLink($chat->getUsername()),
                'text' => Yii::t('bot', 'Channel'),
                'visible' => (bool)$chat->getUsername(),
            ],
        ];

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('view', [
                    'chat' => $chat,
                ]),
                $buttons,
                [
                    'disablePreview' => true,
                ]
            )
            ->build();
    }
}
