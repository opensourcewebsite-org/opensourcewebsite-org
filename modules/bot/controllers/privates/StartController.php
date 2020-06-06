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
 * @package app\controllers\bot
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
                            'url' => 'https://opensourcewebsite.org',
                            'text' => Yii::t('bot', 'Website'),
                        ],
                    ],
                    [
                        [
                            'url' => 'https://join.slack.com/t/opensourcewebsite/shared_invite/enQtNDE0MDc2OTcxMDExLWJmMjFjOGUxNjFiZTg2OTc0ZDdkNTdhNDIzZDE2ODJiMGMzY2M5Yjg3NzEyNGMxNjIwZWE0YTFhNTE3MjhiYjY',
                            'text' => Yii::t('bot', 'Slack'),
                        ],
                    ],
                    [
                        [
                            'url' => 'https://discord.gg/94WpSPJ',
                            'text' => Yii::t('bot', 'Discord'),
                        ],
                    ],
                    [
                        [
                            'url' => 'https://gitter.im/opensourcewebsite-org',
                            'text' => Yii::t('bot', 'Gitter'),
                        ],
                    ],
                    [
                        [
                            'url' => 'https://github.com/opensourcewebsite-org/opensourcewebsite-org',
                            'text' => Yii::t('bot', 'Source Code'),
                        ],
                    ],
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
                            'text' => Emoji::MENU,
                        ],
                        [
                            'callback_data' => LanguageController::createRoute(),
                            'text' => Emoji::LANGUAGE,
                        ],
                    ],
                ]
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

        $chatId = $this->getUpdate()->getMessage()->getChat()->getId();
        $messageId = $this->getUpdate()->getMessage()->getMessageId();

        if (isset($language)) {
            #$this->DeleteLastMessage($chatId, $messageId);
            return $this->actionSave($language->code);
        } else {
            #$this->DeleteLastMessage($chatId, $messageId);
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

    public function deleteLastMessage($chatId, $messageId)
    {
        $deleteBotMessage = new DeleteMessageCommand($chatId, $messageId - 1);
        $deleteBotMessage->send($this->getBotApi());
        $deleteUserMessage = new DeleteMessageCommand($chatId, $messageId);
        $deleteUserMessage->send($this->getBotApi());
    }
}
