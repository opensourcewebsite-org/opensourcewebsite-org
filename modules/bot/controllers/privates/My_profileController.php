<?php

namespace app\modules\bot\controllers\privates;

use Yii;
use \app\modules\bot\components\response\SendMessageCommand;
use \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use app\modules\bot\components\Controller as Controller;

/**
 * Class My_profileController
 *
 * @package app\modules\bot\controllers
 */
class My_profileController extends Controller
{
    /**
     * @return array
     */
    public function actionIndex()
    {
        $update = $this->getUpdate();

        return [
            new SendMessageCommand(
                $this->getTelegramChat()->chat_id,
                $this->render('index', [
                    'profile' => $update->getMessage()->getFrom(),
                ]),
                [
                    'parseMode' => $this->textFormat,
                    'replyMarkup' => new InlineKeyboardMarkup([
                        [
                            [
                                'callback_data' => '/help',
                                'text' => '🔙',
                            ],
                        ],
                    ]),
                ]
            ),
        ];
    }
}
