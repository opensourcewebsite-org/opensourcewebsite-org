<?php

namespace app\modules\bot\controllers\privates;

use \app\modules\bot\components\response\SendMessageCommand;
use \app\modules\bot\components\ReplyKeyboardManager;
use \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use Yii;
use app\modules\bot\components\Controller as Controller;

/**
 * Class StartController
 *
 * @package app\controllers\bot
 */
class StartController extends Controller
{
    /**
     * @return array
     */
    public function actionIndex()
    {
        $update = $this->getUpdate();

        ReplyKeyboardManager::getInstance()->addKeyboardButton(0, [
            'text' => '⚙️',
            ReplyKeyboardManager::REPLYKEYBOARDBUTTON_IS_CONSTANT => true,
        ]);

        return [
            new SendMessageCommand(
                $this->getTelegramChat()->chat_id,
                $this->render('index'),
                [
                    'parseMode' => $this->textFormat,
                    'replyMarkup' => new InlineKeyboardMarkup([
                        [
                            [
                                'callback_data' => '/my_language',
                                'text' => Yii::t('bot', 'Language')
                            ],
                            [
                                'callback_data' => '/help',
                                'text' => Yii::t('bot', 'Help')
                            ],
                            [
                                'url' => 'https://opensourcewebsite.org',
                                'text' => Yii::t('bot', 'Website')
                            ],
                        ],
                    ]),
                ]
            ),
        ];
    }
}
