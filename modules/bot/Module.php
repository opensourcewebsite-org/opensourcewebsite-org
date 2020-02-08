<?php

namespace app\modules\bot;

use app\modules\bot\models\BotClient;
use app\modules\bot\telegram\BotApiClient;
use app\modules\bot\telegram\Message;
use yii\base\InvalidRouteException;
use yii\base\InvalidConfigException;
use app\modules\bot\models\BotInsideMessage;
use app\modules\bot\models\BotOutsideMessage;

/**
 * OSW Bot module definition class
 * @link https://t.me/opensourcewebsite_bot
 */
class Module extends \yii\base\Module
{
    /** @var BotApiClient */
    public $botApi;

    /**
     * @param BotApiClient $botApi
     */
    public function initBotComponents($botApi)
    {
        $this->botApi = $botApi;

        $botConfig = require(\Yii::getAlias('@app/modules/bot/config') . '/bot.php');
        \Yii::configure(\Yii::$app, $botConfig);

        if ($botApi->getMessage()) {
            \Yii::$app->requestMessage->setMessage($botApi->getMessage());
        } elseif ($callbackQuery = $botApi->getCallbackQuery()) {
            $messageData = $callbackQuery['message'];
            $messageData['from'] = $callbackQuery['from'];
            \Yii::$app->requestMessage->map($messageData);
        }

        if ($botApi->getMessage() && !$botApi->getMessage()->getFrom()->isBot()) {
            /** @var Module $botModule */
            $botApi->bot_client_id = $botApi->saveClientInfo();
            $botApi->type = $botApi->getMessage()->isBotCommand() ? BotOutsideMessage::TYPE_COMMAND
                : BotOutsideMessage::TYPE_ORDINARY_TEXT;

            BotOutsideMessage::saveMessage($botApi);
        }

        /** @var BotClient $botClient */
        if ($botClient = \Yii::$app->botClient->getModel()) {
            \Yii::$app->language = $botClient->language_code;
        }
    }

    /**
     * @return bool
     * @throws \TelegramBot\Api\Exception
     * @throws \TelegramBot\Api\InvalidArgumentException
     */
    public function dispatchRoute()
    {
        $result = false;

        if (\Yii::$app->commandRouter->dispatchRoute($this->botApi)) {
            /** @var Message $responseMessage */
            $responseMessage = \Yii::$app->responseMessage;
            $id = $responseMessage->getMessageId();
            $text = Message::prepareText($responseMessage->getText());

            $sentMessage = null;
            $chatId = \Yii::$app->requestMessage->getChat()->getId();
            if ($id) {
                $sentMessage = $this->botApi->editMessageText(
                    $chatId,
                    $id,
                    $text,
                    'html',
                    null,
                    \Yii::$app->responseMessage->getKeyboard()
                );
            } else {
                $sentMessage = $this->botApi->sendMessage(
                    $chatId,
                    $text,
                    'html',
                    null,
                    null,
                    \Yii::$app->responseMessage->getKeyboard()
                );
            }

            BotInsideMessage::saveMessage($sentMessage, $chatId);

            $result = true;
        }

        return $result;
    }

    /**
     *
     * Runs a command controller action specified by a route.
     *
     * @param string $route the route that specifies the action.
     * @param array $params the parameters to be passed to the action
     *
     * @return mixed the result of the action.
     * @throws InvalidConfigException if the requested route cannot be resolved into an action successfully.
     * @throws InvalidRouteException
     */
    public function runAction($route, $params = [])
    {
        $parts = $this->createController($route);
        if (is_array($parts)) {
            /* @var $controller CommandController */
            list($controller, $actionID) = $parts;
            $oldController = \Yii::$app->controller;
            \Yii::$app->controller = $controller;
            $result = $controller->runAction($actionID, $params, true);
            if ($oldController !== null) {
                \Yii::$app->controller = $oldController;
            }

            return $result;
        }

        $id = $this->getUniqueId();
        throw new InvalidRouteException('Unable to resolve the request "' . ($id === '' ? $route
                : $id . '/' . $route) . '".');
    }
}
