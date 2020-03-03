<?php

namespace app\modules\bot\controllers\privates;

use Yii;
use \app\modules\bot\components\response\SendMessageCommand;
use \app\modules\bot\components\response\EditMessageTextCommand;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use app\modules\bot\components\Controller as Controller;
use app\modules\bot\models\Chat;
use app\modules\bot\models\Phrase;
use app\modules\bot\models\User;

/**
 * Class FilterChatController
 *
 * @package app\controllers\bot
 */
class Admin_filter_newphraseController extends Controller
{
    /**
     * @return array
     */
    public function actionIndex($type = null, $groupId = null)
    {
        $telegramUser = $this->getTelegramUser();

        $telegramUser->getState()->setName('/admin_filter_set_newphrase ' . $type . ' ' . $groupId);
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
                                'callback_data' => ($type == Phrase::TYPE_BLACKLIST ? '/admin_filter_blacklist' : '/admin_filter_whitelist') . ' ' . $groupId,
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

    public function actionUpdate($type = null, $groupId = null)
    {
        $update = $this->getUpdate();
        $telegramUser = $this->getTelegramUser();

        $text = $update->getMessage()->getText();

        if (!Phrase::find()->where(['type' => $type, 'group_id' => $groupId, 'text' => $text])->exists()) {
            $phrase = new Phrase();

            $user = User::find()->where(['provider_user_id' => $this->getTelegramChat()->chat_id])->one();

            $phrase->setAttributes([
                'group_id' => $groupId,
                'type' => $type,
                'text' => $text,
                'created_by' => $user->id,
            ]);

            $phrase->save();
        }

        $telegramUser->getState()->setName(($type == Phrase::TYPE_BLACKLIST ? '/admin_filter_blacklist' : '/admin_filter_whitelist') . ' ' . $groupId);
        $telegramUser->save();

        $this->module->dispatchRoute($update);
    }
}
