<?php
namespace app\modules\bot\components\response\commands;

use TelegramBot\Api\BotApi;

abstract class Command
{
    private $fields = [];

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

    abstract public function send(BotApi $botApi);

    protected function getOptionalProperty($name, $defaultValue)
    {
        return $this->{$name} ?? $defaultValue;
    }
}
