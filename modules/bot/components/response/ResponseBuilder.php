<?php

namespace app\modules\bot\components\response;

use app\modules\bot\components\api\BotApi;
use app\modules\bot\components\api\Types\Update;
use app\modules\bot\components\helpers\MessageText;
use app\modules\bot\components\helpers\Photo;
use app\modules\bot\components\response\commands\AnswerCallbackQueryCommand;
use app\modules\bot\components\response\commands\DeleteMessageCommand;
use app\modules\bot\components\response\commands\EditMessageReplyMarkupCommand;
use app\modules\bot\components\response\commands\EditMessageTextCommand;
use app\modules\bot\components\response\commands\EditPhotoCommand;
use app\modules\bot\components\response\commands\ReplaceMessageTextCommand;
use app\modules\bot\components\response\commands\SendLocationCommand;
use app\modules\bot\components\response\commands\SendMessageCommand;
use app\modules\bot\components\response\commands\SendPhotoCommand;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * Class ResponseBuilder
 * @package app\modules\bot\components\response
 */
class ResponseBuilder
{
    /**
     * @var null
     */
    private $command = null;

    /**
     * @var null
     */
    protected $chatId = null;

    public function __construct(string $chatId = null)
    {
        $this->chatId = $chatId;
    }

    /**
     * @param MessageText $messageText
     * @param array $replyMarkup
     * @param array $optionalParams
     * @return ResponseBuilder
     */
    public function editMessageTextOrSendMessage(
        MessageText $messageText,
        array $replyMarkup = [],
        array $optionalParams = []
    ) {
        if ($this->getUpdate()) {
            if ($this->getChat()->isPrivate()) {
                // Delete messages, sent earlier by bot in private chat
                if ($messageIds = $this->getUpdate()->getPrivateMessageIds()) {
                    // Ignore the message with callbackQuery
                    if ($this->getUpdate()->getCallbackQuery()) {
                        $excludeMessageId = $this->getUpdate()->getCallbackQuery()
                            ->getMessage()
                            ->getMessageId();

                        $key = array_search($excludeMessageId, $messageIds);

                        if ($key !== false) {
                            unset($messageIds[$key]);
                        }
                    }

                    foreach ($messageIds as $key => $messageId) {
                        $this->deleteMessage($messageId)->send();
                    }
                }
            }

            if ($this->getUpdate()->getCallbackQuery() && ($this->getChatId() == $this->getUpdate()->getChat()->getId())) {
                if (!$this->getUpdate()->getRequestMessage()->getPhoto()) {
                    $this->command = new EditMessageTextCommand(
                        $this->getChatId(),
                        $this->getUpdate()->getRequestMessage()->getMessageId(),
                        $messageText,
                        $this->collectEditMessageOptionalParams($replyMarkup, $optionalParams)
                    );

                    return $this;
                } else {
                    $this->deleteMessage()->send();
                }
            }
        }

        $this->command = new SendMessageCommand(
            $this->getChatId(),
            $messageText,
            $this->collectSendMessageOptionalParams($replyMarkup, $optionalParams)
        );

        return $this;
    }

    /**
     * @param ?string $photoFileId
     * @param MessageText $messageText
     * @param array $replyMarkup
     * @param array $optionalParams
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
            $this->answerCallbackQuery()->send();

            $this->command = new SendPhotoCommand(
                $this->getChatId(),
                $photo,
                $messageText,
                $this->collectEditMessageOptionalParams($replyMarkup, $optionalParams)
            );
        } elseif (($messageIds = $this->getUpdate()->getPrivateMessageIds())) {
            foreach ($messageIds as $messageId) {
                $this->deleteMessage($messageId)->send();
            }

            $this->command = new SendMessageCommand(
                $this->getChatId(),
                $messageText,
                $this->collectSendMessageOptionalParams($replyMarkup, $optionalParams)
            );
        } else {
            $this->command = new SendMessageCommand(
                $this->getChatId(),
                $messageText,
                $this->collectSendMessageOptionalParams($replyMarkup, $optionalParams)
            );
        }

        return $this;
    }

    /**
     * @param ?string $photoFileId
     * @param MessageText $messageText
     * @param array $replyMarkup
     * @param array $optionalParams
     * @return ResponseBuilder
     */
    public function sendPhoto(
        ?string $photoFileId,
        MessageText $messageText,
        array $replyMarkup = [],
        array $optionalParams = []
    ) {
        $photo = new Photo($photoFileId);

        if (!$photo->isNull()) {
            $this->command = new SendPhotoCommand(
                $this->getChatId(),
                $photo,
                $messageText,
                $this->collectSendMessageOptionalParams($replyMarkup, $optionalParams)
            );
        }

        return $this;
    }

