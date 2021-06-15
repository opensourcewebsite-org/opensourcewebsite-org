<?php

namespace app\modules\bot\components\actions\privates\wordlist;

use app\modules\bot\components\actions\BaseAction;

class UpdateAction extends BaseAction
{
    public function run($phraseId = null)
    {
        $phrase = $this->wordModelClass::findOne($phraseId);
        $text = htmlspecialchars($this->getUpdate()->getMessage()->getText());

        if (!$this->wordModelClass::find()->where([
            'chat_id' => $phrase->chat_id,
            'text' => $text,
        ])->exists()) {
            $phrase->text = $text;
            $phrase->updated_by = $this->getTelegramUser()->id;

            $phrase->save();

            return $this->controller->run($this->viewActionId, [
                'phraseId' => $phraseId,
            ]);
        }
        //TODO missing return
    }
}
