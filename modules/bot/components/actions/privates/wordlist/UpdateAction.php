<?php

namespace app\modules\bot\components\actions\privates\wordlist;

use app\modules\bot\components\actions\BaseAction;
use app\modules\bot\models\Chat;

class UpdateAction extends BaseAction
{
    public $wordModelClass;
    public $viewActionId = 'w-v';

    public function run($phraseId = null)
    {
        $update = $this->getUpdate();

        $phrase = $this->wordModelClass::findOne($phraseId);

        $text = $update->getMessage()->getText();

        if (!$this->wordModelClass::find()->where([
            'chat_id' => $phrase->chat_id,
            'text' => $text,
        ])->exists()) {
            $phrase->text = $text;
            $phrase->save();

            return $this->controller->run($this->viewActionId, ['phraseId' => $phraseId]);
        }
    }
}
