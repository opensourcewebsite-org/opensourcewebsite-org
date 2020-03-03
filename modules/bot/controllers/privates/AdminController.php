<?php

namespace app\modules\bot\controllers\privates;

use Yii;
use \app\modules\bot\components\response\SendMessageCommand;
use \app\modules\bot\components\response\EditMessageTextCommand;
use \app\modules\bot\components\response\AnswerCallbackQueryCommand;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use app\modules\bot\components\Controller as Controller;
use app\modules\bot\models\ChatMember;
use app\modules\bot\models\Chat;

/**
 * Class AdminController
 *
 * @package app\controllers\bot
 */
class AdminController extends Controller
{
    /**
     * @return array
     */
    public function actionIndex()
    {
        $chat_id = $this->getTelegramChat()->chat_id;

        $admins = ChatMember::find()->where(['and', ['telegram_user_id' => $chat_id], ['or', ['status' => ChatMember::STATUS_CREATOR], ['status' => ChatMember::STATUS_ADMINISTRATOR]]])->all();

        $adminGroups = [];
        foreach ($admins as $admin) {
            $adminGroups[] = Chat::findOne($admin->chat_id);
        }

        $buttons = [];
        $currentRow = [];

        foreach ($adminGroups as $adminGroup) {
            $currentRow[] = [
                'callback_data' => '/admin_filter_chat ' . $adminGroup->id,
                'text' => $adminGroup->title,
            ];

            if (count($currentRow) == 2) {
                $buttons[] = $currentRow;
                $currentRow = [];
            }
        }

        if (!empty($currentRow)) {
            $buttons[] = $currentRow;
            $currentRow = [];
        }

        $buttons[] = [
            [
                'url' => 'https://github.com/opensourcewebsite-org/opensourcewebsite-org/blob/master/CONTRIBUTING.md',
                'text' => Yii::t('bot', 'Read more')
            ],
        ];

        $buttons[] = [
            [
                'callback_data' => '/menu',
                'text' => '🔙',
            ],
        ];

        if ($this->getUpdate()->getCallbackQuery()) {
            return [
                new EditMessageTextCommand(
                    $this->getTelegramChat()->chat_id,
                    $this->getUpdate()->getCallbackQuery()->getMessage()->getMessageId(),
                    $this->render('index'),
                    [
                        'parseMode' => $this->textFormat,
                        'replyMarkup' => new InlineKeyboardMarkup($buttons),
                    ]
                ),
            ];
        } else {
            return [
                new SendMessageCommand(
                    $this->getTelegramChat()->chat_id,
                    $this->render('index'),
                    [
                        'parseMode' => $this->textFormat,
                        'replyMarkup' => new InlineKeyboardMarkup($buttons),
                    ]
                ),
            ];
        }
    }
}
