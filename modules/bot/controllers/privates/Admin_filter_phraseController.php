<?php

namespace app\modules\bot\controllers\privates;

use Yii;
use \app\modules\bot\components\response\SendMessageCommand;
use \app\modules\bot\components\response\EditMessageTextCommand;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use app\modules\bot\components\Controller as Controller;
use app\modules\bot\models\Phrase;

/**
 * Class FilterChatController
 *
 * @package app\controllers\bot
 */
class Admin_filter_phraseController extends Controller
{
    /**
     * @return array
     */
    public function actionIndex($phraseId = null)
    {
        $telegramUser = $this->getTelegramUser();

        $telegramUser->getState()->setName(null);
        $telegramUser->save();

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
                                    'callback_data' => '/admin_filter_change_phrase ' . $phraseId,
                                    'text' => Yii::t('bot', 'Change'),
                                ],
                            ],
                            [
                                [
                                    'callback_data' => '/admin_filter_delete_phrase ' . $phraseId,
                                    'text' => Yii::t('bot', 'Remove'),
                                ],
                            ],
                            [
                                [
                                    'callback_data' => ($phrase->isTypeBlack()
                                        ? '/admin_filter_blacklist'
                                        : '/admin_filter_whitelist'
                                    ) . ' ' . $phrase->chat_id,
                                    'text' => '🔙',
                                ],
                                [
                                    'callback_data' => '/menu',
                                    'text' => '⏪ ' . Yii::t('bot', 'Main menu'),
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
                                    'callback_data' => '/admin_filter_change_phrase ' . $phraseId,
                                    'text' => Yii::t('bot', 'Change'),
                                ],
                            ],
                            [
                                [
                                    'callback_data' => '/admin_filter_delete_phrase ' . $phraseId,
                                    'text' => Yii::t('bot', 'Remove'),
                                ],
                            ],
                            [
                                [
                                    'callback_data' => ($phrase->isTypeBlack()
                                        ? '/admin_filter_blacklist'
                                        : '/admin_filter_whitelist'
                                    ) . ' ' . $phrase->chat_id,
                                    'text' => '🔙',
                                ],
                                [
                                    'callback_data' => '/menu',
                                    'text' => '⏪ ' . Yii::t('bot', 'Main menu'),
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
        $update->getCallbackQuery()->setData(($isTypeBlack
            ? '/admin_filter_blacklist'
            : '/admin_filter_whitelist'
        ) . ' ' . $chatId);

        $this->module->dispatchRoute($update);
    }

    public function actionCreate($phraseId = null)
    {
        $telegramUser = $this->getTelegramUser();

        $telegramUser->getState()->setName('/admin_filter_update_phrase ' . $phraseId);
        $telegramUser->save();

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
                                'callback_data' => '/admin_filter_phrase ' . $phraseId,
                                'text' => '🔙',
                            ],
                            [
                                'callback_data' => '/menu',
                                'text' => '⏪ ' . Yii::t('bot', 'Main menu'),
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
