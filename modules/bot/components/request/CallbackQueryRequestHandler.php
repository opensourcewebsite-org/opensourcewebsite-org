<?php
namespace app\modules\bot\components\request;

use TelegramBot\Api\Types\Update;

class CallbackQueryRequestHandler implements IRequestHandler
{
    public function getFrom(Update $update)
    {
        $from = null;

        if ($callbackQuery = $update->getCallbackQuery()) {
            $from = $callbackQuery->getFrom();
        }

        return $from ?? null;
    }

    public function getChat(Update $update)
    {
        $chat = null;

    	if ($callbackQuery = $update->getCallbackQuery()) {
            $chat = $callbackQuery->getMessage()->getChat();
        }

        return $chat;
    }

    public function getCommandText(Update $update)
    {
        $commandText = null;

        if ($callbackQuery = $update->getCallbackQuery()) {
            $commandText = $callbackQuery->getData();
        }

        return $commandText ?? null;
    }
}
