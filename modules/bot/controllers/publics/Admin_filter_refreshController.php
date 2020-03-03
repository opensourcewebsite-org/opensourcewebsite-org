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
class Admin_filter_refreshController extends Controller
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

        $administrators = $this->getBotApi()->getChatAdministrators($chat->chat_id);

        $adminUserIds = [];
        foreach ($administrators as $administrator) {
            $userId = $administrator->getUser()->getId();
            $adminUserIds[] = $userId;

            if (!ChatMember::find()->where(['chat_id' => $chat->id, 'telegram_user_id' => $userId])->exists()) {
                $chatMember = new ChatMember();

                $chatMember->setAttributes([
                    'chat_id' => $chat->id,
                    'telegram_user_id' => $userId,
                    'status' => $administrator->getStatus(),
                ]);

                $chatMember->save();
            }
        }

        $curAdmins = ChatMember::find()->where(['and', ['chat_id' => $chat->id], ['or', ['status' => ChatMember::STATUS_CREATOR], ['status' => ChatMember::STATUS_ADMINISTRATOR]]])->all();

        foreach ($curAdmins as $curAdmin) {
            if (!in_array($curAdmin->telegram_user_id, $adminUserIds)) {
                $curAdmin->delete();
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
