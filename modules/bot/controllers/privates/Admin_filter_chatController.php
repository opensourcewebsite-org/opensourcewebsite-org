<?php

namespace app\modules\bot\controllers\privates;

use Yii;
use \app\modules\bot\components\response\SendMessageCommand;
use \app\modules\bot\components\response\EditMessageTextCommand;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use app\modules\bot\components\Controller as Controller;
use app\modules\bot\models\Chat;

/**
 * Class AdminController
 *
 * @package app\controllers\bot
 */
class Admin_filter_chatController extends Controller
{
    /**
     * @return array
     */
    public function actionIndex($groupId)
    {
        $chat = Chat::find()->where(['id' => $groupId])->one();
        $groupTitle = $chat->title;

        return [
            new EditMessageTextCommand(
                $this->getTelegramChat()->chat_id,
                $this->getUpdate()->getCallbackQuery()->getMessage()->getMessageId(),
                $this->render('index', compact('groupTitle')),
                [
                    'parseMode' => $this->textFormat,
                    'replyMarkup' => new InlineKeyboardMarkup([
                        [
                            [
                                'callback_data' => '/admin_filter_filterchat ' . $groupId,
                                'text' => Yii::t('bot', 'Message Filter'), 
                            ],
                        ],
                        [
                            [
                                'callback_data' => '/join_hider_main ' . $groupId,
                                'text' => 'Join Hider',
                            ],
                        ],
                        [
                            [
                                'url' => 'https://github.com/opensourcewebsite-org/opensourcewebsite-org/blob/master/CONTRIBUTING.md',
                                'text' => Yii::t('bot', 'Read more')
                            ],
                        ],
                        [
                            [
                                'callback_data' => '/admin',
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
