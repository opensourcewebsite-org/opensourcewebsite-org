<?php

namespace app\modules\bot\controllers\publics;

use \app\modules\bot\components\response\DeleteMessageCommand;
use app\modules\bot\components\Controller as Controller;
use app\modules\bot\models\Chat;
use app\modules\bot\models\ChatSetting;

/**
 * Class SystemMessageController
 *
 * @package app\controllers\bot
 */
class SystemMessageController extends Controller
{
    /**
     * @return array
     */
    public function actionIndex()
    {
        $update = $this->getUpdate();

        $chat = $this->getTelegramChat();

        $deleteMessage = false;
        $joinHiderStatus = $chat->getSetting(ChatSetting::JOIN_HIDER_STATUS);

        if (isset($joinHiderStatus) && $joinHiderStatus->value == ChatSetting::JOIN_HIDER_STATUS_ON) {
            $deleteMessage = true;
        }

        if ($deleteMessage) {
            return [
                new DeleteMessageCommand(
                    $update->getMessage()->getChat()->getId(),
                    $update->getMessage()->getMessageId()
                ),
            ];
        }
    }

    public function actionGroupToSupergroup()
    {
        $chat = $this->getTelegramChat();

        $chat->setAttributes([
            'type' => Chat::TYPE_SUPERGROUP,
            'chat_id' => $this->getUpdate()->getMessage()->getMigrateToChatId(),
        ]);

        $chat->save();

        return [];
    }
}
