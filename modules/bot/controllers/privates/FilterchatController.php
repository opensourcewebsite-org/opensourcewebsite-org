<?php

namespace app\modules\bot\controllers\privates;

use Yii;
use \app\modules\bot\components\response\SendMessageCommand;
use \app\modules\bot\components\response\EditMessageTextCommand;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use app\modules\bot\components\Controller as Controller;
use app\modules\bot\models\Chat;

/**
 * Class FilterChatController
 *
 * @package app\controllers\bot
 */
class FilterchatController extends Controller
{
    /**
     * @return array
     */
    public function actionIndex($groupId = null)
    {

        $chat = Chat::find()->where(['id' => $groupId])->one();

        $groupTitle = $chat->title;
        $isFilterModeBlack = $chat->isFilterModeBlack();

        return [
            new EditMessageTextCommand(
                $this->getTelegramChat()->chat_id,
                $this->getUpdate()->getCallbackQuery()->getMessage()->getMessageId(),
                $this->render('index', compact('groupTitle', 'isFilterModeBlack')),
                [
                    'parseMode' => $this->textFormat,
                    'replyMarkup' => new InlineKeyboardMarkup([
                        [
                            [
                                'callback_data' => '/change_filter_mode ' . $groupId,
                                'text' => Yii::t('bot', 'Change mode'),
                            ],
                        ],
                        [
                            [
                                'callback_data' => '/whitelist ' . $groupId,
                                'text' => Yii::t('bot', 'Change WhiteList'),
                            ],
                        ],
                        [
                            [
                                'callback_data' => '/blacklist ' . $groupId,
                                'text' => Yii::t('bot', 'Change BlackList'),
                            ],
                        ],
                        [
                            [
                                'callback_data' => '/filter',
                                'text' => '🔙',
                            ],
                            [
                                'callback_data' => '/menu',
                                'text' => '⏪ ' . Yii::t('bot', 'Main menu'),
                            ],
                        ]
                    ]),
                ]
            ),
        ];
    }

    public function actionUpdate($groupId = null) {
        $group = Chat::find()->where(['id' => $groupId])->one();

        if ($group->isFilterModeBlack()) {
            $group->filter_mode = Chat::FILTER_MODE_WHITE;
        } else {
            $group->filter_mode = Chat::FILTER_MODE_BLACK;
        }

        $group->save();

        return $this->actionIndex($groupId);
    }
}
