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

/**
 * Class MessageController
 *
 * @package app\controllers\bot
 */
class MessageController extends Controller
{
    /**
     * @return array
     */
    public function actionIndex($groupId = null)
    {
        $telegramUser = $this->getTelegramUser();
        $update = $this->getUpdate();

        $groupId = $update->getMessage()->getChat()->getId();

        if (!Chat::find()->where(['chat_id' => $groupId])->exists()) {
            return;
        }

        $chat = Chat::find()->where(['chat_id' => $groupId])->one();

        $statusSetting = ChatSetting::find()->where(['chat_id' => $chat->id, 'setting' => ChatSetting::FILTER_STATUS])->one();
        $modeSetting = ChatSetting::find()->where(['chat_id' => $chat->id, 'setting' => ChatSetting::FILTER_MODE])->one();

        if (!isset($statusSetting) || !isset($modeSetting) || $statusSetting->value == ChatSetting::FILTER_STATUS_OFF) {
            return;
        }

        $deleteMessage = false;

        if ($update->getMessage()->getText() !== null) {
            if ($modeSetting->value == ChatSetting::FILTER_MODE_BLACKLIST) {
                $deleteMessage = false;

                $phrases = Phrase::find()->where(['group_id' => $chat->id, 'type' => ChatSetting::FILTER_MODE_BLACKLIST])->all();
                
                foreach ($phrases as $phrase) {
                    if (mb_stripos($update->getMessage()->getText(), $phrase->text) !== false) {
                        $deleteMessage = true;
                        break;
                    }
                }
            } else {
                $deleteMessage = true;

                $phrases = Phrase::find()->where(['group_id' => $chat->id, 'type' => ChatSetting::FILTER_MODE_WHITELIST])->all();
                foreach ($phrases as $phrase) {
                    if (mb_stripos($update->getMessage()->getText(), $phrase->text) !== false) {
                        $deleteMessage = false;
                        break;
                    }
                }
            }
        }

        $joinHiderStatus = ChatSetting::find()->where(['chat_id' => $chat->id, 'setting' => ChatSetting::JOIN_HIDER_STATUS])->one();

        if (isset($joinHiderStatus) && $joinHiderStatus->value == ChatSetting::JOIN_HIDER_STATUS_ON) {
            if ($update->getMessage()->getNewChatMember() !== null || $update->getMessage()->getLeftChatMember() !== null) {
                $deleteMessage = true;
            }
        }

        if ($deleteMessage) {
            return [
                new DeleteMessageCommand(
                    $update->getMessage()->getChat()->getId(),
                    $update->getMessage()->getMessageId(),
                ),
            ];
        }
    }
}
