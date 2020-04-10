<?php

namespace app\modules\bot\controllers\privates;

use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\response\ResponseBuilder;
use app\modules\bot\components\Controller;
use app\modules\bot\models\Chat;
use app\modules\bot\models\Phrase;
use app\modules\bot\components\helpers\PaginationButtons;

/**
 * Class AdminMessageFilterWhitelistController
 *
 * @package app\controllers\bot
 */
class AdminMessageFilterWhitelistController extends Controller
{
    /**
     * @return array
     */
    public function actionIndex($chatId = null, $page = 1)
    {
        $chat = Chat::findOne($chatId);
        if (!isset($chat)) {
            return ResponseBuilder::fromUpdate($this->getUpdate())
                ->answerCallbackQuery()
                ->build();
        }

        $this->getState()->setName(null);

        $chatTitle = $chat->title;

        $phraseButtons = PaginationButtons::buildFromQuery(
            $chat->getWhitelistPhrases(),
            function ($page) use ($chatId) {
                return self::createRoute('index',
                    [
                        'chatId' => $chatId,
                        'page' => $page,
                    ]);
            },
            function (Phrase $phrase)
            {
                return [
                    'callback_data' => AdminMessageFilterPhraseController::createRoute('index', [
                        'phraseId' => $phrase->id,
                    ]),
                    'text' => $phrase->text
                ];
            },
            $page
        );

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render('index', compact('chatTitle')),
               array_merge($phraseButtons,  [
                   [
                       'callback_data' => AdminMessageFilterController::createRoute('index', [
                           'chatId' => $chatId,
                       ]),
                       'text' => Emoji::BACK,
                   ],
                   [
                       'callback_data' => AdminMessageFilterNewphraseController::createRoute('index', [
                           'type' => Phrase::TYPE_WHITELIST,
                           'chatId' => $chatId,
                       ]),
                       'text' => Emoji::ADD,
                   ],
               ])
            )
            ->build();
    }
}
