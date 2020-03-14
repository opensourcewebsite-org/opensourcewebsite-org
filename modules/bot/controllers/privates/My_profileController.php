<?php

namespace app\modules\bot\controllers\privates;

use Yii;
use \app\modules\bot\components\response\SendMessageCommand;
use app\modules\bot\components\Controller;
use \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use app\models\Currency;
use app\models\Language;

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
        $telegramUser = $this->getTelegramUser();
        $user = $this->getUser();

        $currencyCode = $telegramUser->currency_code;
        $currencyName = Currency::findOne(['code' => $currencyCode])->name;

        $languageCode = $telegramUser->language_code;
        $languageName = Language::findOne(['code' => $languageCode])->name;
        $languageCode = strtoupper($languageCode);

        $params = [
            'firstName' => $telegramUser->provider_user_first_name,
            'lastName' => $telegramUser->provider_user_last_name,
            'username' => $telegramUser->provider_user_name,
            'gender' => $user->gender,
            'birthday' => $user->birthday,
            'currency' => "$currencyName ($currencyCode)",
            'language' => "$languageName ($languageCode)",
        ];

        return [
            new SendMessageCommand(
                $this->getTelegramChat()->chat_id,
                $this->render('index', $params),
                [
                    'parseMode' => $this->textFormat,
                    'replyMarkup' => new InlineKeyboardMarkup([
                        [
                            [
                                'callback_data' => '/my_location',
                                'text' => Yii::t('bot', 'Location'),
                            ],
                        ],
                        [
                            [
                                'callback_data' => '/my_timezone',
                                'text' => '🏗 ' . Yii::t('bot', 'Timezone'),
                            ],
                        ],
                        [
                            [
                                'callback_data' => '/my_gender',
                                'text' => Yii::t('bot', 'Gender'),
                            ],
                        ],
                        [
                            [
                                'callback_data' => '/my_birthday',
                                'text' => Yii::t('bot', 'Birthday'),
                            ],
                        ],
                        [
                            [
                                'callback_data' => '/my_currency',
                                'text' => Yii::t('bot', 'Currency'),
                            ],
                        ],
                        [
                            [
                                'callback_data' => '/my_email',
                                'text' => Yii::t('bot', 'Email'),
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
