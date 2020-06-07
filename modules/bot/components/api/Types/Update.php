<?php

namespace app\modules\bot\components\api\Types;

use app\modules\bot\models\UserState;
use TelegramBot\Api\Types\Inline\ChosenInlineResult;
use TelegramBot\Api\Types\Inline\InlineQuery;
use TelegramBot\Api\Types\Payments\Query\PreCheckoutQuery;
use TelegramBot\Api\Types\Payments\Query\ShippingQuery;
use TelegramBot\Api\Types\CallbackQuery;

class Update extends \TelegramBot\Api\Types\Update
{
    private $privateMessageIds;
    private $privateMessageChatId;

    static protected $map = [
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
    ];

    public function setPrivateMessageFromState(UserState $state)
    {
        $privateMessageIds = json_decode($state->getIntermediateField('private_message_ids', json_encode([])));
        $privateMessageChatId = $state->getIntermediateField('private_message_chat_id', null);

        if ($privateMessageIds && $privateMessageChatId) {
            $this->privateMessageChatId = $privateMessageChatId;
            $this->privateMessageIds = $privateMessageIds;
        }
    }

    public function getPrivateMessageIds()
    {
        return $this->privateMessageIds;
    }

    public function getPrivateMessageChatId()
    {
        return $this->privateMessageChatId;
    }
}
