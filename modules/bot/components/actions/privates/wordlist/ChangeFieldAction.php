<?php

namespace app\modules\bot\components\actions\privates\wordlist;

use app\modules\bot\components\actions\BaseAction;
use app\modules\bot\components\helpers\MessageWithEntitiesConverter;
use app\modules\bot\components\helpers\Emoji;

class ChangeFieldAction extends BaseAction
{
    public function run($phraseId = null, $field = null)
    {
        // check allowed fields
        if (($key = array_search($field, array_column($this->buttons, 'field'))) === false) {
            return [];
        }

        $this->getState()->setName($this->createRoute($this->updateFieldActionId, [
            'phraseId' => $phraseId,
            'field' => $field,
        ]));

        $phrase = $this->wordModelClass::findOne($phraseId);
        $params = isset($phrase) ? [$field . 'Markdown' => MessageWithEntitiesConverter::fromHtml($phrase->$field)] : [];

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('set-' . $field, $params),
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
