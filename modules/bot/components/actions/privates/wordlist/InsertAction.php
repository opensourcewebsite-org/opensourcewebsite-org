<?php

namespace app\modules\bot\components\actions\privates\wordlist;

use app\modules\bot\components\actions\BaseAction;
use app\modules\bot\models\Chat;

class InsertAction extends BaseAction
{
    public $wordModelClass;
    public $listActionId = 'w-l';
    public $modelAttributes = [];

    public function run($chatId = null)
    {
        $update = $this->getUpdate();
        $text = $update->getMessage()->getText();
        $rows = explode(PHP_EOL, $text);
        foreach ($rows as $row) {
            if ($row) {
                if (!$this->wordModelClass::find()->where(array_merge($this->modelAttributes, ['chat_id' => $chatId, 'text' => $row]))->exists()) {
                    $phrase = new $this->wordModelClass();
                    $phrase->setAttributes(array_merge($this->modelAttributes, [
                                        'chat_id' => $chatId,
                                        'text' => $row,
                                        'created_by' => $this->getTelegramUser()->id,
                                    ]));

                    $phrase->save();
                }
            }
        }

        return $this->controller->runAction($this->listActionId, [
            'chatId' => $chatId,
        ]);
    }
}
