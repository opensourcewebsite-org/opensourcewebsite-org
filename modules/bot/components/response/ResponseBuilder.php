<?php

namespace app\modules\bot\components\response;

use Yii;
use app\modules\bot\components\helpers\MessageText;
use app\modules\bot\components\helpers\Photo;
use app\modules\bot\components\response\commands\AnswerCallbackQueryCommand;
use app\modules\bot\components\response\commands\DeleteMessageCommand;
use app\modules\bot\components\response\commands\EditMessageReplyMarkupCommand;
use app\modules\bot\components\response\commands\EditMessageTextCommand;
use app\modules\bot\components\response\commands\ReplaceMessageTextCommand;
use app\modules\bot\components\response\commands\SendLocationCommand;
use app\modules\bot\components\response\commands\SendMessageCommand;
use app\modules\bot\components\response\commands\SendPhotoCommand;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use TelegramBot\Api\Types\Update;
use yii\helpers\ArrayHelper;
use TelegramBot\Api\BotApi;

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
     * @var BotApi
     */
    private $botApi;

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
        $commands = $this->deleteOutdatedMessages();

        if (!$this->update->getCallbackQuery() || $this->update->getCallbackQuery()->getMessage()->getPhoto() === null) {
            if ($callbackQuery = $this->update->getCallbackQuery()) {
                $this->answerCallbackQuery();
                $commands[] = new EditMessageTextCommand(
                    $callbackQuery->getMessage()->getChat()->getId(),
                    $callbackQuery->getMessage()->getMessageId(),
                    $messageText,
                    $this->collectEditMessageOptionalParams($replyMarkup, $disablePreview, $optionalParams)
                );
            } elseif ($message = $this->update->getMessage() ?? $this->update->getEditedMessage()) {
                $commands[] = new SendMessageCommand(
                    $message->getChat()->getId(),
                    $messageText,
                    $this->collectSendMessageOptionalParams($replyMarkup, $disablePreview, $optionalParams)
                );
            }
        } else {
            if ($callbackQuery = $this->update->getCallbackQuery()) {
                $this->answerCallbackQuery();
                $this->deleteMessage();

                $commands[] = new SendMessageCommand(
                    $callbackQuery->getMessage()->getChat()->getId(),
                    $messageText,
                    $this->collectSendMessageOptionalParams($replyMarkup, $disablePreview, $optionalParams)
                );
            } elseif ($message = $this->update->getMessage()) {
                $commands[] = new SendMessageCommand(
                    $message->getChat()->getId(),
                    $messageText,
                    $this->collectSendMessageOptionalParams($replyMarkup, $disablePreview, $optionalParams)
                );
            }
        }

        if (!empty($commands)) {
            $this->commands = array_merge($this->commands, $commands);
        }
        return $this;
    }

    /**
     * Create array of commands for delete messages,
     * sended earlier by bot in private chat
     * @return array Array of delete messages commands
     */
    private function deleteOutdatedMessages(): array
    {
        $commands = [];
        if (($messageIds = $this->update->getPrivateMessageIds())
            && ($chatId = $this->update->getPrivateMessageChatId())) {
            $excludeMessageDelete = 0;
            if ($this->update->getCallbackQuery()) {
                $excludeMessageDelete = $this->update->getCallbackQuery()
                    ->getMessage()
                    ->getMessageId();
            }

            $messageIds = array_filter($messageIds, function ($messageId) use ($excludeMessageDelete) {
                return $messageId != $excludeMessageDelete;
            });

            foreach ($messageIds as $messageId) {
                $commands[] = new DeleteMessageCommand(
                    $chatId,
                    $messageId
                );
            }
        }
        return $commands;
    }

    /**
     * @param ?string $photoFileId
     * @param MessageText $messageText
     * @param array $replyMarkup
     * @param bool $disablePreview
     * @return $this
     */
    public function sendPhotoOrSendMessage(
        ?string $photoFileId,
        MessageText $messageText,
        array $replyMarkup = [],
        bool $disablePreview = false
    ) {
        $photo = new Photo($photoFileId);

        if ($photo->isNull()) {
            return $this->sendMessage($messageText, $replyMarkup);
        }

        if ($callbackQuery = $this->update->getCallbackQuery()) {
            $this->answerCallbackQuery();
            $commands[] = new SendPhotoCommand(
                $callbackQuery->getMessage()->getChat()->getId(),
                $photo,
                $messageText,
                $this->collectEditMessageOptionalParams($replyMarkup, $disablePreview)
            );
        } elseif (($messageIds = $this->update->getPrivateMessageIds())
            && ($chatId = $this->update->getPrivateMessageChatId())) {
            foreach ($messageIds as $messageId) {
                $commands[] = new DeleteMessageCommand(
                    $chatId,
                    $messageId
                );
            }
            $commands[] = new SendMessageCommand(
                $chatId,
                $messageText,
                $this->collectSendMessageOptionalParams($replyMarkup, $disablePreview)
            );
        } elseif ($message = $this->update->getMessage() ?? $this->update->getEditedMessage()) {
            $commands[] = new SendMessageCommand(
                $message->getChat()->getId(),
                $messageText,
                $this->collectSendMessageOptionalParams($replyMarkup, $disablePreview)
            );
        }

        if (!empty($commands)) {
            $this->commands = array_merge($this->commands, $commands);
        }
        return $this;
    }

    /**
     * filter params and create array of optional params  for edit message api
     * command
     *
     * @param  array $replyMarkup
     * @param  bool              $disablePreview
     * @param  array                $optionalParams
     * @return array
     */
    private function collectEditMessageOptionalParams(array $replyMarkup, bool $disablePreview, array $optionalParams = []):array
    {
        return $this->filterAndMergeOptionalParams(
            $replyMarkup,
            $disablePreview,
            $optionalParams,
            ['inlineMessageId']
        );
    }

    /**
     * filter params and create array of optional params  for send message api
     * command
     *
     * @param  array $replyMarkup
     * @param  bool              $disablePreview
     * @param  array                $optionalParams
     * @return array
     */
    private function collectSendMessageOptionalParams(array $replyMarkup, bool $disablePreview, array $optionalParams = []):array
    {
        return $this->filterAndMergeOptionalParams(
            $replyMarkup,
            $disablePreview,
            $optionalParams,
            ['replyToMessageId','disableNotification','parseMode']
        );
    }

    /**
     *
     * @param  array $replyMarkup
     * @param  bool              $disablePreview
     * @param  array                $optionalParams
     * @param  array                $optionalParamsFilter
     * @return array
     */
    private function filterAndMergeOptionalParams(array $replyMarkup, bool $disablePreview, array $optionalParams, array $optionalParamsFilter):array
    {
        $optionalParams = ArrayHelper::merge(
            [
                'replyMarkup' => !empty($replyMarkup) ? new InlineKeyboardMarkup($replyMarkup) : null,
                'disablePreview' => $disablePreview,
            ],
            ArrayHelper::filter($optionalParams, $optionalParamsFilter)
        );
        return $optionalParams;
    }

    /**
     * @param ?string $photoFileId
     * @param MessageText $messageText
     * @param array $replyMarkup
     * @param bool $disablePreview
     * @return $this
     */
    public function sendPhotoOrEditMessageTextOrSendMessage(
        ?string $photoFileId,
        MessageText $messageText,
        array $replyMarkup = [],
        bool $disablePreview = false
    ) {
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
        array $replyMarkup = []
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
    public function sendMessage(
        MessageText $messageText,
        array $replyMarkup = [],
        bool $disablePreview = false,
        array $optionalParams = []
    ) {
        $chatId = null;
        if ($message = $this->update->getMessage() ?? $this->update->getEditedMessage()) {
            $chatId = $message->getChat()->getId();
        } elseif ($callbackQuery = $this->update->getCallbackQuery()) {
            $chatId = $callbackQuery->getMessage()->getChat()->getId();
        }
        if (!is_null($chatId)) {
            $this->commands[] = new SendMessageCommand(
                $chatId,
                $messageText,
                $this->collectSendMessageOptionalParams($replyMarkup, $disablePreview, $optionalParams)
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

    /**
     * @param Update $update
     * @return ResponseBuilder
     */
    public function setBotApi(BotApi &$botApi)
    {
        $this->botApi = $botApi;
    }

    /**
     * @return BotApi
     */
    protected function getBotApi()
    {
        return $this->botApi;
    }

    public function send()
    {
        $answer = false;

        foreach ($this->commands as $command) {
            try {
                $answer[] = $command->send($this->botApi);
            } catch (\Exception $e) {
                    Yii::error('[' . get_class($command) . '] ' . $e->getCode() . ' ' . $e->getMessage(), 'bot');
                }
            }

        if ($answer && (count($answer) == 1)) {
            $answer = current($answer);
        }

        return $answer;
    }
}
