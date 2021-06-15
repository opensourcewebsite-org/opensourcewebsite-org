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
     * @var array
     */
    private $commands = [];

    /**
     * @param MessageText $messageText
     * @param array $replyMarkup
     * @param array $optionalParams
     *
     * @return ResponseBuilder
     */
    public function editMessageTextOrSendMessage(
        MessageText $messageText,
        array $replyMarkup = [],
        array $optionalParams = []
    ) {
        Yii::warning($replyMarkup);

        if ($this->getUpdate()) {
            if ($this->getChat()->isPrivate()) {
                $commands = $this->deleteOutdatedMessages();
            }

            if (!$this->getUpdate()->getCallbackQuery() || ($this->getUpdate()->getCallbackQuery()->getMessage()->getPhoto() === null)) {
                if ($callbackQuery = $this->getUpdate()->getCallbackQuery()) {
                    $this->answerCallbackQuery();

                    $commands[] = new EditMessageTextCommand(
                        $this->getChat()->getChatId(),
                        $this->getUpdate()->requestMessage->getMessageId(),
                        $messageText,
                        $this->collectEditMessageOptionalParams($replyMarkup, $optionalParams)
                    );
                } else {
                    $commands[] = new SendMessageCommand(
                        $this->getChat()->getChatId(),
                        $messageText,
                        $this->collectSendMessageOptionalParams($replyMarkup, $optionalParams)
                    );
                }
            } else {
                if ($callbackQuery = $this->getUpdate()->getCallbackQuery()) {
                    $this->answerCallbackQuery();
                    $this->deleteMessage();
                }

                $commands[] = new SendMessageCommand(
                    $this->getChat()->getChatId(),
                    $messageText,
                    $this->collectSendMessageOptionalParams($replyMarkup, $optionalParams)
                );
            }
        } else {
            $commands[] = new SendMessageCommand(
                $this->getChat()->getChatId(),
                $messageText,
                $this->collectSendMessageOptionalParams($replyMarkup, $optionalParams)
            );
        }

        if (!empty($commands)) {
            $this->commands = array_merge($this->commands, $commands);
        }

        return $this;
    }

    /**
     * Create array of commands for delete messages, sended earlier by bot in private chat
     *
     * @return array Array of delete messages commands
     */
    private function deleteOutdatedMessages(): array
    {
        $commands = [];

        if (($messageIds = $this->getUpdate()->getPrivateMessageIds())) {
            $excludeMessageDelete = 0;
            if ($this->getUpdate()->getCallbackQuery()) {
                $excludeMessageDelete = $this->getUpdate()->getCallbackQuery()
                    ->getMessage()
                    ->getMessageId();
            }

            $messageIds = array_filter($messageIds, function ($messageId) use ($excludeMessageDelete) {
                return $messageId != $excludeMessageDelete;
            });

            foreach ($messageIds as $messageId) {
                $commands[] = new DeleteMessageCommand(
                    $this->getChat()->getChatId(),
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
     * @param array $optionalParams
     *
     * @return ResponseBuilder
     */
    public function sendPhotoOrSendMessage(
        ?string $photoFileId,
        MessageText $messageText,
        array $replyMarkup = [],
        array $optionalParams = []
    ) {
        $photo = new Photo($photoFileId);

        if ($photo->isNull()) {
            return $this->sendMessage($messageText, $replyMarkup);
        }

        if ($callbackQuery = $this->getUpdate()->getCallbackQuery()) {
            $this->answerCallbackQuery();
            $commands[] = new SendPhotoCommand(
                $this->getChat()->getChatId(),
                $photo,
                $messageText,
                $this->collectEditMessageOptionalParams($replyMarkup, $optionalParams)
            );
        } elseif (($messageIds = $this->getUpdate()->getPrivateMessageIds())) {
            foreach ($messageIds as $messageId) {
                $commands[] = new DeleteMessageCommand(
                    $this->getChat()->getChatId(),
                    $messageId
                );
            }

            $commands[] = new SendMessageCommand(
                $this->getChat()->getChatId(),
                $messageText,
                $this->collectSendMessageOptionalParams($replyMarkup, $optionalParams)
            );
        } else {
            $commands[] = new SendMessageCommand(
                $this->getChat()->getChatId(),
                $messageText,
                $this->collectSendMessageOptionalParams($replyMarkup, $optionalParams)
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
     * @param  array $optionalParams
     *
     * @return array
     */
    private function collectEditMessageOptionalParams(
        array $replyMarkup,
        array $optionalParams = []
    ) : array {
        return $this->filterAndMergeOptionalParams(
            $replyMarkup,
            $optionalParams,
            [
                'disablePreview',
                'inlineMessageId',
            ]
        );
    }

    /**
     * filter params and create array of optional params  for send message api
     * command
     *
     * @param  array $replyMarkup
     * @param  array $optionalParams
     *
     * @return array
     */
    private function collectSendMessageOptionalParams(
        array $replyMarkup,
        array $optionalParams = []
    ) : array {
        return $this->filterAndMergeOptionalParams(
            $replyMarkup,
            $optionalParams,
            [
                'disablePreview',
                'replyToMessageId',
                'disableNotification',
                'parseMode',
            ]
        );
    }

    /**
     *
     * @param  array $replyMarkup
     * @param  array $optionalParams
     * @param  array $optionalParamsFilter
     *
     * @return array
     */
    private function filterAndMergeOptionalParams(
        array $replyMarkup,
        array $optionalParams,
        array $optionalParamsFilter
    ) : array {
        $optionalParams = ArrayHelper::merge(
            [
                'replyMarkup' => !empty($replyMarkup) ? new InlineKeyboardMarkup($replyMarkup) : null,
            ],
            ArrayHelper::filter($optionalParams, $optionalParamsFilter)
        );

        return $optionalParams;
    }

    /**
     * @param ?string $photoFileId
     * @param MessageText $messageText
     * @param array $replyMarkup
     * @param  array $optionalParams
     *
     * @return ResponseBuilder
     */
    public function sendPhotoOrEditMessageTextOrSendMessage(
        ?string $photoFileId,
        MessageText $messageText,
        array $replyMarkup = [],
        array $optionalParams = []
    ) {
        $photo = new Photo($photoFileId);

        if ($photo->isNull()) {
            return $this->editMessageTextOrSendMessage($messageText, $replyMarkup, $optionalParams);
        } else {
            $this->deleteMessage();

            return $this->sendPhotoOrSendMessage($photoFileId, $messageText, $replyMarkup, $optionalParams);
        }
    }

    /**
     * @param array|null $replyMarkup
     * @return ResponseBuilder
     */
    public function editMessageReplyMarkup(
        array $replyMarkup = []
    ) {
        if ($callbackQuery = $this->getUpdate()->getCallbackQuery()) {
            $this->answerCallbackQuery();
            $this->commands[] = new EditMessageReplyMarkupCommand(
                $this->getChat()->getChatId(),
                $this->getUpdate()->requestMessage->getMessageId(),
                !empty($replyMarkup) ? new InlineKeyboardMarkup($replyMarkup) : null
            );
        }

        return $this;
    }

    /**
     * @return ResponseBuilder
     */
    public function removeInlineKeyboardMarkup()
    {
        if ($callbackQuery = $this->getUpdate()->getCallbackQuery()) {
            $this->answerCallbackQuery();
            $this->commands[] = new EditMessageReplyMarkupCommand(
                $this->getChat()->getChatId(),
                $this->getUpdate()->requestMessage->getMessageId(),
                null
            );
        }

        return $this;
    }

    /**
     * @param MessageText|null $messageText
     * @param bool $showAlert
     *
     * @return ResponseBuilder
     */
    public function answerCallbackQuery(MessageText $messageText = null, bool $showAlert = false)
    {
        if ($callbackQuery = $this->getUpdate()->getCallbackQuery()) {
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
     * @param  array $optionalParams
     *
     * @return ResponseBuilder
     */
    public function sendMessage(
        MessageText $messageText,
        array $replyMarkup = [],
        array $optionalParams = []
    ) {
        Yii::warning($replyMarkup);

        $this->commands[] = new SendMessageCommand(
            $this->getChat()->getChatId(),
            $messageText,
            $this->collectSendMessageOptionalParams($replyMarkup, $optionalParams)
        );

        return $this;
    }

    /**
     * @return ResponseBuilder
     */
    public function deleteMessage()
    {
        $this->commands[] = new DeleteMessageCommand(
            $this->getChat()->getChatId(),
            $this->getUpdate()->requestMessage->getMessageId()
        );

        return $this;
    }

    /**
     * @param int $longitude
     * @param int $latitude
     *
     * @return ResponseBuilder
     */
    public function sendLocation(int $longitude, int $latitude)
    {
        $this->commands[] = new SendLocationCommand(
            $this->getChat()->getChatId(),
            $longitude,
            $latitude
        );

        return $this;
    }

    /**
     * @param array $commands
     *
     * @return ResponseBuilder
     */
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

    public function send()
    {
        $answer = false;

        foreach ($this->commands as $command) {
            try {
                $answer[] = $command->send($this->getBotApi());
            } catch (\Exception $e) {
                Yii::error('[' . get_class($command) . '] ' . $e->getCode() . ' ' . $e->getMessage(), 'bot');
            }
        }

        if ($answer && (count($answer) == 1)) {
            $answer = current($answer);
        }

        return $answer;
    }

    /**
     * @return Chat|null
     */
    public function getChat()
    {
        if (Yii::$container->hasSingleton('chat')) {
            return Yii::$container->get('chat');
        }

        return null;
    }

    /**
     * @return Update|null
     */
    public function getUpdate()
    {
        if (Yii::$container->hasSingleton('update')) {
            return Yii::$container->get('update');
        }

        return null;
    }

    /**
     * @return Bot|null
     */
    public function getBot()
    {
        if (Yii::$container->hasSingleton('bot')) {
            return Yii::$container->get('bot');
        }

        return null;
    }

    /**
     * @return BotApi
     */
    public function getBotApi()
    {
        if (Yii::$container->hasSingleton('botApi')) {
            return Yii::$container->get('botApi');
        } elseif ($this->getBot()) {
            $botApi = new BotApi($this->getBot()->token);

            if ($botApi) {
                if (isset(Yii::$app->params['telegramProxy'])) {
                    $botApi->setProxy(Yii::$app->params['telegramProxy']);
                }

                return $this->setBotApi($botApi);
            }
        }

        return null;
    }

    /**
     * @param BotApi $botApi
     *
     * @return BotApi
     */
    public function setBotApi(BotApi $botApi)
    {
        Yii::$container->setSingleton('botApi', $botApi);

        return $botApi;
    }

    /**
     * @param int $chatId
     *
     * @return ResponseBuilder
     */
    public function setChatId(int $chatId)
    {
        $this->getChat()->setChatId($chatId);

        return $this;
    }
}
