<?php

// TODO

namespace app\modules\bot\controllers\privates;

use Yii;
use app\modules\bot\components\Controller;
use app\modules\bot\components\helpers\Emoji;
use yii\data\Pagination;
use app\modules\bot\components\helpers\PaginationButtons;
use app\modules\bot\models\Chat;
use app\modules\bot\models\ChatMember;
use app\modules\bot\models\ChatSetting;
use app\modules\bot\models\User;
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
        // TODO order by rating
        // '{{%user}}.rating' => SORT_DESC,
        // '{{%user}}.created_at' => SORT_ASC,
        $chatQuery = Chat::find()
            ->where([
                'type' => Chat::TYPE_CHANNEL,
            ])
            ->andWhere([
                'not', ['username' => null],
            ])
            ->orderBy(['id' => SORT_ASC]);

        $pagination = new Pagination([
            'totalCount' => $chatQuery->count(),
            'pageSize' => 1,
            'params' => [
                'page' => $page,
            ],
            'pageSizeParam' => false,
            'validatePage' => true,
        ]);

        $chats = $chatQuery->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();

        $paginationButtons = PaginationButtons::build($pagination, function ($page) {
            return self::createRoute('index', [
                'page' => $page,
            ]);
        });

        $buttons = [];

        if ($chats) {
            if ($paginationButtons) {
                $buttons[] = $paginationButtons;
            }
        } else {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $chat = $chats[0];

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
