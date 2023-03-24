<?php

declare(strict_types=1);

namespace app\modules\bot\models;

use app\modules\bot\components\api\BotApi;
use Yii;

/**
 * @package app\modules\bot\models
 */
class Bot
{
    public ?string $username = null;

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

        return $this->botApi->setWebhook($url);
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
        return $this->botApi->deleteWebhook();
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
