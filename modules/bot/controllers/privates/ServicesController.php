<?php

namespace app\modules\bot\controllers\privates;

use Yii;
use \app\modules\bot\components\response\SendMessageCommand;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use app\modules\bot\components\Controller as Controller;

/**
 * Class ServicesController
 *
 * @package app\controllers\bot
 */
class ServicesController extends Controller
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
                                'callback_data' => '/s_ce',
                                'text' => '🏗 ' . Yii::t('bot', 'Currency Exchange'),
                            ],
                        ],
                        [
                            [
                                'callback_data' => '/s_job',
                                'text' => '🏗 ' . Yii::t('bot', 'Jobs'),
                            ],
                        ],
                        [
                            [
                                'callback_data' => '/s_ad',
                                'text' => '🏗 ' . Yii::t('bot', 'Ads'),
                            ],
                        ],
                        [
                            [
                                'callback_data' => '/s_da',
                                'text' => '🏗 ' . Yii::t('bot', 'Dating'),
                            ],
                        ],
                        [
                            [
                                'callback_data' => '/s_re',
                                'text' => '🏗 ' . Yii::t('bot', 'Real Estates'),
                            ],
                        ],
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
                                'callback_data' => '/menu',
                                'text' => '📱',
                            ],
                        ],
                    ]),
                ]
            ),
        ];
    }
}
