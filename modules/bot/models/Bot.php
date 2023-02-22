<?php

declare(strict_types=1);

namespace app\modules\bot\models;

use app\modules\bot\components\api\BotApi;
use TelegramBot\Api\Types\CallbackQuery;
use TelegramBot\Api\Types\ChatJoinRequest;
use TelegramBot\Api\Types\ChatMemberUpdated;
use TelegramBot\Api\Types\Inline\ChosenInlineResult;
use TelegramBot\Api\Types\Inline\InlineQuery;
use TelegramBot\Api\Types\Message;
use TelegramBot\Api\Types\Payments\Query\PreCheckoutQuery;
use TelegramBot\Api\Types\Payments\Query\ShippingQuery;
use TelegramBot\Api\Types\Poll;
use TelegramBot\Api\Types\PollAnswer;
use Yii;

/**
 * @package app\modules\bot\models
 */
class Bot
{
    public ?string $name = null;

    public ?string $token = null;

    public BotApi $botApi;

    public function __construct()
    {
        if (isset(Yii::$app->params['bot'])
            && isset(Yii::$app->params['bot']['username'])
            && isset(Yii::$app->params['bot']['token'])) {
            $this->username = Yii::$app->params['bot']['username'];
            $this->token = Yii::$app->params['bot']['token'];
            $this->botApi = new BotApi($this->token);

            if (isset(Yii::$app->params['bot']['proxy'])) {
                $this->botApi->setProxy(Yii::$app->params['bot']['proxy']);
            }
        }
    }

    /**
     * Set webhook for bot token using telegram API
     *
     * @return string
     * @throws \TelegramBot\Api\Exception
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function setWebhook()
    {
        $url = Yii::$app->urlManager->createAbsoluteUrl(['/webhook/telegram-bot/' . $this->token]);
        $url = str_replace('http:', 'https:', $url);
        $allowedUpdates = [
            'message',
            'edited_message',
            'channel_post',
            'edited_channel_post',
            'inline_query',
            'chosen_inline_result',
            'callback_query',
            'shipping_query',
            'pre_checkout_query',
            'poll_answer',
            'poll',
            'my_chat_member',
            'chat_member',
            'chat_join_request',
        ];
        $response = $this->botApi->setWebhook($url, null, json_encode($allowedUpdates));

        return $response;
    }

    /**
     * Delete webhook for bot token using telegram API
     *
     * @return mixed
     * @throws \TelegramBot\Api\Exception
     * @throws \TelegramBot\Api\HttpException
     * @throws \TelegramBot\Api\InvalidJsonException
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function deleteWebhook()
    {
        $response = $this->botApi->deleteWebhook();

        return $response;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function getToken()
    {
        return $this->token;
    }

    public function getBotApi()
    {
        return $this->botApi;
    }

    public function getProviderUserId()
    {
        return explode(':', $this->getToken())[0];
    }
}