    /**
     * @param ?string $photoFileId
     * @param MessageText $messageText
     * @param array $replyMarkup
     * @param array $optionalParams
     * @return ResponseBuilder
     */
    public function editPhotoOrSendPhoto(
        ?string $photoFileId,
        MessageText $messageText,
        array $replyMarkup = [],
        array $optionalParams = []
    ) {
        $photo = new Photo($photoFileId);

        if (!$photo->isNull()) {
            if ($this->getUpdate()) {
                if ($this->getUpdate()->getCallbackQuery() && ($this->getChatId() == $this->getUpdate()->getChat()->getId())) {
                    if ($this->getUpdate()->getRequestMessage()->getPhoto()) {
                        $this->command = new EditPhotoCommand(
                            $this->getChatId(),
                            $this->getUpdate()->getRequestMessage()->getMessageId(),
                            $photo,
                            $messageText,
                            $this->collectSendMessageOptionalParams($replyMarkup, $optionalParams)
                        );

                        return $this;
                    } else {
                        $this->deleteMessage()->send();
                    }
                }
            }

            $this->command = new SendPhotoCommand(
                $this->getChatId(),
                $photo,
                $messageText,
                $this->collectSendMessageOptionalParams($replyMarkup, $optionalParams)
            );
        }

        return $this;
    }

