<?php

namespace app\modules\bot\controllers\privates;

use app\modules\bot\components\Controller;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\models\Chat;
use app\modules\bot\models\ChatMember;
use app\modules\bot\models\User;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * Class GroupRefreshController
 *
 * @package app\modules\bot\controllers\privates
 */
class GroupRefreshController extends Controller
{
    /**
     * @param string|int|null $chatId
     * @return array
     * @throws \yii\db\Exception
     */
    public function actionIndex($chatId = null): array
    {
        function removeFromDb(Chat &$chat)
        {
            $chat->unlinkAll('phrases', true);
            $chat->unlinkAll('settings', true);
            $chat->unlinkAll('users', true);
            $chat->delete();
        }

        if (!isset($chatId)) {
            return $this->run('group/index');
        }

        $chat = Chat::findOne($chatId);

        if (!isset($chat) || !$chat->isGroup()) {
            return $this->run('group/index');
        }

        try {
            $this->getBotApi()->getChat($chat->getChatId());
            $this->getBotApi()->getChatMember($chat->getChatId(), explode(':', $this->getBot()->token)[0])->isActualChatMember();
            $telegramAdministrators = $this->getBotApi()->getChatAdministrators($chat->getChatId());
            $telegramAdministratorsIds = array_map(
                fn ($a) => $a->getUser()->getId(),
                $telegramAdministrators
            );
        } catch (\Exception $e) {
            Yii::warning($e);

            if (in_array($e->getCode(), [400, 403])) {
                // chat has been removed in Telegram or bot is not the chat member => remove chat from db
                removeFromDb($chat);

                return $this->run('group/index');
            }

            throw $e;
        }

        $outdatedAdministrators = $chat->getAdministrators()
            ->andWhere([
                'not',
                ['provider_user_id' => $telegramAdministratorsIds],
            ])
            ->all();

        foreach ($outdatedAdministrators as $outdatedAdministrator) {
            try {
                $telegramChatMember = $this->getBotApi()->getChatMember(
                    $chat->getChatId(),
                    $outdatedAdministrator->provider_user_id
                );

                if ($telegramChatMember && $telegramChatMember->isActualChatMember()) {
                    $chatMember = ChatMember::findOne([
                        'chat_id' => $chat->id,
                        'user_id' => $outdatedAdministrator->id,
                    ]);

                    $chatMember->setAttributes([
                        'status' => $telegramChatMember->getStatus(),
                    ]);

                    $chatMember->save();

                    continue;
                }
            } catch (\Exception $e) {
                Yii::warning($e);
            }

            $chat->unlink('users', $outdatedAdministrator, true);
        }

        $currentAdministratorsIds = array_map(
            fn ($a) => $a->provider_user_id,
            $chat->getAdministrators()->all()
        );

        foreach ($telegramAdministrators as $telegramAdministrator) {
            $user = User::find()
                ->andWhere([
                    'provider_user_id' => $telegramAdministrator->getUser()->getId(),
                ])
                ->one();

            if (!$user) {
                $user = User::createUser($telegramAdministrator->getUser());
                $user->updateInfo($telegramAdministrator->getUser());
                $user->save();
            }

            if (!in_array($user->provider_user_id, $currentAdministratorsIds)) {
                $user->link('chats', $chat, ['status' => $telegramAdministrator->getStatus()]);
            }
        }
        // user is not in Telegram admin list
        if (!in_array($this->getTelegramUser()->provider_user_id, $telegramAdministratorsIds)) {
            return $this->run('group/index');
        } else {
            return $this->getResponseBuilder()
                ->answerCallbackQuery(
                    $this->render('index'),
                    true
                )
                ->build();
        }
    }
}
