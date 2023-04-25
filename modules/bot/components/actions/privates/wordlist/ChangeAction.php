<?php

namespace app\modules\bot\components\actions\privates\wordlist;

use app\modules\bot\components\actions\BaseAction;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\models\Chat;

class ChangeAction extends BaseAction
{
    /**
    * @param int|null $id ChatPhrase->id
    */
    public function run($id = null)
    {
        $phrase = $this->wordModelClass::findOne($id);

        $this->getState()->setInputRoute($this->createRoute($this->updateActionId, [
            'id' => $id,
        ]));

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render($this->id),
                [
                    [
                        [
                            'callback_data' => $this->createRoute($this->viewActionId, [
                                'id' => $phrase->id,
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
