<?php

namespace app\modules\bot\components\response\commands;

use app\modules\bot\components\api\BotApi;
use Yii;

/**
 * Class Command
 *
 * @package app\modules\bot\components\response\commands
 */
abstract class Command
{
    private $fields = [];

    private $messageId;

    protected function __construct(array $array = [])
    {
        foreach ($array as $key => $value) {
            $this->{$key} = $value;
        }
    }

    public function __get(string $name)
    {
        return $this->fields[$name] ?? null;
    }

    public function __set(string $name, $value)
    {
        $this->fields[$name] = $value;
    }

    // TODO temporarily
    //abstract public function send();

    protected function getOptionalProperty($name, $defaultValue)
    {
        return $this->{$name} ?? $defaultValue;
    }

    protected function setMessageId(int $id)
    {
        $this->messageId = $id;
    }

    public function getMessageId()
    {
        return $this->messageId;
    }

    /**
     * @return Bot|null
     */
    public function getBot()
    {
        if (Yii::$container->hasSingleton('bot')) {
            return Yii::$container->get('bot');
        }

        return null;
    }

    /**
     * @return BotApi|null
     */
    public function getBotApi()
    {
        if ($bot = $this->getBot()) {
            return $bot->getBotApi();
        }

        return null;
    }

    // TODO temporarily
    public function build()
    {
        $this->send();
    }

    abstract public function send();
}
