<?php

namespace app\modules\bot\controllers\privates;

use Yii;
use \app\modules\bot\components\response\SendMessageCommand;
use \app\modules\bot\components\response\EditMessageTextCommand;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use app\modules\bot\components\Controller as Controller;
use app\modules\bot\models\Chat;
use app\modules\bot\models\Phrase;

/**
 * Class FilterChatController
 *
 * @package app\controllers\bot
 */
class PhraseController extends Controller
{
    /**
     * @return array
     */
    public function actionIndex($phraseId = null)
    {
        $telegramUser = $this->getTelegramUser();

        $telegramUser->getState()->setName(null);
        $telegramUser->save();

        $phrase = Phrase::find()->where(['id' => $phraseId])->one();

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
                                'callback_data' => '/change_phrase ' . $phraseId,
                                'text' => 'Изменить',
                            ],
                        ],
                        [
                            [
                                'callback_data' => '/delete_phrase ' . $phraseId,
                                'text' => 'Удалить',
                            ],
                        ],
                        [
                            [
                                'callback_data' => ($phrase->isTypeBlack() ? '/blacklist' : '/whitelist') . ' ' . $phrase->group_id,
                                'text' => '🔙',
                            ],
                            [
                                'callback_data' => '/menu',
                                'text' => '⏪ В главное меню',
                            ],
                        ],
                    ]),
                ]
            ),
        ];
    }

    public function actionDelete($phraseId = null) {
        $phrase = Phrase::find()->where(['id' => $phraseId])->one();

        $groupId = $phrase->group_id;

        $isTypeBlack = $phrase->isTypeBlack();
        $phrase->delete();

        return [
            new EditMessageTextCommand(
                $this->getTelegramChat()->chat_id,
                $this->getUpdate()->getCallbackQuery()->getMessage()->getMessageId(),
                $this->render('delete'),
                [
                    'parseMode' => $this->textFormat,
                    'replyMarkup' => new InlineKeyboardMarkup([
                        [
                            [
                                'callback_data' => ($isTypeBlack ? '/blacklist' : '/whitelist') . ' ' . $groupId,
                                'text' => Yii::t('bot', 'Next'),
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

    public function actionCreate($phraseId = null) {
        $telegramUser = $this->getTelegramUser();

        $telegramUser->getState()->setName('/update_phrase ' . $phraseId);
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
                                'callback_data' => '/phrase ' . $phraseId,
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

    public function actionUpdate($phraseId = null) {
        $update = $this->getUpdate();

        $phrase = Phrase::find()->where(['id' => $phraseId])->one();

        $text = $update->getMessage()->getText();

        $phrase->text = $text;
        $phrase->save();

        return [
            new SendMessageCommand(
                $this->getTelegramChat()->chat_id,
                $this->render('update'),
                [
                    'parseMode' => $this->textFormat,
                    'replyMarkup' => new InlineKeyboardMarkup([
                        [
                            [
                                'callback_data' => '/phrase ' . $phraseId,
                                'text' => Yii::t('bot', 'Next'),
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
