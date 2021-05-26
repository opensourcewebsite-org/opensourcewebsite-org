<?php

namespace app\modules\bot\components\actions\privates\wordlist;

use app\modules\bot\components\actions\BaseAction;
use app\modules\bot\models\Chat;
use app\modules\bot\components\helpers\Emoji;

class ChangeFieldAction extends BaseAction
{
    public function run($phraseId = null, $field = null)
    {
        $this->getState()->setName($this->createRoute($this->updateFieldActionId, [
            'phraseId' => $phraseId,
            'field' => $field,
        ]));

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('set-' . $field),
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
