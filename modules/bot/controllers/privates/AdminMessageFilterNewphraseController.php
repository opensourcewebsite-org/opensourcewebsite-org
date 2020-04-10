<?php

namespace app\modules\bot\controllers\privates;

use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\response\ResponseBuilder;
use app\modules\bot\components\Controller as Controller;
use app\modules\bot\models\Phrase;

/**
 * Class AdminMessageFilterNewphraseController
 *
 * @package app\controllers\bot
 */
class AdminMessageFilterNewphraseController extends Controller
{
    /**
     * @return array
     */
    public function actionIndex($type = null, $chatId = null)
    {
        $this->getState()->setName(self::createRoute('update', [
            'type' => $type,
            'chatId' => $chatId,
        ]));

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render('index'),
                [
                    [
                        [
                            'callback_data' => $type == Phrase::TYPE_BLACKLIST
                                ? AdminMessageFilterBlacklistController::createRoute('index', [
                                    'chatId' => $chatId,
                                ])
                                : AdminMessageFilterWhitelistController::createRoute('index', [
                                    'chatId' => $chatId,
                                ]),
                            'text' => Emoji::BACK,
                        ],
                    ],
                ]
            )
            ->build();
    }

    public function actionUpdate($type = null, $chatId = null)
    {
        $update = $this->getUpdate();

        $text = $update->getMessage()->getText();

        if (!Phrase::find()->where(['type' => $type, 'chat_id' => $chatId, 'text' => $text])->exists()) {
            $phrase = new Phrase([
                'chat_id' => $chatId,
                'type' => $type,
                'text' => $text,
                'created_by' => $this->getTelegramUser()->id,
            ]);
            $phrase->save();
        }

        $this->getState()->setName($type == Phrase::TYPE_BLACKLIST
            ? AdminMessageFilterBlacklistController::createRoute('index', [
                'chatId' => $chatId,
            ])
            : AdminMessageFilterWhitelistController::createRoute('index', [
                'chatId' => $chatId,
            ]));

        $this->module->dispatchRoute($update);
    }
}
