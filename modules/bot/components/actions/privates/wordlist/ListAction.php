<?php

namespace app\modules\bot\components\actions\privates\wordlist;

use app\modules\bot\components\actions\BaseAction;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\helpers\PaginationButtons;
use app\modules\bot\models\Chat;
use Yii;
use yii\data\Pagination;

class ListAction extends BaseAction
{
    public $paginationPageSize = 9;

    /**
    * @param int|null $chatId Chat->id
    * @return array
    */
    public function run($chatId = null, $page = 1)
    {
        $chat = Chat::findOne($chatId);

        if (!isset($chat)) {
            return [];
        }

        $this->getState()->setName(null);

        $query = $this->wordModelClass::find()
            ->where(array_merge($this->modelAttributes, [
                'chat_id' => $chat->id,
            ]))
            ->orderBy(['text' => SORT_ASC]);

        $pagination = new Pagination([
            'totalCount' => $query->count(),
            'pageSize' => $this->paginationPageSize,
            'params' => [
                'page' => $page,
            ],
            'pageSizeParam' => false,
            'validatePage' => true,
        ]);

        $phrases = $query->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();

        $paginationButtons = PaginationButtons::build($pagination, function ($page) use ($chat) {
            return $this->createRoute($this->id, [
                    'chatId' => $chat->id,
                    'page' => $page,
                ]);
        });

        $buttons = [];

        $chatMember = $chat->getChatMemberByUserId();

        if ($phrases) {
            foreach ($phrases as $phrase) {
                if ($this->options['actions']['select']) {
                    if ($chatMember) {
                        $buttons[][] = [
                            'callback_data' => $this->createRoute($this->selectActionId, [
                                'id' => $phrase->id,
                                'page' => $page,
                            ]),
                            'text' => ($chatMember->hasPhrase($phrase) ? Emoji::STATUS_ON : Emoji::STATUS_OFF) . ' ' . $phrase->text,
                        ];
                    }
                } else {
                    $buttons[][] = [
                        'callback_data' => $this->createRoute($this->viewActionId, [
                            'id' => $phrase->id,
                        ]),
                        'text' => $phrase->text,
                    ];
                }
            }

            if ($paginationButtons) {
                $buttons[] = $paginationButtons;
            }
        }

        $buttons[] = [
            [
                // TODO $id $chatId
                'callback_data' => $this->createRoute($this->options['listBackRoute'], [
                    'id' => $chatId,
                    'chatId' => $chatId,
                ]),
                'text' => Emoji::BACK,
            ],
            [
                'callback_data' => $this->createRoute($this->viewListActionId, [
                    'chatId' => $chatId,
                ]),
                'text' => Emoji::LIST,
                'visible' => !empty($phrases),
            ],
            [
                'callback_data' => $this->createRoute($this->enterActionId, [
                    'chatId' => $chatId,
                ]),
                'text' => Emoji::ADD,
                'visible' => $this->options['actions']['insert'],
            ],
        ];

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render($this->id, [
                    'chat' => $chat,
                ]),
                $buttons
            )
            ->build();
    }
}
