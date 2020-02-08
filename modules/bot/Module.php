<?php

namespace app\modules\bot;

use Yii;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Types\Update;
use app\modules\bot\models\Bot;
use app\modules\bot\models\BotClient;
use yii\base\InvalidRouteException;
use app\models\User;
use app\modules\bot\components\request\MessageRequestHandler;
use app\modules\bot\components\request\CallbackQueryRequestHandler;

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
        foreach ($this->commandRouteResolver->requestHandlers as $requestHandler) {
            $from = $requestHandler->getFrom($update);
            if (isset($from))
            {
                break;
            }
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
                $this->user = new User();
                $this->user->name = $from->getFirstName() . ' ' . $from->getLastName();
                $this->user->password = Yii::$app->security->generateRandomString();
                $this->user->generateAuthKey();
                if ($this->user->save())
                {
                    $botClient->user_id = $this->user->id;
                }
            }
            else
            {
                $this->user = User::findOne($botClient->user_id);
            }

            if (isset($botClient->user_id) && isset($this->user))
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

        list($route, $params) = $this->commandRouteResolver->resolveRoute($update);
        if ($route)
        {
            $commandSenders = $this->runAction($route, $params);

            if (is_array($commandSenders))
            {
                foreach ($commandSenders as $commandSender) {
                    try
                    {
                        $commandSender->sendCommand($this->botApi);
                    }
                    catch (\Exception $ex)
                    {
                        Yii::error($ex->getCode() . ': ' . $ex->getMessage(), 'bot');
                    }
                }

                $result = true;
            }
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
            /* @var $controller Controller */
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
