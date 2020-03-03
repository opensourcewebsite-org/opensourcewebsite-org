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
use app\modules\bot\models\Setting;

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

        $statusSetting = Setting::find()->where(['chat_id' => $chat->id, 'setting' => Setting::FILTER_STATUS])->one();
        $modeSetting = Setting::find()->where(['chat_id' => $chat->id, 'setting' => Setting::FILTER_MODE])->one();

        if (!isset($statusSetting) || $statusSetting->value == Setting::FILTER_STATUS_OFF) {
            return;
        }

        $deleteMessage = null;
        if ($modeSetting->value = Setting::FILTER_MODE_BLACK) {
            $deleteMessage = false;

            $phrases = Phrase::find()->where(['group_id' => $chat->id, 'type' => Setting::FILTER_MODE_BLACK])->all();
            foreach ($phrases as $phrase) {
                if (mb_stripos($update->getMessage()->getText(), $phrase->text) !== false) {
                    $deleteMessage = true;
                    break;
                }
            }
        } else {
            $deleteMessage = true;

            $phrases = Phrase::find()->where(['group_id' => $chat->id, 'type' => Setting::FILTER_MODE_WHITE])->all();
            foreach ($phrases as $phrase) {
                if (mb_stripos($update->getMessage()->getText(), $phrase->text) !== false) {
                    $deleteMessage = false;
                    break;
                }
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
