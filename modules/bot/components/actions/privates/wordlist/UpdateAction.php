<?php

namespace app\modules\bot\components\actions\privates\wordlist;

use app\modules\bot\components\actions\BaseAction;

class UpdateAction extends BaseAction
{
    /**
    * @param int|null $id ChatPhrase->id
    */
    public function run($id = null)
    {
        $phrase = $this->wordModelClass::findOne($id);

        $text = htmlspecialchars($this->getUpdate()->getMessage()->getText());

        if (!$this->wordModelClass::find()->where([
            'chat_id' => $phrase->getChatId(),
            'text' => $text,
        ])->exists()) {
            $phrase->text = $text;
            $phrase->updated_by = $this->getTelegramUser()->id;

            $phrase->save();

            return $this->controller->run($this->viewActionId, [
                'id' => $phrase->id,
            ]);
        }
        //TODO missing return
    }
}
