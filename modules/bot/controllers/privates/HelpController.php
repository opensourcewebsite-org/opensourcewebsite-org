<?php

namespace app\modules\bot\controllers\privates;

use \app\modules\bot\components\response\SendMessageCommand;
use \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use Yii;
use app\modules\bot\components\Controller as Controller;

/**
 * Class HelpController
 *
 * @package app\controllers\bot
 */
class HelpController extends Controller
{
    /**
     * @return array
     */
    public function actionIndex()
    {
        return [
            new SendMessageCommand(
                $this->getTelegramChat()->chat_id,
                $this->render('index'),
                [
                    'parseMode' => $this->textFormat,
                    'replyMarkup' => new InlineKeyboardMarkup([
                        [
                            [
                                'callback_data' => '/start',
                                'text' => Yii::t('bot', 'Greeting')
                            ],
                            [
                                'callback_data' => '/my_language',
                                'text' => Yii::t('bot', 'Language')
                            ],
                        ],
                    ]),
                ]
            ),
        ];
    }
}
