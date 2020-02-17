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
