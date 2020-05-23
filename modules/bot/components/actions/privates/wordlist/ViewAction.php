<?php

namespace app\modules\bot\components\actions\privates\wordlist;

use app\modules\bot\components\actions\BaseAction;
use app\modules\bot\models\Chat;
use app\modules\bot\components\helpers\PaginationButtons;
use yii\data\Pagination;

class ViewAction extends BaseAction
{
    public $wordModelClass;
    public $listActionId = 'w-l';
    public $changeActionId = 'w-c';
    public $deleteActionId = 'w-d';

    /**
    * @return array
    */
    public function run($phraseId = null)
    {
        $this->getState()->setName(null);

        $phrase = $this->wordModelClass::findOne($phraseId);

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render($this->id, compact('phrase')),
                [
                    [
                        [
                            'callback_data' => $this->createRoute($this->listActionId, [
                                'chatId' => $phrase->chat_id,
                            ]),
                            'text' => '🔙',
                        ],
                        [
                            'callback_data' => $this->createRoute($this->changeActionId, [
                                'phraseId' => $phraseId,
                            ]),
                            'text' => '✏️',
                        ],
                        [
                            'callback_data' => $this->createRoute($this->deleteActionId, [
                                'phraseId' => $phraseId,
                            ]),
                            'text' => '🗑',
                        ],
                    ],
                ]
            )
            ->build();
    }
}
