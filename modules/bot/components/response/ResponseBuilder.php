<?php

namespace app\modules\bot\components\response;

use app\modules\bot\components\helpers\MessageText;
use app\modules\bot\components\response\commands\AnswerCallbackQueryCommand;
use app\modules\bot\components\response\commands\DeleteMessageCommand;
use app\modules\bot\components\response\commands\EditMessageReplyMarkupCommand;
use app\modules\bot\components\response\commands\EditMessageTextCommand;
use app\modules\bot\components\response\commands\SendLocationCommand;
use app\modules\bot\components\response\commands\SendMessageCommand;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use TelegramBot\Api\Types\Update;

/**
 * Class ResponseBuilder
 * @package app\modules\bot\components\response
 */
class ResponseBuilder
{
    /**
     * @var Update
     */
    private $update;

    /**
     * @var array
     */
    private $commands = [];

    /**
     * ResponseBuilder constructor.
     * @param $update
     */
    public function __construct($update)
    {
        $this->update = $update;
    }

    /**
     * @param MessageText $messageText
     * @param array $replyMarkup
     * @param bool $disablePreview
     * @return $this
     */
    public function editMessageTextOrSendMessage(
        MessageText $messageText,
        array $replyMarkup = [],
        bool $disablePreview = false
    ) {
        $commands = [];

        if ($callbackQuery = $this->update->getCallbackQuery()) {
            $this->answerCallbackQuery();
            $commands[] = new EditMessageTextCommand(
                $callbackQuery->getMessage()->getChat()->getId(),
                $callbackQuery->getMessage()->getMessageId(),
                $messageText,
                [
                    'replyMarkup' => !empty($replyMarkup) ? new InlineKeyboardMarkup($replyMarkup) : null,
                ]
            );
        } elseif ($message = $this->update->getMessage()) {
            $commands[] = new SendMessageCommand(
                $message->getChat()->getId(),
                $messageText,
                [
                    'replyMarkup' => !empty($replyMarkup) ? new InlineKeyboardMarkup($replyMarkup) : null,
                    'disablePreview' => $disablePreview,
                ]
            );
        }
        if (!empty($commands)) {
            $this->commands = array_merge($this->commands, $commands);
        }
        return $this;
    }

    /**
     * @param array|null $replyMarkup
     * @return $this
     */
    public function editMessageReplyMarkup(
        array $replyMarkup = null
    ) {
        if ($callbackQuery = $this->update->getCallbackQuery()) {
            $this->answerCallbackQuery();
            $this->commands[] = new EditMessageReplyMarkupCommand(
                $callbackQuery->getMessage()->getChat()->getId(),
                $callbackQuery->getMessage()->getMessageId(),
                !empty($replyMarkup) ? new InlineKeyboardMarkup($replyMarkup) : null
            );
        }
        return $this;
    }

    /**
     * @return $this
     */
    public function removeInlineKeyboardMarkup()
    {
        if ($callbackQuery = $this->update->getCallbackQuery()) {
            $this->answerCallbackQuery();
            $this->commands[] = new EditMessageReplyMarkupCommand(
                $callbackQuery->getMessage()->getChat()->getId(),
                $callbackQuery->getMessage()->getMessageId(),
                null
            );
        }
        return $this;
    }

    /**
     * @param MessageText|null $messageText
     * @param bool $showAlert
     * @return $this
     */
    public function answerCallbackQuery(MessageText $messageText = null, bool $showAlert = false)
    {
        if ($callbackQuery = $this->update->getCallbackQuery()) {
            $this->commands[] = new AnswerCallbackQueryCommand(
                $callbackQuery->getId(),
                $messageText,
                $showAlert
            );
        }
        return $this;
    }

    /**
     * @param MessageText $messageText
     * @param array|null $replyMarkup
     * @param bool $disablePreview
     * @return $this
     */
    public function sendMessage(MessageText $messageText, array $replyMarkup = null, bool $disablePreview = false)
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
                $messageText,
                [
                    'replyMarkup' => !empty($replyMarkup) ? new InlineKeyboardMarkup($replyMarkup) : null,
                    'disablePreview' => $disablePreview,
                ]
            );
        }
        return $this;
    }

    /**
     * @param MessageText $messageText
     * @return $this
     */
    public function deleteMessage(MessageText $messageText)
    {
        $chat = $messageTex->getChat();
        $chatId = $chat->getId();

        if (!is_null($chatId)) {
            $this->commands[] = new DeleteMessageCommand(
                $messageText
            );
        }
        return $this;
    }

    /**
     * @param int $longitude
     * @param int $latitude
     * @return $this
     */
    public function sendLocation(int $longitude, int $latitude)
    {
        $chatId = null;
        if ($message = $this->update->getMessage()) {
            $chatId = $message->getChat()->getId();
        } elseif ($callbackQuery = $this->update->getCallbackQuery()) {
            $chatId = $callbackQuery->getMessage()->getChat()->getId();
        }
        if (!is_null($chatId)) {
            $this->commands[] = new SendLocationCommand(
                $chatId,
                $longitude,
                $latitude
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

    /**
     * @param Update $update
     * @return ResponseBuilder
     */
    public static function fromUpdate(Update $update)
    {
        return new ResponseBuilder($update);
    }
}
