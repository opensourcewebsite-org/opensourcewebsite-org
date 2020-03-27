<?php

namespace app\modules\bot\components;

use app\modules\bot\components\helpers\MessageText;

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

    protected function getTelegramUser()
    {
        return $this->module->telegramUser;
    }

    protected function getTelegramChat()
    {
        return $this->module->telegramChat;
    }

    protected function getUser()
    {
        return $this->module->user;
    }

    protected function getUpdate()
    {
        return $this->module->update;
    }

    protected function getState()
    {
        return $this->module->userState;
    }

    protected function getBotName()
    {
        return $this->module->getBotName();
    }

    protected function getBotApi()
    {
        return $this->module->getBotApi();
    }

    /**
     * @param string $actionName
     * @param array $params
     * @return string
     */
    protected static function createRoute(string $actionName = '', array $params = [])
    {
        $controllerName = self::controllerName();
        $route = "/$controllerName";
        if (!empty($actionName)) {
            $route .= "_$actionName";
        }
        $params = array_filter($params);
        if (!empty($params)) {
            $paramsString = implode($params);
            $route .= " $paramsString";
        }
        return $route;
    }

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
        if ($this->textFormat == 'html')
        {
            $text = str_replace(["\n", "\r\n"], '', $text);
            $text = preg_replace('/<br\W*?\/>/i', PHP_EOL, $text);
        }
        return new MessageText($text, $this->textFormat);
    }
}
