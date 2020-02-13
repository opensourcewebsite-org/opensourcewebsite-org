<?php
namespace app\modules\bot\components\request;

use TelegramBot\Api\Types\Update;

interface IRequestHandler
{
    public function getFrom(Update $update);
    public function getChat(Update $update);
    public function getCommandText(Update $update);
}
