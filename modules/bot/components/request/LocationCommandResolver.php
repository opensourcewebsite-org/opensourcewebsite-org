<?php
namespace app\modules\bot\components\request;

use app\modules\bot\controllers\privates\MyLocationController;
use TelegramBot\Api\Types\Update;

class LocationCommandResolver implements ICommandResolver
{
    public function resolveCommand(Update $update)
    {
        if (($message = $update->getMessage()) && $message->getLocation()) {
            $commandText = MyLocationController::createRoute('update');
        }

        return $commandText ?? null;
    }
}
