<?php

namespace app\modules\bot\controllers\privates;

use app\modules\bot\components\Controller;
use Yii;
use app\modules\bot\components\response\SendMessageCommand;
use \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;

/**
 * Class DefaultController
 *
 * @package app\modules\bot\controllers
 */
class DefaultController extends Controller
{
    /**
     * @return string
     */
    public function actionIndex()
    {
        return [
            new SendMessageCommand(
                $this->getTelegramChat()->chat_id,
                $this->render('/menu/index'),
                [
                    'parseMode' => $this->textFormat,
                ]
            ),
        ];
    }

    /**
     * @return string
     */
    public function actionCommandNotFound()
	{
        return [
            new SendMessageCommand(
                $this->getTelegramChat()->chat_id,
                $this->render('command-not-found'),
                [
                    'parseMode' => $this->textFormat,
                    'replyMarkup' => new InlineKeyboardMarkup([
                        [
                            [
                                'url' => 'https://github.com/opensourcewebsite-org/opensourcewebsite-org/blob/master/CONTRIBUTING.md',
                                'text' => Yii::t('bot', 'Read more')
                            ],
                        ],
                        [
                            [
                                'callback_data' => '/menu',
                                'text' => '🔙',
                            ],
                        ],
                    ]),
                ]
            ),
        ];
    }
}
