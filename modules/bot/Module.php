<?php

namespace app\modules\bot;

use Yii;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Types\Update;
use app\modules\bot\models\Bot;
use app\modules\bot\models\BotClient;
use yii\base\InvalidRouteException;
use app\models\User;

/**
 * admin module definition class
 */
class Module extends \yii\base\Module
{
    /** 
     * @var TelegramBot\Api\BotApi
     */
    private $botApi;

    /**
     * @var \TelegramBot\Api\Types\Chat
     */
    private $chat;

    private $requestId;

    /**
     * @var app\modules\bot\models\BotClient
     */
    public $botClient;

    /**
     * @var \TelegramBot\Api\Types\Update
     */
    public $update;

    /**
     * @var \app\models\User
     */
    public $user;

    public function init()
    {
        parent::init();

        Yii::configure($this, require __DIR__ . '/config.php');
    }

    public function handleInput($input, $token)
    {
        $updateArray = json_decode($input, TRUE);
        $this->update = Update::fromResponse($updateArray);
        $botInfo = Bot::findOne(['token' => $token]);
        if ($botInfo) {
            $this->botApi = new BotApi($botInfo->token);

            if (isset(Yii::$app->params['telegramProxy'])) {
                $this->botApi->setProxy(Yii::$app->params['telegramProxy']);
            }

            $this->botClient = $this->resolveBotClient($this->update);
            if (isset($this->botClient))
            {
                Yii::$app->language = $this->botClient->language_code;

                $result = $this->dispatchRoute($this->update);
            }
            else
            {
                $result = false;
            }
        }
        return $result;
    }

    /**
     * @param $update \TelegramBot\Api\Types\Update
     *
     * @return \app\modules\bot\models\BotClient
     */
    private function resolveBotClient($update)
    {
        if ($update->getMessage())
        {
            $from = $update->getMessage()->getFrom();
            $this->chat = $update->getMessage()->getChat();
            $this->requestId = $update->getMessage()->getMessageId();
        }
        else if ($update->getCallbackQuery())
        {
            $from = $update->getCallbackQuery()->getFrom();
            $this->chat = $update->getCallbackQuery()->getMessage()->getChat();
            $this->requestId = $update->getCallbackQuery()->getId();
        }

        if ($from)
        {
            $botClient = BotClient::findOne(['provider_user_id' => $from->getId()]);
            if (!isset($botClient))
            {
                $botClient = new BotClient();
                $botClient->setAttributes([
                    'provider_user_id' => $from->getId(),
                    'language_code' => $from->getLanguageCode(),
                ]);   
            }

            if (!isset($botClient->user_id))
            {
                $this->user = User::genereateUserWithRandomPassword();
                if ($this->user)
                {
                    $botClient->user_id = $this->user->id;
                }
            }
            else
            {
                $this->user = User::findOne($botClient->user_id);
            }

            if (isset($botClient->user_id))
            {
                if ($update->getMessage() && $location = $update->getMessage()->getLocation())
                {
                    $botClient->setAttributes([
                        'location_lon' => $location->getLongitude(),
                        'location_lat' => $location->getLatitude(),
                        'location_at' => time(),
                    ]);            
                }

                $botClient->setAttributes([
                    'provider_user_name' => $from->getUsername(),
                    'provider_user_first_name' => $from->getFirstName(),
                    'provider_user_last_name' => $from->getLastName(),
                    'provider_bot_user_blocked' => 0,
                    'last_message_at' => time(),
                ]);

                if (!$botClient->save())
                {
                    unset($botClient);
                }
            }
        }
        
        return $botClient;
    }

    public function isBotCommand($text)
    {
        return substr(trim($text), 0, 1) === '/';
    }

    /**
     * @param $update \TelegramBot\Api\Types\Update
     *
     * @return bool
     * @throws \TelegramBot\Api\Exception
     * @throws \TelegramBot\Api\InvalidArgumentException
     */
    public function dispatchRoute($update)
    {
        $result = false;

        if ($update->getMessage() && $this->isBotCommand($update->getMessage()->getText())) {
            $route = $update->getMessage()->getText();
        } elseif ($callbackQuery = $update->getCallbackQuery()) {
            $route = $callbackQuery->getData();
        } else {
            $route = $this->botClient->getState()->state;
        }

        $responses = $this->commandRouter->dispatchRoute($route);
        if (is_array($responses))
        {
            $chatId = $this->chat->getId();
            foreach ($responses as $response) {
                $response = (object)$response;
                $type = $response->type;
                if ($type == 'message')
                {
                    $this->botApi->sendMessage(
                        $chatId,
                        $this->prepareText($response->text),
                        'html',
                        FALSE,
                        NULL,
                        $response->replyMarkup
                    );
                }
                elseif ($type == 'editMessage')
                {
                    $this->botApi->editMessageText(
                        $chatId,
                        $this->update->getCallbackQuery()->getMessage()->getMessageId(),
                        $this->prepareText($response->text),
                        'html',
                        FALSE,
                        $response->replyMarkup
                    );
                }
                elseif ($type == 'location')
                {
                    $this->botApi->sendLocation(
                        $chatId,
                        $response->latitude,
                        $response->longtitude,
                        NULL,
                        $response->replyMarkup
                    );
                }
                elseif ($type == 'callback')
                {
                    $this->botApi->answerCallbackQuery(
                        $this->requestId
                    );
                }
            }

            $result = true;
        }

        return $result;
    }

    /**
     * @param string $text
     *
     * @return string
     */
    public static function prepareText($text)
    {
        $text = str_replace(["\n", "\r\n"], '', $text);

        return preg_replace('/<br\W*?\/>/i', PHP_EOL, $text);
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
