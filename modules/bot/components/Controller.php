<?php

namespace app\modules\bot\components;

use app\models\User;
use app\modules\bot\components\helpers\MessageText;
use app\modules\bot\models\Chat;
use app\modules\bot\models\UserState;
use TelegramBot\Api\BotApi;
use app\modules\bot\components\api\Types\Update;
use app\modules\bot\components\response\ResponseBuilder;

/**
 * Class Controller
 *
 * @package app\modules\bot
 */
class Controller extends \yii\web\Controller
{
    // Postfix 's' must be present because of php-keywords (such as 'private')
    public const TYPE_PUBLIC = 'publics';
    public const TYPE_PRIVATE = 'privates';

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
    protected function getTelegramUser()
    {
        return $this->module->telegramUser;
    }

    /**
     * @return Chat
     */
    protected function getTelegramChat()
    {
        return $this->module->telegramChat;
    }

    /**
     * @return User
     */
    protected function getUser()
    {
        return $this->module->user;
    }

    /**
     * @return Update
     */
    protected function getUpdate()
    {
        return $this->module->update;
    }

    /**
     * @return ResponseBuilder
     */
    protected function getResponseBuilder()
    {
        return ResponseBuilder::fromUpdate($this->getUpdate());
    }

    /**
     * @return TelegramBot\Api\Types\Message
     */
    protected function getMessage()
    {
        $update = $this->getUpdate();
        $message = $update->getMessage();
        $message = $message ?? $update->getEditedMessage();

        $callbackQuery = $update->getCallbackQuery();
        if (!isset($message)) {
            $message = $callbackQuery->getMessage();
        }

        return $message;
    }

    /**
     * @return UserState
     */
    protected function getState()
    {
        return $this->module->userState;
    }

    /**
     * @return string
     */
    protected function getBotName()
    {
        return $this->module->getBotName();
    }

    /**
     * @return BotApi
     */
    protected function getBotApi()
    {
        return $this->module->getBotApi();
    }

    /**
     * @param string $actionName
     * @param array $params
     * @return string
     */
    public static function createRoute(string $actionName = 'index', array $params = [])
    {
        $controllerName = self::controllerName();
        $route = "/$controllerName";
        if (empty($actionName)) {
            $actionName = 'index';
        }
        $actionName = str_replace('-', '_', $actionName);
        $route .= "__$actionName";
        $params = array_filter($params);
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
