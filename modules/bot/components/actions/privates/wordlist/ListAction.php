<?php

namespace app\modules\bot\components\actions\privates\wordlist;

use app\modules\bot\components\actions\BaseAction;
use app\modules\bot\models\Chat;
use app\modules\bot\components\helpers\PaginationButtons;
use yii\data\Pagination;

class ListAction extends BaseAction
{
    public $wordModelClass;
    public $modelAttributes = [];
    public $pageWordsCount = 9;
    public $viewActionId = 'w-v';
    public $enterActionId = 'w-e';

    /**
    * @return array
    */
    public function run($chatId = null, $page = 1)
    {
        $chat = Chat::findOne($chatId);

        if (!isset($chat)) {
            return [];
        }

        $this->getState()->setName(null);
        $phraseQuery = $this->wordModelClass::find()->where($this->modelAttributes);

        $pagination = new Pagination([
                'totalCount' => $phraseQuery->count(),
                'pageSize' => $this->pageWordsCount,
                'params' => [
                    'page' => $page,
                ],
            ]);

        $pagination->pageSizeParam = false;
        $pagination->validatePage = true;

        $chatTitle = $chat->title;
        $phrases = $phraseQuery->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();

        $paginationButtons = PaginationButtons::build($pagination, function ($page) use ($chatId) {
            return $this->createRoute($this->id, [
                    'chatId' => $chatId,
                    'page' => $page,
                ]);
        });
        $buttons = [];

        if ($phrases) {
            foreach ($phrases as $phrase) {
                $buttons[][] = [
                        'callback_data' => $this->createRoute($this->viewActionId, [
                            'phraseId' => $phrase->id,
                        ]),
                        'text' => $phrase->text
                    ];
            }

            if ($paginationButtons) {
                $buttons[] = $paginationButtons;
            }
        }

        $buttons[] = [
                [
                    'callback_data' => $this->createRoute('index', [
                        'chatId' => $chatId,
                    ]),
                    'text' => '🔙',
                ],
                [
                    'callback_data' => $this->createRoute($this->enterActionId, [
                        'chatId' => $chatId,
                    ]),
                    'text' => '➕',
                ],
            ];

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render($this->id, compact('chatTitle')),
                $buttons
            )
            ->build();
    }
}
