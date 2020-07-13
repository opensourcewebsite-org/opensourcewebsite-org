<?php
namespace app\modules\bot\components\request;

use app\modules\bot\controllers\publics\JoinCaptchaController;
use app\modules\bot\controllers\publics\SystemMessageController;
use TelegramBot\Api\Types\Update;
use app\modules\bot\models\ChatMember;

class SystemMessageCommandResolver implements ICommandResolver
{
    public function resolveCommand(Update $update)
    {

        if ($update->getMessage() && $update->getMessage()->getNewChatMember()) {

            $chatId = $update->getMessage()->getChat()->getId();
            $telegramUser = $update->getMessage()->getFrom();
            $isAdmin = false;
            if (isset($chatId) && isset($telegramUser)){

                $chatMember = ChatMember::find()
                            ->select('bot_chat_member.id')
                            ->leftJoin('bot_chat','bot_chat_member.chat_id = bot_chat.id')
                            ->leftJoin('bot_user','bot_chat_member.user_id = bot_user.id')
                            ->where(['bot_chat.chat_id' => $chatId, 'bot_user.provider_user_id' => $telegramUser->getId()])->one();

                if(isset($chatMember)){
                    $isAdmin = $chatMember->isAdmin();
                }
            }

            if(!$isAdmin) {
                $commandText = JoinCaptchaController::createRoute('show-captcha');
            }

        }

        if ($update->getMessage() && $update->getMessage()->getLeftChatMember()) {
            $commandText = SystemMessageController::createRoute();
        }

        if ($update->getMessage() && $update->getMessage()->getMigrateToChatId()) {
            $commandText = SystemMessageController::createRoute('group-to-supergroup');
        }

        return $commandText ?? null;
    }
}