    /**
     * filter params and create array of optional params  for edit message api
     * command
     *
     * @param array $replyMarkup
     * @param array $optionalParams
     * @return array
     */
    private function collectEditMessageOptionalParams(
        array $replyMarkup,
        array $optionalParams = []
    ): array {
        Yii::warning($replyMarkup);

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
     * @param array $replyMarkup
     * @param array $optionalParams
     * @return array
     */
    private function collectSendMessageOptionalParams(
        array $replyMarkup,
        array $optionalParams = []
    ): array {
        Yii::warning($replyMarkup);

        return $this->filterAndMergeOptionalParams(
            $replyMarkup,
            $optionalParams,
            [
                'messageThreadId',
                'parseMode',
                'disablePreview',
                'replyToMessageId',
                'disableNotification',
            ]
        );
    }

    /**
     * @param array $replyMarkup
     * @param array $optionalParams
     * @param array $optionalParamsFilter
     * @return array
     */
    private function filterAndMergeOptionalParams(
        array $replyMarkup,
        array $optionalParams,
        array $optionalParamsFilter
    ): array {
        foreach ($replyMarkup as $key1 => $array1) {
            foreach ($array1 as $key2 => $array2) {
                // remove all items vith visible = 0
                if (isset($array2['visible'])) {
                    if ($array2['visible']) {
                        unset($replyMarkup[$key1][$key2]['visible']);
                    } else {
                        unset($replyMarkup[$key1][$key2]);
                    }
                }
            }
        }

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
     * @param array $optionalParams
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
            $this->deleteMessage()->send();

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
            $this->answerCallbackQuery()->send();

            $this->command = new EditMessageReplyMarkupCommand(
                $this->getChatId(),
                $this->getUpdate()->getRequestMessage()->getMessageId(),
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
            $this->answerCallbackQuery()->send();

            $this->command = new EditMessageReplyMarkupCommand(
                $this->getChatId(),
                $this->getUpdate()->getRequestMessage()->getMessageId(),
                null
            );
        }

        return $this;
    }

    /**
     * @param MessageText|null $messageText
     * @param bool $showAlert
     * @return AnswerCallbackQueryCommand
     */
    public function answerCallbackQuery(MessageText $messageText = null, bool $showAlert = false)
    {
        if ($this->getUpdate() && ($callbackQuery = $this->getUpdate()->getCallbackQuery())) {
            $this->command = new AnswerCallbackQueryCommand(
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
     * @param array $optionalParams
     * @return ResponseBuilder
     */
    public function sendMessage(
        MessageText $messageText,
        array $replyMarkup = [],
        array $optionalParams = []
    ) {
        $this->command = new SendMessageCommand(
            $this->getChatId(),
            $messageText,
            $this->collectSendMessageOptionalParams($replyMarkup, $optionalParams)
        );

        return $this;
    }

    /**
     * @param int $messageId
     * @param MessageText $messageText
     * @param array|null $replyMarkup
     * @param array $optionalParams
     * @return ResponseBuilder
     */
    public function editMessage(
        int $messageId,
        MessageText $messageText,
        array $replyMarkup = [],
        array $optionalParams = []
    ) {
        $this->command = new EditMessageTextCommand(
            $this->getChatId(),
            $messageId,
            $messageText,
            $this->collectSendMessageOptionalParams($replyMarkup, $optionalParams)
        );

        return $this;
    }

    /**
     * @param int $messageId
     * @param int $chatId
     * @return DeleteMessageCommand
     */
    public function deleteMessage(int $messageId = null, int $chatId = null)
    {
        if ($this->getUpdate() && ($this->getChatId() != $this->getUpdate()->getChat()->getId()) && !$chatId) {
            return $this;
        }

        if (!$chatId) {
            $chatId = $this->getChatId();
        }

        if (!$messageId && $this->getUpdate()) {
            $messageId = $this->getUpdate()->getRequestMessage()->getMessageId();
        }

        if ($messageId) {
            $this->command = new DeleteMessageCommand($chatId, $messageId);
        }

        return $this;
    }

    /**
     * @param int $longitude
     * @param int $latitude
     * @return ResponseBuilder
     */
    public function sendLocation(int $longitude, int $latitude)
    {
        $this->command = new SendLocationCommand(
            $this->getChatId(),
            $longitude,
            $latitude
        );

        return $this;
    }

    /**
     * @return array
     */
    public function build()
    {
        return $this->send();
    }

    public function send()
    {
        $privateMessageIds = [];
        $answer = false;
        $isPrivateChat = false;

        if ($this->getUpdate() && ($this->getChatId() == $this->getUpdate()->getChat()->getId()) && $this->getChat()->isPrivate()) {
            $isPrivateChat = true;
        }

        if ($this->command) {
            try {
                $answer = $this->command->send();
                // Remember ids of all bot messages in private chat to delete them later
                if ($isPrivateChat && ($messageId = $this->command->getMessageId())) {
                    $privateMessageIds []= $messageId;
                }

                $this->command = null;
            } catch (\Exception $e) {
                Yii::error(get_class($this->command) . ' ' . $e->getCode() . ' ' . $e->getMessage(), 'bot');
            }
        }

        if ($privateMessageIds) {
            $this->getUserState()->setIntermediateField('private_message_ids', json_encode($privateMessageIds));
            $this->getUserState()->save($this->getUser());
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
     * @return BotApi|null
     */
    public function getBotApi()
    {
        if ($bot = $this->getBot()) {
            return $bot->getBotApi();
        }

        return null;
    }

    /**
     * @return User|null
     */
    public function getUser()
    {
        if (Yii::$container->hasSingleton('user')) {
            return Yii::$container->get('user');
        }

        return null;
    }

    /**
     * @return UserState|null
     */
    public function getUserState()
    {
        if (Yii::$container->hasSingleton('userState')) {
            return Yii::$container->get('userState');
        }

        return null;
    }

    /**
     * @return int|null
     */
    public function getChatId()
    {
        return $this->chatId ?: $this->getChat()->getChatId();
    }

    /**
     * @param int $chatId
     * @return ResponseBuilder
     */
    public function setChatId(int $chatId)
    {
        $this->chatId = $chatId;

        return $this;
    }
}
