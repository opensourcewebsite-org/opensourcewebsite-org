<?php

namespace app\modules\bot\controllers\privates;

use Yii;
use \app\modules\bot\components\response\commands\SendMessageCommand;
use \app\modules\bot\components\response\commands\EditMessageTextCommand;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use app\modules\bot\components\Controller as Controller;
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

        if ($this->getUpdate()->getCallbackQuery()) {
            return [
                new EditMessageTextCommand(
                    $this->getTelegramChat()->chat_id,
                    $this->getUpdate()->getCallbackQuery()->getMessage()->getMessageId(),
                    $this->render('index', compact('phrase')),
                    [
                        'parseMode' => $this->textFormat,
                        'replyMarkup' => new InlineKeyboardMarkup([
                            [
                                [
                                    'callback_data' => $phrase->isTypeBlack()
                                        ? AdminMessageFilterBlacklistController::createRoute('index', [
                                            'chatId' => $phrase->chat_id,
                                        ])
                                        : AdminMessageFilterWhitelistController::createRoute('index', [
                                            'chatId' => $phrase->chat_id,
                                        ]),
                                    'text' => '🔙',
                                ],
                                [
                                    'callback_data' => AdminMessageFilterPhraseController::createRoute('create', [
                                        'phraseId' => $phraseId,
                                    ]),
                                    'text' => '✏️',
                                ],
                                [
                                    'callback_data' => AdminMessageFilterPhraseController::createRoute('delete', [
                                        'phraseId' => $phraseId,
                                    ]),
                                    'text' => '🗑',
                                ],
                            ],
                        ]),
                    ]
                ),
            ];
        } else {
            return [
                new SendMessageCommand(
                    $this->getTelegramChat()->chat_id,
                    $this->render('index', compact('phrase')),
                    [
                        'parseMode' => $this->textFormat,
                        'replyMarkup' => new InlineKeyboardMarkup([
                            [
                                [
                                    'callback_data' => $phrase->isTypeBlack()
                                        ? AdminMessageFilterBlacklistController::createRoute('index', [
                                            'chatId' => $phrase->chat_id,
                                        ])
                                        : AdminMessageFilterWhitelistController::createRoute('index', [
                                            'chatId' => $phrase->chat_id,
                                        ]),
                                    'text' => '🔙',
                                ],
                                [
                                    'callback_data' => AdminMessageFilterPhraseController::createRoute('create', [
                                        'phraseId' => $phraseId,
                                    ]),
                                    'text' => '✏️',
                                ],
                                [
                                    'callback_data' => AdminMessageFilterPhraseController::createRoute('delete', [
                                        'phraseId' => $phraseId,
                                    ]),
                                    'text' => '🗑',
                                ],
                            ],
                        ]),
                    ]
                ),
            ];
        }
    }

    public function actionDelete($phraseId = null)
    {
        $phrase = Phrase::findOne($phraseId);

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

        return [
            new EditMessageTextCommand(
                $this->getTelegramChat()->chat_id,
                $this->getUpdate()->getCallbackQuery()->getMessage()->getMessageId(),
                $this->render('create'),
                [
                    'parseMode' => $this->textFormat,
                    'replyMarkup' => new InlineKeyboardMarkup([
                        [
                            [
                                'callback_data' => AdminMessageFilterPhraseController::createRoute('index', [
                                    'phraseId' => $phraseId,
                                ]),
                                'text' => '🔙',
                            ],
                        ],
                    ]),
                ]
            ),
        ];
    }

    public function actionUpdate($phraseId = null)
    {
        $update = $this->getUpdate();

        $phrase = Phrase::findOne($phraseId);

        $text = $update->getMessage()->getText();

        if (!Phrase::find()->where([
            'chat_id' => $phrase->chat_id,
            'text' => $text,
            'type' => $phrase->type
        ])->exists()) {
            $phrase->text = $text;
            $phrase->save();

            return $this->actionIndex($phraseId);
        }
    }
}
