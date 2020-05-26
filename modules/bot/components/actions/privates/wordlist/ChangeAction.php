<?php

namespace app\modules\bot\components\actions\privates\wordlist;

use app\modules\bot\components\actions\BaseAction;
use app\modules\bot\models\Chat;

class ChangeAction extends BaseAction
{
    public $wordModelClass;
    public $updateActionId = 'w-u';
    public $viewActionId = 'w-v';

    public function run($phraseId = null)
    {
        $this->getState()->setName($this->createRoute($this->updateActionId, [
            'phraseId' => $phraseId,
        ]));

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render($this->id),
                [
                    [
                        [
                            'callback_data' => $this->createRoute($this->viewActionId, [
                                'phraseId' => $phraseId,
                            ]),
                            'text' => '🔙',
                        ],
                    ],
                ]
            )
            ->build();
    }
}
