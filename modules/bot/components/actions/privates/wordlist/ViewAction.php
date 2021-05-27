<?php

namespace app\modules\bot\components\actions\privates\wordlist;

use Yii;
use app\modules\bot\components\actions\BaseAction;
use app\modules\bot\models\Chat;
use app\modules\bot\components\helpers\Emoji;

class ViewAction extends BaseAction
{
    /**
    * @return array
    */
    public function run($phraseId = null)
    {
        $this->getState()->setName(null);

        $phrase = $this->wordModelClass::findOne($phraseId);

        $buttons = [];

        if ($this->buttons) {
            foreach ($this->buttons as $button) {
                $buttons[] = [
                    [
                        'callback_data' => self::createRoute($this->changeFieldActionId, [
                            'phraseId' => $phraseId,
                            'field' => $button['field'],
                        ]),
                        'text' => $button['text'],
                    ],
                ];
            }
        }

        $buttons[] = [
            [
                'callback_data' => $this->createRoute($this->listActionId, [
                    'chatId' => $phrase->chat_id,
                ]),
                'text' => Emoji::BACK,
            ],
            [
                'callback_data' => $this->createRoute($this->changeActionId, [
                    'phraseId' => $phraseId,
                ]),
                'text' => Emoji::EDIT,
            ],
            [
                'callback_data' => $this->createRoute($this->deleteActionId, [
                    'phraseId' => $phraseId,
                ]),
                'text' => Emoji::DELETE,
            ],
        ];

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render($this->id, compact('phrase')),
                $buttons
            )
            ->build();
    }
}
