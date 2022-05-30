<?php

namespace app\modules\bot\controllers\privates;

use app\modules\bot\components\Controller;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\models\Chat;
use app\modules\bot\models\User;
use Yii;

/**
 * Class UserController
 *
 * @package app\modules\bot\controllers\privates
 */
class UserController extends Controller
{
    /**
     * @return array
     */
    public function actionMessage()
    {
        if ($forwardFromUser = $this->getMessage()->getForwardFrom()) {
            $providerUserId = $forwardFromUser->getId();
        }

        if (!isset($providerUserId)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        return $this->runAction('id', [
            'id' => $providerUserId,
        ]);
    }

    /**
     * @return array
     */
    public function actionId($id = null)
    {
        if ($id) {
            $providerUserId = $id;
        } elseif ($text = $this->getMessage()->getText()) {
            if (preg_match('/(?:^(?:[0-9]+))/i', $text, $matches)) {
                $providerUserId = $matches[0];
            }
        }

        $user = User::findOne([
            'provider_user_id' => $providerUserId,
            'is_bot' => 0,
        ]);

        if (!isset($user)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('index', [
                    'user' => $user,
                ]),
                [
                    [
                        [
                            'callback_data' => MenuController::createRoute(),
                            'text' => Emoji::MENU,
                        ],
                        [
                            'callback_data' => self::createRoute('refresh', [
                                'id' => $user->provider_user_id,
                            ]),
                            'text' => Emoji::REFRESH,
                        ],
                    ],
                ],
                [
                    'disablePreview' => true,
                ]
            )
            ->build();
    }

    /**
     * @return array
     */
    public function actionUsername()
    {
        if ($text = $this->getMessage()->getText()) {
            if (preg_match('/(?:^@(?:[A-Za-z0-9][_]{0,1})*[A-Za-z0-9]+)/i', $text, $matches)) {
                $username = ltrim($matches[0], '@');
            }
        }

        if (!isset($username)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $user = User::findOne([
            'provider_user_name' => $username,
            'is_bot' => 0,
        ]);

        if (!isset($user)) {
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

            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        return $this->runAction('id', [
            'id' => $user->provider_user_id,
        ]);
    }

    /**
     * @return array
     */
    public function actionRefresh($id = null)
    {
        if (!$id) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $user = User::findOne([
            'provider_user_id' => $id,
            'is_bot' => 0,
        ]);

        if (!isset($user)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        foreach ($user->chatMembers as $chatMember) {
            $botApiChatMember = $this->getBotApi()->getChatMember(
                $chatMember->chat->getChatId(),
                $user->provider_user_id
            );

            if ($botApiChatMember) {
                $botApiUser = $botApiChatMember->getUser();

                $user->setAttributes([
                    'provider_user_name' => $botApiUser->getUsername(),
                    'provider_user_first_name' => $botApiUser->getFirstName(),
                    'provider_user_last_name' => $botApiUser->getLastName(),
                ]);

                $user->save(false);

                break;
            }
        }

        return $this->runAction('id', [
            'id' => $id,
        ]);
    }
}
