<?php

namespace app\modules\bot\controllers\groups;

use app\modules\bot\components\Controller;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\helpers\ExternalLink;
use app\modules\bot\components\helpers\PaginationButtons;
use app\modules\bot\models\ChatSetting;
use Yii;
use yii\data\Pagination;

/**
 * Class PremiumMembersController
 *
 * @package app\modules\bot\controllers\groups
 */
class PremiumMembersController extends Controller
{
    /**
    * @param int $page
    * @return array
     */
    public function actionIndex($page = 1)
    {
        if ($this->getUpdate() && !$this->getUpdate()->getCallbackQuery()) {
            $this->getResponseBuilder()
                ->deleteMessage()
                ->send();
        }

        $chat = $this->getTelegramChat();

        if ($chat->isMembershipOn()) {
            if ($chat->isLimiterOn()) {
                $query = $chat->getPremiumChatMembersWithLimiter();
            } else {
                $query = $chat->getPremiumChatMembers();
            }

            $pagination = new Pagination([
                'totalCount' => $query->count(),
                'pageSize' => 20,
                'params' => [
                    'page' => $page,
                ],
                'pageSizeParam' => false,
                'validatePage' => true,
            ]);

            $paginationButtons = PaginationButtons::build($pagination, function ($page) use ($chat) {
                return self::createRoute('index', [
                    'id' => $chat->id,
                    'page' => $page,
                ]);
            });

            $buttons = [];

            $members = $query->offset($pagination->offset)
                ->limit($pagination->limit)
                ->all();

            if ($members) {
                if ($paginationButtons) {
                    $buttons[] = $paginationButtons;
                }
            }

            return $this->getResponseBuilder()
                ->editMessageTextOrSendMessage(
                    $this->render('index', [
                        'chat' => $chat,
                        'members' => $members,
                    ]),
                    $buttons,
                    [
                        'disablePreview' => true,
                    ]
                )
                ->send();
        }

        return [];
    }
}
