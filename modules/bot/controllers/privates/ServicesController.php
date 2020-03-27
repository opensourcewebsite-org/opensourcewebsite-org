<?php

namespace app\modules\bot\controllers\privates;

use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\response\ResponseBuilder;
use Yii;
use app\modules\bot\components\response\commands\SendMessageCommand;
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
        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->answerCallbackQuery()
            ->editMessageTextOrSendMessage(
                $this->render('index'),
                [
                    [
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
                            [
                                'callback_data' => '/s_re',
                                'text' => '🏗 ' . Yii::t('bot', 'Real Estates'),
                            ],
                        ],
                    ],
                    [
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
                    ],
                ],
                MenuController::createRoute(),
                false
            )
            ->build();
    }
}
