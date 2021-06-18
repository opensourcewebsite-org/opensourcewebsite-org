<?php

namespace app\modules\bot\components;

use Yii;
use app\models\User;
use app\modules\bot\components\helpers\MessageText;
use app\modules\bot\models\Chat;
use app\modules\bot\models\UserState;
use app\modules\bot\components\api\Types\Update;
use app\modules\bot\components\response\ResponseBuilder;
use TelegramBot\Api\HttpException;

/**
 * Class Controller
 *
 * @package app\modules\bot
 */
class Controller extends \yii\web\Controller
{
    public const PRIVATE_NAMESPACE = 'privates';
    public const GROUP_NAMESPACE = 'groups';
    public const CHANNEL_NAMESPACE = 'channels';

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
        return $this->module->getBotUser();
    }

    /**
     * @return Chat
     */
    protected function getTelegramChat()
    {
        return $this->module->getChat();
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
        return $this->module->getUpdate();
    }

    /**
     * @return ResponseBuilder
     */
    protected function getResponseBuilder()
    {
        return new ResponseBuilder();
    }

    // TODO refactoring, maybe remove
    /**
     * @return TelegramBot\Api\Types\Message
     */
    protected function getMessage()
    {
        return $this->update->requestMessage;
    }

    /**
     * @return UserState
     */
    protected function getState()
    {
        return $this->module->getBotUserState();
    }

    /**
     * @return Bot
     */
    protected function getBot()
    {
        return $this->module->getBot();
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
    public static function createRoute(string $actionName = null, array $params = [])
    {
        $controllerName = self::controllerName();
        $route = "/$controllerName";

        if (!empty($actionName)) {
            $actionName = str_replace('-', '_', $actionName);
            $route .= "__$actionName";
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
