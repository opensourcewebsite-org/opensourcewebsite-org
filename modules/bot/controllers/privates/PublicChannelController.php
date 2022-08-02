<?php

// TODO

namespace app\modules\bot\controllers\privates;

use app\modules\bot\components\Controller;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\helpers\ExternalLink;
use app\modules\bot\components\helpers\PaginationButtons;
use app\modules\bot\models\Chat;
use app\modules\bot\models\ChatMember;
use app\modules\bot\models\ChatSetting;
use app\modules\bot\models\User;
use Yii;
use yii\data\Pagination;
use yii\helpers\ArrayHelper;

/**
 * Class PublicChannelController
 *
 * @package app\modules\bot\controllers\privates
 */
class PublicChannelController extends Controller
{
    /**
     * @return array
     */
    public function actionIndex($page = 1)
    {
        $this->getState()->setName(null);

        $query = Chat::find()
            ->channel()
            ->hasUsername()
            ->orderByCreatorRank();

        $pagination = new Pagination([
            'totalCount' => $query->count(),
            'pageSize' => 1,
            'params' => [
                'page' => $page,
            ],
            'pageSizeParam' => false,
            'validatePage' => true,
        ]);

        $chat = $query->offset($pagination->offset)
            ->limit($pagination->limit)
            ->one();

        $paginationButtons = PaginationButtons::build($pagination, function ($page) {
            return self::createRoute('index', [
                'page' => $page,
            ]);
        });

        $buttons = [];

        if ($chat) {
            if ($paginationButtons) {
                $buttons[] = $paginationButtons;
            }
        } else {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        // $buttons[] = [
        //     [
        //         'callback_data' => ChannelGuestController::createRoute('view', [
        //             'id' => $chat->id,
        //         ]),
        //         'text' => Yii::t('bot', 'Details'),
        //     ],
        // ];

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
                'callback_data' => PublicChannelRefreshController::createRoute('index', [
                    'chatId' => $chat->id,
                    'page' => $page,
                ]),
                'text' => Emoji::REFRESH,
            ],
            [
                'url' => ExternalLink::getTelegramAccountLink($chat->getUsername()),
                'text' => Yii::t('bot', 'Channel'),
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
