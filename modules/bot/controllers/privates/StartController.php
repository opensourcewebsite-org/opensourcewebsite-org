<?php

namespace app\modules\bot\controllers\privates;

use app\models\Language;
use app\modules\bot\components\Controller;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\models\Chat;
use app\modules\bot\models\User;
use Yii;

/**
 * Class StartController
 *
 * @package app\modules\bot\controllers\privates
 */
class StartController extends Controller
{
    /**
     * @param string|null $start - "A-Z, a-z, 0-9, _ and - are allowed".
     * @return array
     *
     * @link https://core.telegram.org/bots#deep-linking
     */
    public function actionIndex($start = null)
    {
        if (!empty($start)) {
            if ($start < 0) {
                $chat = Chat::findOne([
                    'chat_id' => $start,
                ]);

                if (isset($chat)) {
                    if ($chat->isGroup()) {
                        return $this->run('group-guest/view', [
                            'chatId' => $chat->id,
                        ]);
                    } elseif ($chat->isChannel()) {
                        return $this->run('channel-guest/view', [
                            'chatId' => $chat->id,
                        ]);
                    }
                }
            } elseif (preg_match('/(?:^(?:[A-Za-z0-9][_]{0,1})*[A-Za-z0-9]+)/i', $start, $matches)) {
                $username = $matches[0];

                if ($username) {
                    $viewUser = User::findOne([
                        'provider_user_name' => $username,
                        'is_bot' => 0,
                    ]);

                    if (isset($viewUser)) {
                        $user = $this->getTelegramUser();

                        if ($user->provider_user_name == $username) {
                            return $this->run('my-profile/index');
                        }

                        return $this->run('user/id', [
                            'id' => $viewUser->provider_user_id,
                        ]);
                    } else {
                        $chat = Chat::findOne([
                            'username' => $username,
                        ]);

                        if (isset($chat)) {
                            if ($chat->isGroup()) {
                                return $this->run('group-guest/view', [
                                    'chatId' => $chat->id,
                                ]);
                            } elseif ($chat->isChannel()) {
                                return $this->run('channel-guest/view', [
                                    'chatId' => $chat->id,
                                ]);
                            }
                        }
                    }
                }
            }
        }

        $this->getState()->setName(self::createRoute('input-language'));

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

    public function actionInputLanguage()
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
            $user = $this->getTelegramUser();
            $user->language_id = $language->id;

            if ($user->save()) {
                Yii::$app->language = $language->code;
            }
        }

        return $this->actionIndex();
    }
}
