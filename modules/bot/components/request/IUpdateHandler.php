<?php
namespace app\modules\bot\components\request;

use TelegramBot\Api\Types\Update;

interface IUpdateHandler
{
    public function getFrom(Update $update);
    public function getChat(Update $update);
}
