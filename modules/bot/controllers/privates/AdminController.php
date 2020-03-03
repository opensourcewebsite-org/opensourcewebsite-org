<?php

namespace app\modules\bot\controllers\privates;

use Yii;
use \app\modules\bot\components\response\SendMessageCommand;
use \app\modules\bot\components\response\EditMessageTextCommand;
use \app\modules\bot\components\response\AnswerCallbackQueryCommand;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use app\modules\bot\components\Controller as Controller;
use app\modules\bot\models\Admin;
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

        $admins = Admin::find()->where(['telegram_user_id' => $chat_id])->all();

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
                'callback_data' => '/admin_refresh',
                'text' => Yii::t('bot', 'Refresh'),
            ],
        ];

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

    public function actionRefresh()
    {
        $chats = Chat::find()->where(['or', ['type' => Chat::TYPE_GROUP], ['type' => Chat::TYPE_SUPERGROUP], ['type' => Chat::TYPE_CHANNEL]])->all();

        foreach ($chats as $chat) {
            $administrators = $this->getBotApi()->getChatAdministrators($chat->chat_id);

            $adminUserIds = [];
            foreach ($administrators as $administrator) {
                $userId = $administrator->getUser()->getId();
                $adminUserIds[] = $userId;

                if (!Admin::find()->where(['chat_id' => $chat->id, 'telegram_user_id' => $userId])->exists()) {
                    $admin = new Admin();

                    $admin->setAttributes([
                        'chat_id' => $chat->id,
                        'telegram_user_id' => $userId,
                    ]);

                    $admin->save();
                }
            }

            $curAdmins = Admin::find()->where(['chat_id' => $chat->id])->all();

            foreach ($curAdmins as $curAdmin) {
                if (!in_array($curAdmin->telegram_user_id, $adminUserIds)) {
                    $curAdmin->delete();
                }
            }
        }

        $response = $this->actionIndex();

        $response[] = new AnswerCallbackQueryCommand(
            $this->getUpdate()->getCallbackQuery()->getId(),
            ['text' => Yii::t('bot', 'Chats successfully refreshed') . ' ✅']
        );

        return $response;
    }
}
