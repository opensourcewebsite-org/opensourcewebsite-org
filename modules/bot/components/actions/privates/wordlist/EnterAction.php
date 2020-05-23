<?php

namespace app\modules\bot\components\actions\privates\wordlist;

use app\modules\bot\components\actions\BaseAction;
use app\modules\bot\models\Chat;

class EnterAction extends BaseAction
{
    public $wordModelClass;
    public $insertActionId = 'w-i';
    public $listActionId = 'w-l';

    /**
    * @return array
    */
    public function run($chatId)
    {
        $this->getState()->setName($this->createRoute( $this->insertActionId, [
            'chatId' => $chatId
        ]));

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render($this->id),
                [
                    [
                        [
                            'callback_data' => $this->createRoute($this->listActionId, [
                                'chatId' => $chatId,
                            ]),
                            'text' => '🔙',
                        ],
                    ],
                ]
            )
            ->build();
    }
}
