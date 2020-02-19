<?php

namespace app\modules\bot\components\response;

class ResponseBuilder
{
    /**
     * @var TelegramBot\Api\Types\Update
     */
    private $update;

    /**
     * @var array
     */
    private $commands = [];

    public function __construct($update)
    {
        $this->update = $update;
    }

    public function editMessageTextOrSendMessage(
        string $text,
        string $parseMode,
        $replyMarkupForEdit,
        $replyMarkupForSend
    )
    {
        $commands = [];
        if ($callbackQuery = $this->update->getCallbackQuery()) {
            $commands[] = new EditMessageTextCommand(
                $callbackQuery->getMessage()->getChat()->getId(),
                $callbackQuery->getMessage()->getMessageId(),
                $text,
                [
                    'parseMode' => $parseMode,
                    'replyMarkup' => $replyMarkupForEdit,
                ]
            );
        } elseif ($message = $this->update->getMessage()) {
            $commands[] = new SendMessageCommand(
                $message->getChat()->getId(),
                $text,
                [
                    'parseMode' => $parseMode,
                    'replyMarkup' => $replyMarkupForSend,
                ]
            );
        }
        if (!empty($commands)) {
            $this->commands = array_merge($this->commands, $commands);
        }
        return $this;
    }

    public function removeInlineKeyboardMarkup(InlineKeyboardMarkup $inlineKeyboardMarkup = null)
    {
        if ($callbackQuery = $this->update->getCallbackQuery()) {
            $this->commands[] = new EditMessageReplyMarkupCommand(
                $callbackQuery->getMessage()->getChat()->getId(),
                $callbackQuery->getMessage()->getMessageId(),
                $inlineKeyboardMarkup
            );
        }
        return $this;
    }

    public function answerCallbackQuery()
    {
        if ($callbackQuery = $this->update->getCallbackQuery()) {
            $this->commands[] = new AnswerCallbackQueryCommand(
                $callbackQuery->getId()
            );
        }
        return $this;
    }

    public function sendMessage(string $text, string $parseMode, $replyMarkup)
    {
        $chatId = null;
        if ($message = $this->update->getMessage()) {
            $chatId = $message->getChat()->getId();
        } elseif ($callbackQuery = $this->update->getCallbackQuery()) {
            $chatId = $callbackQuery->getMessage()->getChat()->getId();
        }
        if (!is_null($chatId)) {
            $this->commands[] = new SendMessageCommand(
                $chatId,
                $text,
                [
                    'parseMode' => $parseMode,
                    'replyMarkup' => $replyMarkup,
                ]
            );
        }
        return $this;
    }

    /**
     * @return array
     */
    public function build()
    {
        return $this->commands;
    }
}
