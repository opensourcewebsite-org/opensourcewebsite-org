<?php

namespace app\modules\bot\controllers\publics;

use Yii;
use \app\modules\bot\components\response\SendMessageCommand;
use \app\modules\bot\components\response\EditMessageTextCommand;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use app\modules\bot\components\Controller as Controller;
use app\modules\bot\models\Chat;
use app\modules\bot\models\Phrase;
use app\modules\bot\models\ChatMember;
use app\modules\bot\models\User;

/**
 * Class FilterChatController
 *
 * @package app\controllers\bot
 */
class RefreshController extends Controller
{
    /**
     * @return array
     */
    public function actionIndex()
    {
        $chat = $this->getTelegramChat();

        $telegramAdministrators = $this->getBotApi()->getChatAdministrators($chat->chat_id);

        $administratorUsers = [];

        foreach ($telegramAdministrators as $telegramAdministrator) {
            $user = User::find()->where(['provider_user_id' => $telegramAdministrator->getUser()->getId()])->one();

            if (isset($user)) {
                $administratorUsers[] = $user;
            }

            if (isset($user) && !in_array($user, $chat->getAdminUsers()->all())) {
                $user->link('chats', $chat, ['status' => $telegramAdministrator->getStatus()]);
            }
        }

        $currentAdministrators = $chat->getAdminUsers()->all();

        foreach ($currentAdministrators as $currentAdministrator) {
            if (!in_array($currentAdministrator, $administratorUsers)) {
                $chat->unlink('users', $currentAdministrator, true);
            }
        }
        
        return [
            new SendMessageCommand(
                $this->getTelegramChat()->chat_id,
                $this->render('index'),
                [
                    'parseMode' => $this->textFormat,
                    'replyToMessageId' => $this->getUpdate()->getMessage()->getMessageId(),
                ]
            ),
        ];
        
    }
}
