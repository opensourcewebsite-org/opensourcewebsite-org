<?php

namespace app\modules\bot\components\actions\privates\wordlist;

use app\modules\bot\components\actions\BaseAction;

class DeleteAction extends BaseAction
{
    /**
    * @param int|null $id ChatPhrase->id
    * @return array
    */
    public function run($id = null)
    {
        $phrase = $this->wordModelClass::findOne($id);

        $chatId = $phrase->getChatId();
        $phrase->delete();

        return $this->controller->run($this->listActionId, ['chatId' => $chatId]);
    }
}
