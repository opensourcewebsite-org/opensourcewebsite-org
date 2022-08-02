<?php

namespace app\modules\bot\controllers\privates;

use app\models\Language;
use app\modules\bot\components\Controller;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\models\Chat;
use app\modules\bot\models\ChatMember;
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
     * @param string|null $start [A-Za-z0-9_-]
     * @return array
     *
     * @link https://core.telegram.org/bots#deep-linking
     */
    public function actionIndex($start = null)
    {
        if (!empty($start)) {
            // provider chat id
            if ($start < 0) {
                $chat = Chat::findOne([
                    'chat_id' => $start,
                ]);
            // provider user username/id or chat username is used
            } elseif (preg_match_all('/(?:(?:[A-Za-z0-9][_]{0,1})*[A-Za-z0-9]+)/i', $start, $matches)) {
                $matches = array_shift($matches);

                if (isset($matches[0])) {
                    $username = $matches[0];

                    $viewUser = User::find()
                        ->andWhere([
                            'or',
                            ['provider_user_name' => $username],
                            ['provider_user_id' => $username],
                        ])
                        ->human()
                        ->one();

                    if (isset($viewUser)) {
                        $user = $this->getTelegramUser();

                        if (isset($matches[1])) {
                            $username2 = $matches[1];

                            $chat = Chat::findOne([
                                'username' => $username2,
                            ]);

                            if (isset($chat)) {
                                if (($user->provider_user_name == $username) || ($user->provider_user_id == $username)) {
                                    if ($chat->isGroup()) {
                                        return $this->run('group-guest/view', [
                                            'id' => $chat->id,
                                        ]);
                                    } elseif ($chat->isChannel()) {
                                        return $this->run('channel-guest/view', [
                                            'id' => $chat->id,
                                        ]);
                                    }
                                }

                                $chatMember = ChatMember::findOne([
                                    'chat_id' => $chat->id,
                                    'user_id' => $viewUser->id,
                                ]);

                                if (isset($chatMember)) {
                                    return $this->run('member/id', [
                                        'id' => $chatMember->id,
                                    ]);
                                }
                            }
                        }

                        if (($user->provider_user_name == $username) || ($user->provider_user_id == $username)) {
                            return $this->run('my-profile/index');
                        }

                        return $this->run('user/id', [
                            'id' => $viewUser->provider_user_id,
                        ]);
                    } else {
                        $chat = Chat::findOne([
                            'username' => $username,
                        ]);
                    }
                }
            }

            if (isset($chat)) {
                if ($chat->isGroup()) {
                    return $this->run('group-guest/view', [
                        'id' => $chat->id,
                    ]);
                } elseif ($chat->isChannel()) {
                    return $this->run('channel-guest/view', [
                        'id' => $chat->id,
                    ]);
                }
            }
        }

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
}
