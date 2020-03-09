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
class MessageController extends Controller
{
    /**
     * @return array
     */
    public function actionIndex()
    {
        $telegramUser = $this->getTelegramUser();
        $update = $this->getUpdate();

        $chat = $this->getTelegramChat();

        $statusSetting = $chat->getSetting(ChatSetting::FILTER_STATUS);
        $modeSetting = $chat->getSetting(ChatSetting::FILTER_MODE);

        if (!isset($statusSetting) || !isset($modeSetting) || $statusSetting->value == ChatSetting::FILTER_STATUS_OFF) {
            return;
        }

        $deleteMessage = false;

        if ($update->getMessage()->getText() !== null) {
            $adminUser = $chat->getAdministrators()->where(['id' => $telegramUser->user_id])->one();

            if (!isset($adminUser)) {
                if ($modeSetting->value == ChatSetting::FILTER_MODE_BLACKLIST) {
                    $deleteMessage = false;

                    $phrases = $chat->getBlacklistPhrases();

                    foreach ($phrases as $phrase) {
                        if (mb_stripos($update->getMessage()->getText(), $phrase->text) !== false) {
                            $deleteMessage = true;
                            break;
                        }
                    }
                } else {
                    $deleteMessage = true;

                    $phrases = $chat->getWhitelistPhrases();

                    foreach ($phrases as $phrase) {
                        if (mb_stripos($update->getMessage()->getText(), $phrase->text) !== false) {
                            $deleteMessage = false;
                            break;
                        }
                    }
                }
            }
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
}
