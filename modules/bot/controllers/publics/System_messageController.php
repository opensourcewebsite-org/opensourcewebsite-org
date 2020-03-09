<?php

namespace app\modules\bot\controllers\publics;

use Yii;
use \app\modules\bot\components\response\SendMessageCommand;
use \app\modules\bot\components\response\EditMessageTextCommand;
use \app\modules\bot\components\response\DeleteMessageCommand;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use app\modules\bot\components\Controller as Controller;
use app\modules\bot\models\Chat;
use app\modules\bot\models\Phrase;
use app\modules\bot\models\ChatSetting;
use app\modules\bot\models\ChatMember;

/**
 * Class MessageController
 *
 * @package app\controllers\bot
 */
class System_messageController extends Controller
{
    /**
     * @return array
     */
    public function actionIndex()
    {
        $telegramUser = $this->getTelegramUser();
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

    public function actionGroup_to_supergroup()
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
