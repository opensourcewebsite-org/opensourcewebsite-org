<?php

namespace app\modules\bot\controllers\privates;

use Yii;
use \app\modules\bot\components\response\SendMessageCommand;
use \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use app\modules\bot\components\Controller as Controller;

/**
 * Class MenuController
 *
 * @package app\controllers\bot
 */
class MenuController extends Controller
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
                                'callback_data' => MyProfileController::createRoute(),
                                'text' => Yii::t('bot', 'Profile')
                            ],
                        ],
                        [
                            [
                                'callback_data' => MyRatingController::createRoute(),
                                'text' => Yii::t('bot', 'Rating')
                            ],
                        ],
                        [
                            [
                                'callback_data' => MyReferralsController::createRoute(),
                                'text' => Yii::t('bot', 'Referrals')
                            ],
                        ],
                        [
                            [
                                'callback_data' => ServicesController::createRoute(),
                                'text' => '🏗 ' . Yii::t('bot', 'Services')
                            ],
                        ],
                        [
                            [
                                'callback_data' => AdminController::createRoute(),
                                'text' => Yii::t('bot', 'Groups')
                            ],
                        ],
                        [
                            [
                                'callback_data' => HelpController::createRoute(),
                                'text' => Yii::t('bot', 'Commands')
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
                                'callback_data' => StartController::createRoute(),
                                'text' => '👋',
                            ],
                            [
                                'callback_data' => MyLanguageController::createRoute(),
                                'text' => '🗣',
                            ],
                        ],
                    ]),
                ]
            ),
        ];
    }
}
