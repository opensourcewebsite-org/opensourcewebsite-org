<?php

namespace app\modules\bot\controllers\privates;

use Yii;
use app\modules\bot\components\Controller;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\models\User;

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

        $telegramUser = User::findOne([
            'provider_user_id' => $providerUserId,
            'is_bot' => 0,
        ]);

        if (!isset($telegramUser)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $params = [
            'telegramUser' => $telegramUser,
            'user' => $telegramUser->globalUser,
        ];

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('index', $params),
                [
                    [
                        [
                            'callback_data' => MenuController::createRoute(),
                            'text' => Emoji::MENU,
                        ],
                        [
                            'callback_data' => self::createRoute('refresh', [
                                'id' => $telegramUser->provider_user_id,
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
                $providerUserName = ltrim($matches[0], '@');
            }
        }

        if (!isset($providerUserName)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $telegramUser = User::findOne([
            'provider_user_name' => $providerUserName,
            'is_bot' => 0,
        ]);

        if (!isset($telegramUser)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        return $this->runAction('id', [
            'id' => $telegramUser->provider_user_id,
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

        $telegramUser = User::findOne([
            'provider_user_id' => $id,
            'is_bot' => 0,
        ]);

        if (!isset($telegramUser)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        foreach ($telegramUser->chatMembers as $chatMember) {
            $botApiChatMember = $this->getBotApi()->getChatMember(
                $chatMember->chat->getChatId(),
                $telegramUser->provider_user_id
            );

            if ($botApiChatMember) {
                $botApiUser = $botApiChatMember->getUser();

                $telegramUser->setAttributes([
                    'provider_user_name' => $botApiUser->getUsername(),
                    'provider_user_first_name' => $botApiUser->getFirstName(),
                    'provider_user_last_name' => $botApiUser->getLastName(),
                ]);

                $telegramUser->save(false);

                break;
            }
        }

        return $this->runAction('id', [
            'id' => $id,
        ]);
    }
}
