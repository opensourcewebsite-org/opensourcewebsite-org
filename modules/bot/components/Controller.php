<?php

namespace app\modules\bot\components;

use app\models\User as GlobalUser;
use app\modules\bot\components\api\Types\Update;
use app\modules\bot\components\helpers\MessageText;
use app\modules\bot\components\response\ResponseBuilder;
use app\modules\bot\models\Chat;
use app\modules\bot\models\UserState;
use TelegramBot\Api\HttpException;
use Yii;

/**
 * Class Controller
 *
 * @package app\modules\bot
 *
 * @property GlobalUser $globalUser
 * @property Update $update
 */
class Controller extends \yii\web\Controller
{
    /**
     * @var bool
     */
    public $layout = false;

    /**
     * @var bool
     */
    public $enableCsrfValidation = false;

    /**
     * @var string
     */
    protected $textFormat = 'html';

    /**
     * @param string $view
     * @param array $params
     * @return MessageText Instance of MessageText class that is used for sending Telegram commands
     */
    public function render($view, $params = [])
    {
        return $this->prepareMessageText(parent::render($view, $params));
    }

    /**
     * @return \app\modules\bot\models\User
     */
    public function getTelegramUser()
    {
        return $this->module->getUser();
    }

    /**
     * @return \app\modules\bot\models\Chat
     */
    public function getTelegramChat()
    {
        return $this->module->getChat();
    }

    /**
     * @return \app\modules\bot\models\Chat
     */
    public function getChat()
    {
        return $this->module->getChat();
    }

    /**
     * @return GlobalUser
     */
    public function getUser()
    {
        return $this->module->getGlobalUser();
    }

    /**
     * @return GlobalUser
     */
    public function getGlobalUser()
    {
        return $this->module->getGlobalUser();
    }

    /**
     * @return Update
     */
    public function getUpdate()
    {
        return $this->module->getUpdate();
    }

    /**
     * @return ResponseBuilder
     */
    public function getResponseBuilder()
    {
        return new ResponseBuilder();
    }

    // TODO refactoring, maybe remove
    /**
     * @return \TelegramBot\Api\Types\Message
     */
    public function getMessage()
    {
        return $this->update->requestMessage;
    }

    /**
     * @return \app\modules\bot\models\UserState
     */
    public function getState()
    {
        return $this->module->getUserState();
    }

    /**
     * @return \app\modules\bot\models\Bot
     */
    public function getBot()
    {
        return $this->module->getBot();
    }

    /**
     * @return \app\modules\bot\components\api\BotApi
     */
    public function getBotApi()
    {
        return $this->module->getBotApi();
    }

    /**
     * @param string $actionName
     * @param array $params
     * @return string
     */
    public static function createRoute(string $actionName = null, array $params = [])
    {
        $module =  Yii::$app->getModule('bot');
        $controllerName = self::controllerName();

        // replace names of actions to short codes
        $key = array_search($controllerName, $module->commandRouteResolver->controllers);

        if ($key !== false) {
            $route = "/$key";
        } else {
            $route = "/$controllerName";

            Yii::warning('Controller: ' . $controllerName);
        }

        if (!empty($actionName)) {
            $actionName = str_replace('-', '_', $actionName);
            // replace names of actions to short codes
            $key = array_search($actionName, $module->commandRouteResolver->actions);

            if ($key !== false) {
                $route .= "__$key";
            } else {
                $route .= "__$actionName";

                Yii::warning('Action: ' . $actionName);
            }
        }

        if (!empty($params)) {
            $paramsString = http_build_query($params);
            $route .= "?$paramsString";
        }

        return $route;
    }

    /**
     * @return string
     */
    private static function controllerName()
    {
        $className = static::class;
        $parts = explode('\\', $className);
        $className = array_pop($parts);
        $parts = preg_split('/(?=[A-Z])/', $className, -1, PREG_SPLIT_NO_EMPTY);
        array_pop($parts);
        $controllerName = strtolower(implode('_', $parts));

        return $controllerName;
    }

    /**
     * @param $text string Text to format
     * @return MessageText Instance of MessageText class that is used for sending Telegram commands
     */
    private function prepareMessageText($text)
    {
        if ($this->textFormat == 'html') {
            $text = str_replace(["\n", "\r\n"], '', $text);
            $text = preg_replace('/<br\W*?\/>/i', PHP_EOL, $text);
        }

        return new MessageText($text, $this->textFormat);
    }
}
