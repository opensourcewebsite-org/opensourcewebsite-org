<?php

namespace app\modules\bot\controllers\privates;

use Yii;
use app\modules\bot\components\Controller;
use app\models\Language;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\models\Chat;

/**
 * Class StartController
 *
 * @package app\modules\bot\controllers\privates
 */
class StartController extends Controller
{
    /**
     * @return array
     */
    public function actionIndex($start = null)
    {
        if (!empty($start) && ($start < 0)) {
            $chat = Chat::find()
                ->where([
                    'chat_id' => $start,
                ])
                ->one();

            if (isset($chat)) {
                if ($chat->isGroup()) {
                    return $this->run('group-guest/view', [
                        'chatId' => $chat->id,
                    ]);
                }

                if ($chat->isChannel()) {
                    return $this->run('channel-guest/view', [
                        'chatId' => $chat->id,
                    ]);
                }
            }
        }

        $this->getState()->setName(self::createRoute('search'));

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('index'),
                [
                    [
                        [
                            'callback_data' => MenuController::createRoute(),
                            'text' => Emoji::MENU . ' ' . Yii::t('bot', 'BEGIN'),
                        ],
                    ],
                    [
                        [
                            'url' => 'https://github.com/opensourcewebsite-org/opensourcewebsite-org/blob/master/DONATE.md',
                            'text' => Emoji::DONATE . ' ' . Yii::t('bot', 'Donate'),
                        ],
                        [
                            'url' => 'https://github.com/opensourcewebsite-org/opensourcewebsite-org/blob/master/CONTRIBUTING.md',
                            'text' => Emoji::CONTRIBUTE . ' ' . Yii::t('bot', 'Contribute'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => HelpController::createRoute(),
                            'text' => Emoji::INFO . ' ' . Yii::t('bot', 'Commands'),
                        ],
                        [
                            'callback_data' => LanguageController::createRoute(),
                            'text' => Emoji::LANGUAGE  . ' ' . strtoupper(Yii::$app->language),
                        ],
                    ],
                ],
                [
                    'disablePreview' => true,
                ]
            )
            ->build();
    }

    public function actionSearch()
    {
        $text = $this->getUpdate()->getMessage()->getText();

        if (strlen($text) <= 3) {
            $language = Language::find()
                ->orFilterWhere(['like', 'code', $text, false])
                ->one();
        } else {
            $language = Language::find()
                ->orFilterWhere(['like', 'name', $text . '%', false])
                ->orFilterWhere(['like', 'name_ascii', $text . '%', false])
                ->one();
        }

        if (isset($language)) {
            return $this->actionSave($language->code);
        } else {
            return $this->actionIndex();
        }
    }

    public function actionSave($languageCode)
    {
        if ($languageCode) {
            $language = Language::findOne(['code' => $languageCode]);
            if ($language) {
                $telegramUser = $this->getTelegramUser();
                if ($telegramUser) {
                    $telegramUser->language_id = $language->id;
                    if ($telegramUser->save()) {
                        Yii::$app->language = $language->code;
                    }
                }
            }
        } else {
            return $this->actionIndex();
        }
        return $this->actionIndex();
    }
}
