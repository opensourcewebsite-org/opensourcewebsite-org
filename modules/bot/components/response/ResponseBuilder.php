<?php

namespace app\modules\bot\components\response;

use Yii;
use app\modules\bot\components\helpers\MessageText;
use app\modules\bot\components\helpers\Photo;
use app\modules\bot\components\response\commands\AnswerCallbackQueryCommand;
use app\modules\bot\components\response\commands\DeleteMessageCommand;
use app\modules\bot\components\response\commands\EditMessageReplyMarkupCommand;
use app\modules\bot\components\response\commands\EditMessageTextCommand;
use app\modules\bot\components\response\commands\SendLocationCommand;
use app\modules\bot\components\response\commands\SendMessageCommand;
use app\modules\bot\components\response\commands\SendPhotoCommand;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use TelegramBot\Api\Types\Update;
use yii\helpers\ArrayHelper;

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
        bool $disablePreview = false,
        array $optionalParams = []
    ) {
        $commands = [];

        if (!$this->update->getCallbackQuery() || $this->update->getCallbackQuery()->getMessage()->getPhoto() === null) {

            if ($callbackQuery = $this->update->getCallbackQuery()) {
                $this->answerCallbackQuery();
                $commands[] = new EditMessageTextCommand(
                    $callbackQuery->getMessage()->getChat()->getId(),
                    $callbackQuery->getMessage()->getMessageId(),
                    $messageText,
                    [
                        'replyMarkup' => !empty($replyMarkup) ? new InlineKeyboardMarkup($replyMarkup) : null,
                        'disablePreview' => $disablePreview,
                    ]
                );
            } elseif ($message = $this->update->getMessage() ?? $this->update->getEditedMessage()) {
                $optionalParams = ArrayHelper::merge(
                    [
                        'replyMarkup' => !empty($replyMarkup) ? new InlineKeyboardMarkup($replyMarkup) : null,
                        'disablePreview' => $disablePreview,
                    ],
                    ArrayHelper::filter($optionalParams, ['replyToMessageId','disableNotification','parseMode'])
                );

                $commands[] = new SendMessageCommand(
                    $message->getChat()->getId(),
                    $messageText,
                    $optionalParams
                );
            }

            if (!empty($commands)) {
                $this->commands = array_merge($this->commands, $commands);
            }
            return $this;
        } else {
            $commands = [];

            if ($callbackQuery = $this->update->getCallbackQuery()) {
                $this->answerCallbackQuery();
                $this->deleteMessage();

                $commands[] = new SendMessageCommand(
                    $callbackQuery->getMessage()->getChat()->getId(),
                    $messageText,
                    [
                        'replyMarkup' => !empty($replyMarkup) ? new InlineKeyboardMarkup($replyMarkup) : null,
                        'disablePreview' => $disablePreview,
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
    }

    public function sendPhotoOrSendMessage(
        ?string $photoFileId,
        MessageText $messageText,
        array $replyMarkup = [],
        bool $disablePreview = false
    )
    {
        $photo = new Photo($photoFileId);

        if ($photo->isNull()) {
            return $this->sendMessage($messageText, $replyMarkup);
        }

        if ($callbackQuery = $this->update->getCallbackQuery()) {
            $this->answerCallbackQuery();
            $commands[] = new SendPhotoCommand(
                $this->update->getCallbackQuery()->getMessage()->getChat()->getId(),
                $photo,
                $messageText,
                [
                    'replyMarkup' => !empty($replyMarkup) ? new InlineKeyboardMarkup($replyMarkup) : null,
                    'disablePreview' => $disablePreview,
                ]
            );
        } elseif ($message = $this->update->getMessage()) {
            $commands[] = new SendPhotoCommand(
                $this->update->getMessage()->getChat()->getId(),
                $photo,
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

    public function sendPhotoOrEditMessageTextOrSendMessage(
        ?string $photoFileId,
        MessageText $messageText,
        array $replyMarkup = [],
        bool $disablePreview = false
    )
    {
        $photo = new Photo($photoFileId);

        if ($photo->isNull()) {
            return $this->editMessageTextOrSendMessage($messageText, $replyMarkup, $disablePreview);
        } else {
            $this->deleteMessage();
            
            return $this->sendPhotoOrSendMessage($photoFileId, $messageText, $replyMarkup, $disablePreview);
        }
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
    public function sendMessage(MessageText $messageText, array $replyMarkup = null, bool $disablePreview = false, array $optionalParams = [])
    {
        $chatId = null;
        if ($message = $this->update->getMessage() ?? $this->update->getEditedMessage()) {
            $chatId = $message->getChat()->getId();
        } elseif ($callbackQuery = $this->update->getCallbackQuery()) {
            $chatId = $callbackQuery->getMessage()->getChat()->getId();
        }
        if (!is_null($chatId)) {
            $optionalParams = ArrayHelper::merge(
                [
                    'replyMarkup' => !empty($replyMarkup) ? new InlineKeyboardMarkup($replyMarkup) : null,
                    'disablePreview' => $disablePreview,
                ],
                ArrayHelper::filter($optionalParams, ['replyToMessageId','disableNotification','parseMode'])
            );

            $this->commands[] = new SendMessageCommand(
                $chatId,
                $messageText,
                $optionalParams
            );
        }
        return $this;
    }

    /**
     * @return $this
     */
    public function deleteMessage()
    {
        if ($message = $this->update->getMessage() ?? $this->update->getEditedMessage()) {
            $this->commands[] = new DeleteMessageCommand(
                $message->getChat()->getId(),
                $message->getMessageId()
            );
        } elseif ($callbackQuery = $this->update->getCallbackQuery()) {
            $this->commands[] = new DeleteMessageCommand(
                $callbackQuery->getMessage()->getChat()->getId(),
                $callbackQuery->getMessage()->getMessageId()
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
        if ($message = $this->update->getMessage() ?? $this->update->getEditedMessage()) {
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

    public function merge(array $commands)
    {
        $this->commands = array_merge($this->commands, $commands);

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
