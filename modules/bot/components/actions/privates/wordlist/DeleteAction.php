<?php

namespace app\modules\bot\components\actions\privates\wordlist;

use app\modules\bot\components\actions\BaseAction;
use app\modules\bot\models\Chat;

class DeleteAction extends BaseAction
{
    public $wordModelClass;
    public $listActionId = 'w-l';

    public function run($phraseId = null)
    {
        $phrase = $this->wordModelClass::findOne($phraseId);

        $chatId = $phrase->chat_id;
        $phrase->delete();

        return $this->controller->run($this->listActionId, ['chatId' => $chatId]);
    }
}
