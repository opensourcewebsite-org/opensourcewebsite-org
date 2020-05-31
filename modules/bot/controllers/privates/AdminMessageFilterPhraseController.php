<?php

namespace app\modules\bot\controllers\privates;

use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\response\ResponseBuilder;
use app\modules\bot\components\Controller;
use app\modules\bot\models\Phrase;

/**
 * Class AdminMessageFilterPhraseController
 *
 * @package app\controllers\bot
 */
class AdminMessageFilterPhraseController extends Controller
{
    /**
     * @return array
     */
    public function actionIndex($phraseId = null)
    {
        $this->getState()->setName(null);

        $phrase = Phrase::findOne($phraseId);
        if (isset($phrase)) {
            return ResponseBuilder::fromUpdate($this->getUpdate())
                ->answerCallbackQuery()
                ->build();
        }

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render('index', compact('phrase')),
                [
                    [
                        [
                            'callback_data' => $phrase->isTypeBlack()
                                ? AdminMessageFilterBlacklistController::createRoute('index', [
                                    'chatId' => $phrase->chat_id,
                                ])
                                : AdminMessageFilterWhitelistController::createRoute('index', [
                                    'chatId' => $phrase->chat_id,
                                ]),
                            'text' => Emoji::BACK,
                        ],
                        [
                            'callback_data' => AdminMessageFilterPhraseController::createRoute('create', [
                                'phraseId' => $phraseId,
                            ]),
                            'text' => Emoji::EDIT,
                        ],
                        [
                            'callback_data' => AdminMessageFilterPhraseController::createRoute('delete', [
                                'phraseId' => $phraseId,
                            ]),
                            'text' => Emoji::DELETE,
                        ],
                    ],
                ]
            )
            ->build();
    }

    public function actionDelete($phraseId = null)
    {
        $phrase = Phrase::findOne($phraseId);
        if (isset($phrase)) {
            return ResponseBuilder::fromUpdate($this->getUpdate())
                ->answerCallbackQuery()
                ->build();
        }

        $chatId = $phrase->chat_id;

        $isTypeBlack = $phrase->isTypeBlack();
        $phrase->delete();

        $update = $this->getUpdate();
        $update->getCallbackQuery()->setData($isTypeBlack
            ? AdminMessageFilterBlacklistController::createRoute('index', [
                'chatId' => $chatId,
            ])
            : AdminMessageFilterWhitelistController::createRoute('index', [
                'chatId' => $chatId,
            ]));

        $this->module->dispatchRoute($update);
    }

    public function actionCreate($phraseId = null)
    {
        $this->getState()->setName(AdminMessageFilterPhraseController::createRoute('update', [
            'phraseId' => $phraseId,
        ]));

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render('create'),
                [
                    [
                        [
                            'callback_data' => AdminMessageFilterPhraseController::createRoute('index', [
                                'phraseId' => $phraseId,
                            ]),
                            'text' => Emoji::BACK,
                        ],
                    ],
                ]
            )
            ->build();
    }

    public function actionUpdate($phraseId = null)
    {
        $phrase = Phrase::findOne($phraseId);
        if (isset($phrase)) {
            return ResponseBuilder::fromUpdate($this->getUpdate())
                ->answerCallbackQuery()
                ->build();
        }

        $phrase = Phrase::findOne($phraseId);

        $text = $this->getUpdate()->getMessage()->getText();

        if (!Phrase::find()->where([
            'chat_id' => $phrase->chat_id,
            'text' => $text,
            'type' => $phrase->type
        ])->exists()) {
            $phrase->text = $text;
            $phrase->save();

            return $this->actionIndex($phraseId);
        }

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->answerCallbackQuery()
            ->build();
    }
}
