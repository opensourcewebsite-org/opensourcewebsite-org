<?php

namespace app\modules\bot\components\request;

use TelegramBot\Api\Types\Update;

interface ICommandResolver
{
    public function resolveCommand(Update $update);
}
