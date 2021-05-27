<?php

namespace app\modules\bot\components\actions\privates\wordlist;

use app\modules\bot\components\actions\BaseAction;
use app\modules\bot\models\Chat;

class UpdateFieldAction extends BaseAction
{
    public function run($phraseId = null, $field = null)
    {
        // check allowed fields
        if (($key = array_search($field, array_column($this->buttons, 'field'))) === false) {
            return [];
        }

        $phrase = $this->wordModelClass::findOne($phraseId);
        $text = $this->getUpdate()->getMessage()->getText();

        $phrase->$field = $text;
        $phrase->updated_by = $this->getTelegramUser()->id;

        $phrase->save();

        return $this->controller->run($this->viewActionId, [
            'phraseId' => $phraseId,
        ]);
    }
}
