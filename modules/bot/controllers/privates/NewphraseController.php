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
class NewphraseController extends Controller
{
    /**
     * @return array
     */
    public function actionIndex($type = null, $groupId = null)
    {
        $telegramUser = $this->getTelegramUser();

        $telegramUser->getState()->setName('/set_newphrase ' . $type . ' ' . $groupId);
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
                                'callback_data' => ($type == Phrase::TYPE_BLACK ? '/blacklist' : '/whitelist') . ' ' . $groupId,
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

    public function actionUpdate($type = null, $groupId = null) {
        $update = $this->getUpdate();
        $text = $update->getMessage()->getText();

        $phrase = new Phrase();

        $phrase->id = time();
        $phrase->group_id = $groupId;
        $phrase->type = $type;
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
                                'callback_data' => ($type == Phrase::TYPE_BLACK ? '/blacklist' : '/whitelist') . ' ' . $groupId,
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
