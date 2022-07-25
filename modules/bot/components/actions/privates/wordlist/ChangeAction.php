<?php

namespace app\modules\bot\components\actions\privates\wordlist;

use app\modules\bot\components\actions\BaseAction;
use app\modules\bot\models\Chat;
use app\modules\bot\components\helpers\Emoji;

class ChangeAction extends BaseAction
{
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
                            'text' => Emoji::BACK,
                        ],
                    ],
                ],
                [
                    'disablePreview' => true,
                ]
            )
            ->build();
    }
}
