<?php

namespace app\modules\bot\components\actions\privates\wordlist;

use app\modules\bot\components\actions\BaseAction;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\models\Chat;

class ViewListAction extends BaseAction
{
    /**
     * @param int|null $chatId Chat->id
     *
     * @return array
     */
    public function run($chatId = null)
    {
        $chat = Chat::findOne($chatId);

        $phrases = $this->wordModelClass::find()
            ->where(array_merge($this->modelAttributes, [
                'chat_id' => $chat->id,
            ]))
            ->orderBy(['text' => SORT_ASC])
            ->all();

        if (!isset($chat) || !isset($phrases)) {
            return [];
        }

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render($this->id, [
                    'chat' => $chat,
                    'phrases' => $phrases,
                ]),
                [
                    [
                        [
                            'callback_data' => $this->createRoute($this->listActionId, [
                                'chatId' => $chat->id,
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
