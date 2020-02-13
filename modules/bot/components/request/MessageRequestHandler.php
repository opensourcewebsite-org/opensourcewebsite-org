<?php
namespace app\modules\bot\components\request;

class MessageRequestHandler implements IRequestHandler
{
    public function getFrom($update)
    {
        if ($message = $update->getMessage()) {
            $from = $message->getFrom();
        }

        return $from;
    }

    public function getCommandText($update)
    {
        if ($message = $update->getMessage()) {
            $commandText = $message->getText();
        }

        return $commandText;
    }
}
