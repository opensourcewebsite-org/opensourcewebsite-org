<?php
namespace app\modules\bot\components\request;

use TelegramBot\Api\Types\Update;

class CallbackQueryUpdateHandler implements IUpdateHandler
{
    public function getFrom(Update $update)
    {
        if ($callbackQuery = $update->getCallbackQuery()) {
            $from = $callbackQuery->getFrom();
        }

        return $from ?? null;
    }

    public function getChat(Update $update)
    {
    	if ($callbackQuery = $update->getCallbackQuery()) {
            $chat = $callbackQuery->getMessage()->getChat();
        }

        return $chat ?? null;
    }
}
