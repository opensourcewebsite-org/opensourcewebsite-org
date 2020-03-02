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
class BlacklistController extends Controller
{
    /**
     * @return array
     */
    public function actionIndex($groupId = null)
    {

        $telegramUser = $this->getTelegramUser();
        $telegramUser->getState()->setName(null);
        $telegramUser->save();

        $groupTitle = Chat::find()->where(['id' => $groupId])->one()->title;
        $phrases = Phrase::find()->where(['group_id' => $groupId, 'type' => Phrase::TYPE_BLACK])->all();

        $buttons = [];
        foreach ($phrases as $phrase) {
            $buttons[] = [
                [
                    'callback_data' => '/phrase ' . $phrase->id,
                    'text' => $phrase->text,
                ],
            ];
        }

        $buttons[] = [
            [
                'callback_data' => '/newphrase ' . Phrase::TYPE_BLACK . ' ' . $groupId,
                'text' => Yii::t('bot', 'Add phrase'),
            ],
        ];

        $buttons[] = [
            [
                'callback_data' => '/filterchat ' . $groupId,
                'text' => '🔙',
            ],
            [
                'callback_data' => '/menu',
                'text' => '⏪ ' . Yii::t('bot', 'Main menu'),
            ],
        ];

        return [
            new EditMessageTextCommand(
                $this->getTelegramChat()->chat_id,
                $this->getUpdate()->getCallbackQuery()->getMessage()->getMessageId(),
                $this->render('index', compact('groupTitle')),
                [
                    'parseMode' => $this->textFormat,
                    'replyMarkup' => new InlineKeyboardMarkup($buttons),
                ]
            ),
        ];
    }
}
