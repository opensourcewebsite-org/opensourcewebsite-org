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
        $chat = Chat::find()->where([
            'chat_id' => $this->getTelegramChat()->chat_id,
        ])->one();

        if (!isset($chat)) {
            return;
        }

        $telegramAdministrators = $this->getBotApi()->getChatAdministrators($chat->chat_id);

        $administratorUserIds = [];
        foreach ($telegramAdministrators as $telegramAdministrator) {
            $userId = $telegramAdministrator->getUser()->getId();
            $administratorUserIds[] = $userId;

            if (!ChatMember::find()->where(['chat_id' => $chat->id, 'telegram_user_id' => $userId])->exists()) {
                $chatMember = new ChatMember();

                $chatMember->setAttributes([
                    'chat_id' => $chat->id,
                    'telegram_user_id' => $userId,
                    'status' => $telegramAdministrator->getStatus(),
                ]);

                $chatMember->save();
            }
        }

        $currentAdministrators = ChatMember::find()->where(['and', ['chat_id' => $chat->id], ['or', ['status' => ChatMember::STATUS_CREATOR], ['status' => ChatMember::STATUS_ADMINISTRATOR]]])->all();

        foreach ($currentAdministrators as $currentAdministrator) {
            if (!in_array($currentAdministrator->telegram_user_id, $administratorUserIds)) {
                $currentAdministrator->delete();
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
