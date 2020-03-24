<?php

namespace app\modules\bot\components;

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

    protected $textFormat = 'html';

    /**
     * @var bool
     */
    public $layout = false;

    /**
     * @var bool
     */
    public $enableCsrfValidation = false;

    public function render($view, $params = [])
    {
        return $this->prepareText(parent::render($view, $params));
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

    protected function getBotName()
    {
        return $this->module->getBotName();
    }

    protected function getBotApi()
    {
        return $this->module->getBotApi();
    }

    protected static function createRoute(string $actionName = 'index', array $params = [])
    {
        $controllerName = self::controllerName();
        $route = "$controllerName";
        if (empty($actionName)) {
            $actionName = 'index';
        }
        $route .= "/$actionName";
        $params = array_filter($params);
        if (!empty($params)) {
            $paramsString = http_build_query($params);
            $route .= "?$paramsString";
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
     * @param string $text
     *
     * @return string
     */
    private function prepareText($text)
    {
        if ($this->textFormat == 'html')
        {
            $text = str_replace(["\n", "\r\n"], '', $text);
            $text = preg_replace('/<br\W*?\/>/i', PHP_EOL, $text);
        }
        return $text;
    }
}
