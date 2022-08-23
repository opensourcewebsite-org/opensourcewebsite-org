<?php

namespace app\modules\bot\components\actions\privates\wordlist;

use app\modules\bot\components\actions\BaseAction;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\models\Chat;
use Yii;

class ViewAction extends BaseAction
{
    /**
    * @param int|null $id ChatPhrase->id
    * @return array
    */
    public function run($id = null)
    {
        $this->getState()->setName(null);

        $phrase = $this->wordModelClass::findOne($id);

        $buttons = [];

        if ($this->buttons) {
            foreach ($this->buttons as $button) {
                $buttons[] = [
                    [
                        'callback_data' => self::createRoute($this->changeFieldActionId, [
                            'id' => $phrase->id,
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
                    'chatId' => $phrase->getChatId(),
                ]),
                'text' => Emoji::BACK,
            ],
            [
                'callback_data' => $this->createRoute($this->changeActionId, [
                    'id' => $phrase->id,
                ]),
                'text' => Emoji::EDIT,
                'visible' => $this->options['actions']['update'],
            ],
            [
                'callback_data' => $this->createRoute($this->deleteActionId, [
                    'id' => $phrase->id,
                ]),
                'text' => Emoji::DELETE,
                'visible' => $this->options['actions']['delete'],
            ],
        ];

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render($this->id, [
                    'phrase' => $phrase,
                ]),
                $buttons,
                [
                    'disablePreview' => true,
                ]
            )
            ->build();
    }
}
