<?php

namespace app\modules\bot\components\actions\privates\wordlist;

use app\modules\bot\components\actions\BaseAction;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\helpers\MessageWithEntitiesConverter;

class ChangeFieldAction extends BaseAction
{
    /**
    * @param int|null $id ChatPhrase->id
    * @param string|null $field
    */
    public function run($id = null, $field = null)
    {
        // check allowed fields
        if (($key = array_search($field, array_column($this->buttons, 'field'))) === false) {
            return [];
        }

        $this->getState()->setInputRoute($this->createRoute($this->updateFieldActionId, [
            'id' => $id,
            'field' => $field,
        ]));

        $phrase = $this->wordModelClass::findOne($id);
        $params = isset($phrase) ? [$field . 'Markdown' => MessageWithEntitiesConverter::fromHtml($phrase->$field ?? '')] : [];

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('set-' . $field, $params),
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
