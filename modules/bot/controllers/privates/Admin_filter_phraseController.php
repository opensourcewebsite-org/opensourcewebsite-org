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

        $phrase = Phrase::find()->where(['id' => $phraseId])->one();

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
                                    'text' => 'Изменить',
                                ],
                            ],
                            [
                                [
                                    'callback_data' => '/admin_filter_delete_phrase ' . $phraseId,
                                    'text' => 'Удалить',
                                ],
                            ],
                            [
                                [
                                    'callback_data' => ($phrase->isTypeBlack() ? '/admin_filter_blacklist' : '/admin_filter_whitelist') . ' ' . $phrase->group_id,
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
                                    'text' => 'Изменить',
                                ],
                            ],
                            [
                                [
                                    'callback_data' => '/admin_filter_delete_phrase ' . $phraseId,
                                    'text' => 'Удалить',
                                ],
                            ],
                            [
                                [
                                    'callback_data' => ($phrase->isTypeBlack() ? '/admin_filter_blacklist' : '/admin_filter_whitelist') . ' ' . $phrase->group_id,
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
        $phrase = Phrase::find()->where(['id' => $phraseId])->one();

        $groupId = $phrase->group_id;

        $isTypeBlack = $phrase->isTypeBlack();
        $phrase->delete();

        $update = $this->getUpdate();
        $update->getCallbackQuery()->setData(($isTypeBlack ? '/admin_filter_blacklist' : '/admin_filter_whitelist') . ' ' . $groupId);

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

        $phrase = Phrase::find()->where(['id' => $phraseId])->one();

        $text = $update->getMessage()->getText();

        if (Phrase::find()->where(['group_id' => $phrase->group_id, 'text' => $text, 'type' => $phrase->type])->exists()) {
            return [
                new SendMessageCommand(
                    $this->getTelegramChat()->chat_id,
                    $this->render('update'),
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

        $phrase->text = $text;
        $phrase->save();

        return $this->actionIndex($phraseId);
    }
}
