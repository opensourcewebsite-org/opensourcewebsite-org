<?php

namespace app\modules\bot\controllers\privates;

use Yii;
use \app\modules\bot\components\response\commands\EditMessageTextCommand;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
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
        $telegramUser = $this->getTelegramUser();

        $telegramUser->getState()->setName(AdminMessageFilterNewphraseController::createRoute('update', [
            'type' => $type,
            'chatId' => $chatId,
        ]));
        $telegramUser->save();

        return [
            new EditMessageTextCommand(
                $this->getTelegramChat()->chat_id,
                $this->getUpdate()->getCallbackQuery()->getMessage()->getMessageId(),
                $this->render('index'),
                [
                    'parseMode' => $this->textFormat,
                    'replyMarkup' => new InlineKeyboardMarkup([
                        [
                            [
                                'callback_data' => $type == Phrase::TYPE_BLACKLIST
                                    ? AdminMessageFilterBlacklistController::createRoute('index', [
                                        'chatId' => $chatId,
                                    ])
                                    : AdminMessageFilterWhitelistController::createRoute('index', [
                                        'chatId' => $chatId,
                                    ]),
                                'text' => '🔙',
                            ],
                        ],
                    ]),
                ]
            ),
        ];
    }

    public function actionUpdate($type = null, $chatId = null)
    {
        $update = $this->getUpdate();
        $telegramUser = $this->getTelegramUser();

        $text = $update->getMessage()->getText();

        if (!Phrase::find()->where(['type' => $type, 'chat_id' => $chatId, 'text' => $text])->exists()) {
            $phrase = new Phrase();

            $phrase->setAttributes([
                'chat_id' => $chatId,
                'type' => $type,
                'text' => $text,
                'created_by' => $this->getTelegramUser()->id,
            ]);

            $phrase->save();
        }

        $telegramUser->getState()->setName($type == Phrase::TYPE_BLACKLIST
            ? AdminMessageFilterBlacklistController::createRoute('index', [
                'chatId' => $chatId,
            ])
            : AdminMessageFilterWhitelistController::createRoute('index', [
                'chatId' => $chatId,
            ]));
        $telegramUser->save();

        $this->module->dispatchRoute($update);
    }
}
