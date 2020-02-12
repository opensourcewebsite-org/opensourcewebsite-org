<?php

namespace app\modules\bot;

use Yii;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Types\Update;
use app\modules\bot\models\Bot;
use app\modules\bot\models\BotClient;
use yii\base\InvalidRouteException;
use app\models\User;
use app\models\Language;
use app\models\Rating;
use app\modules\bot\components\ReplyKeyboardManager;
use app\modules\bot\components\response\SendMessageCommand;
use TelegramBot\Api\Types\ReplyKeyboardMarkup;
use TelegramBot\Api\Types\ReplyKeyboardRemove;

/**
 * OSW Bot module definition class
 * @link https://t.me/opensourcewebsite_bot
 */
class Module extends \yii\base\Module
{
    /**
     * @var \TelegramBot\Api\BotApi
     */
    private $botApi;

    /**
     * @var models\BotClient
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
        $updateArray = json_decode($input, true);
        $this->update = Update::fromResponse($updateArray);
        $botInfo = Bot::findOne(['token' => $token]);
        if ($botInfo) {
            $this->botApi = new BotApi($botInfo->token);

            if (isset(Yii::$app->params['telegramProxy'])) {
                $this->botApi->setProxy(Yii::$app->params['telegramProxy']);
            }

            $this->botClient = $this->resolveBotClient($this->update, $botInfo->id);
            if (isset($this->botClient)) {
                Yii::$app->language = $this->botClient->language_code;

                $result = $this->dispatchRoute($this->update);
            } else {
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
    private function resolveBotClient($update, $botId)
    {
        foreach ($this->commandRouteResolver->requestHandlers as $requestHandler) {
            $from = $requestHandler->getFrom($update);
            if (isset($from)) {
                break;
            }
        }

        if ($from) {
            $botClient = BotClient::findOne([
                'bot_id' => $botId,
                'provider_user_id' => $from->getId(),
            ]);
            if (!isset($botClient)) {
                $botClient = new BotClient();

                $existingBotClient = BotClient::findOne([
                    'provider_user_id' => $from->getId(),
                ]);

                if (isset($existingBotClient)) {
                    $botClient->setAttributes($existingBotClient->attributes);
                    $botClient->state = null;
                } else {
                    $language = Language::findOne([
                        'code' => $from->getLanguageCode(),
                    ]);
                    $language = isset($language) ? $language->code : 'en';

                    $botClient->setAttributes([
                        'bot_id' => $botId,
                        'provider_user_id' => $from->getId(),
                        'language_code' => $language,
                    ]);
                }
            }

            if (!isset($botClient->user_id)) {
                $this->user = User::createWithRandomPassword();
                $this->user->name = $from->getFirstName() . ' ' . $from->getLastName();
                if ($this->user->save()) {
                    $botClient->user_id = $this->user->id;

                    $this->user->addRating(Rating::USE_TELEGRAM_BOT, 1, false);
                }
            } else {
                $this->user = User::findOne($botClient->user_id);
            }

            $botClient->setAttributes([
                'provider_user_name' => $from->getUsername(),
                'provider_user_first_name' => $from->getFirstName(),
                'provider_user_last_name' => $from->getLastName(),
                'provider_bot_user_blocked' => 0,
                'last_message_at' => time(),
            ]);

            if (!isset($botClient->user_id) || !isset($this->user) || !$botClient->save()) {
                unset($botClient);
            }

            if (isset($botClient)) {
                $keyboardButtons = $botClient->getState()->getKeyboardButtons();
                ReplyKeyboardManager::init($keyboardButtons);
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
        if ($route) {
            $commands = $this->runAction($route, $params);

            if (is_array($commands)) {
                foreach ($commands as $command) {
                    try {
                        $replyMarkup = $command->replyMarkup;
                        if (ReplyKeyboardManager::getInstance()->isChanged()
                            && $command instanceof SendMessageCommand
                            && !isset($replyMarkup)) {
                            $this->setReplyKeyboard($command);

                            $keyboardButtons = ReplyKeyboardManager::getInstance()->getKeyboardButtons();
                            $this->botClient->getState()->setKeyboardButtons($keyboardButtons);
                            $this->botClient->save();
                        }
                        $command->send($this->botApi);
                    } catch (\Exception $ex) {
                        Yii::error($ex->getCode() . ': ' . $ex->getMessage(), 'bot');
                    }
                }

                $result = true;
            }
        }

        return $result;
    }

    private function setReplyKeyboard(&$command)
    {
        $keyboardButtons = ReplyKeyboardManager::getInstance()->getKeyboardButtons();
        $command->replyMarkup = (!empty($keyboardButtons))
            ? new ReplyKeyboardMarkup($keyboardButtons, false, true)
            : new ReplyKeyboardRemove();
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
            $oldController = Yii::$app->controller;
            Yii::$app->controller = $controller;
            $result = $controller->runAction($actionID, $params, true);
            if ($oldController !== null) {
                Yii::$app->controller = $oldController;
            }

            return $result;
        }

        $id = $this->getUniqueId();
        throw new InvalidRouteException('Unable to resolve the request "' . ($id === '' ? $route
                : $id . '/' . $route) . '".');
    }
}
