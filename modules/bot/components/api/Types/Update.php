<?php

namespace app\modules\bot\components\api\Types;

use app\modules\bot\models\UserState;
use TelegramBot\Api\Types\Inline\ChosenInlineResult;
use TelegramBot\Api\Types\Inline\InlineQuery;
use TelegramBot\Api\Types\Payments\Query\PreCheckoutQuery;
use TelegramBot\Api\Types\Payments\Query\ShippingQuery;
use TelegramBot\Api\Types\Poll;
use TelegramBot\Api\Types\PollAnswer;
use Yii;

class Update extends \TelegramBot\Api\Types\Update
{
    /**
     * @var array
     */
    private $privateMessageIds;

    /**
     * @var object
     */
    public $chat;

    /**
     * @var object
     */
    public $from;

    /**
     * @var object
     */
    public $requestMessage;

    protected static $map = [
        'update_id' => true,
        'message' => Message::class,
        'edited_message' => Message::class,
        'channel_post' => Message::class,
        'edited_channel_post' => Message::class,
        'inline_query' => InlineQuery::class,
        'chosen_inline_result' => ChosenInlineResult::class,
        'callback_query' => CallbackQuery::class,
        'shipping_query' => ShippingQuery::class,
        'pre_checkout_query' => PreCheckoutQuery::class,
        'poll_answer' => PollAnswer::class,
        'poll' => Poll::class,
        'my_chat_member' => ChatMemberUpdated::class,
        'chat_member' => ChatMemberUpdated::class,
        'chat_join_request' => ChatJoinRequest::class,
    ];

    public function map($data)
    {
        parent::map($data);

        if ($callbackQuery = $this->getCallbackQuery()) {
            $this->chat = $callbackQuery->getMessage()->getChat();
            $this->from = $callbackQuery->getFrom();
            $this->requestMessage = $callbackQuery->getMessage();
        } elseif ($this->requestMessage = $this->getMessage() ?? $this->getEditedMessage()) {
            $this->chat = $this->requestMessage->getChat();
            $this->from = $this->requestMessage->getFrom();
        } elseif ($this->requestMessage = $this->getChannelPost() ?? $this->getEditedChannelPost()) {
            $this->chat = $this->requestMessage->getChat();
            $this->from = $this->requestMessage->getFrom();
        }
    }

    public function setPrivateMessageFromState(UserState $state)
    {
        $privateMessageIds = $state->getIntermediateField('private_message_ids', []);

        // fix previous invalid saving
        if (is_string($privateMessageIds)) {
            $privateMessageIds = json_decode($privateMessageIds, true);
        }

        if ($privateMessageIds) {
            $this->privateMessageIds = $privateMessageIds;
        }
    }

    public function getPrivateMessageIds()
    {
        return $this->privateMessageIds;
    }

    public function getChat()
    {
        return $this->chat;
    }

    public function getFrom()
    {
        return $this->from;
    }
    public function getRequestMessage()
    {
        return $this->requestMessage;
    }
}
