<?php

namespace app\modules\bot\components\helpers;

/**
 * Class MessageText
 * @package app\modules\bot\components
 */
class MessageText
{
    /**
     * @var string
     */
    private $text;

    /**
     * @var string|null
     */
    private $parseMode;

    /**
     * MessageText constructor.
     * @param string $text
     * @param string|null $parseMode
     */
    public function __construct(string $text, string $parseMode = null)
    {
        $this->text = $text;
        $this->parseMode = $parseMode;
    }

    /**
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @return string
     */
    public function getParseMode()
    {
        return $this->parseMode;
    }
}
