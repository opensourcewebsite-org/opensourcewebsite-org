<?php

namespace app\modules\bot\controllers\privates;

use Yii;
use app\models\Language;
use app\modules\bot\components\Controller;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\response\commands\DeleteMessageCommand;
use TelegramBot\Api\BotApi;

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
    public function actionIndex()
    {
        $this->getState()->setName(self::createRoute('search'));
        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('index'),
                [
                    [
                        [
                            'url' => 'https://github.com/opensourcewebsite-org/opensourcewebsite-org/blob/master/DONATE.md',
                            'text' => '👼 ' . Yii::t('bot', 'Donate'),
                        ],
                        [
                            'url' => 'https://github.com/opensourcewebsite-org/opensourcewebsite-org/blob/master/CONTRIBUTING.md',
                            'text' => '👨‍🚀 ' . Yii::t('bot', 'Contribute'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => MenuController::createRoute(),
                            'text' => Emoji::MENU . ' ' . Yii::t('bot', 'BEGIN'),
                        ],
                        [
                            'callback_data' => LanguageController::createRoute(),
                            'text' => Emoji::LANGUAGE  . ' ' . strtoupper(Yii::$app->language),
                        ],
                    ],
                ],
                true
            )
            ->build();
    }

    public function actionSearch()
    {
        $update = $this->getUpdate();
        $text = $update->getMessage()->getText();

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
