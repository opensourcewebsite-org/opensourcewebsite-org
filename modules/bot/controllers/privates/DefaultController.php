<?php

namespace app\modules\bot\controllers\privates;

use Yii;
use app\modules\bot\components\Controller as Controller;
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
     * @return array
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
     * @return array
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
                                'url' => 'https://github.com/opensourcewebsite-org/opensourcewebsite-org/blob/master/DONATE.md',
                                'text' => '👼 ' . Yii::t('bot', 'Donate'),
                            ],
                            [
                                'url' => 'https://github.com/opensourcewebsite-org/opensourcewebsite-org/blob/master/CONTRIBUTING.md',
                                'text' => '👨‍🚀 ' . Yii::t('bot', 'Contribution'),
                            ],
                        ],
                        [
                            [
                                'callback_data' => MenuController::createRoute(),
                                'text' => Yii::t('bot', 'Menu'),
                            ],
                        ],
                    ]),
                ]
            ),
        ];
    }
}
