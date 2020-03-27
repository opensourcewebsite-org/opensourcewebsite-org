<?php

namespace app\modules\bot\components\response;

use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\helpers\MessageText;
use app\modules\bot\components\response\commands\AnswerCallbackQueryCommand;
use app\modules\bot\components\response\commands\EditMessageReplyMarkupCommand;
use app\modules\bot\components\response\commands\EditMessageTextCommand;
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
     * @param array|null $replyMarkup
     * @param string|null $backButtonData
     * @param bool $useLastRow
     * @return $this
     */
    public function editMessageTextOrSendMessage(
        MessageText $messageText,
        array $replyMarkup = null,
        string $backButtonData = null,
        bool $useLastRow = false
    )
    {
        $commands = [];

        if (!is_null($backButtonData)) {
            $backButton = [
                'text' => Emoji::BACK,
                'callback_data' => $backButtonData,
            ];
            if ($useLastRow) {
                $lastRow = end($replyMarkup);
                array_unshift($lastRow, $backButton);
                $replyMarkup[count($replyMarkup) - 1] = $lastRow;
            } else {
                $replyMarkup[] = [ $backButton ];
            }
        }

        if ($callbackQuery = $this->update->getCallbackQuery()) {

            $commands[] = new EditMessageTextCommand(
                $callbackQuery->getMessage()->getChat()->getId(),
                $callbackQuery->getMessage()->getMessageId(),
                $messageText,
                [
                    'replyMarkup' => !empty($replyMarkup) ? new InlineKeyboardMarkup($replyMarkup) : null
                ]
            );
        } elseif ($message = $this->update->getMessage()) {
            $commands[] = new SendMessageCommand(
                $message->getChat()->getId(),
                $messageText,
                [
                    'replyMarkup' => !empty($replyMarkup) ? new InlineKeyboardMarkup($replyMarkup) : null
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
    )
    {
        $commands = [];
        if ($callbackQuery = $this->update->getCallbackQuery()) {
            $commands[] = new EditMessageReplyMarkupCommand(
                $callbackQuery->getMessage()->getChat()->getId(),
                $callbackQuery->getMessage()->getMessageId(),
                !empty($replyMarkup) ? new InlineKeyboardMarkup($replyMarkup) : null
            );
        } elseif ($message = $this->update->getMessage()) {
            $commands[] = new EditMessageReplyMarkupCommand(
                $message->getChat()->getId(),
                $message->getMessageId(),
                !empty($replyMarkup) ? new InlineKeyboardMarkup($replyMarkup) : null
            );
        }
        if (!empty($commands)) {
            $this->commands = array_merge($this->commands, $commands);
        }
        return $this;
    }

    /**
     * @return $this
     */
    public function removeInlineKeyboardMarkup()
    {
        if ($callbackQuery = $this->update->getCallbackQuery()) {
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
     * @return $this
     */
    public function sendMessage(MessageText $messageText, array $replyMarkup = null)
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

    /**
     * @param Update $update
     * @return ResponseBuilder
     */
    public static function fromUpdate(Update $update)
    {
        return new ResponseBuilder($update);
    }
}
