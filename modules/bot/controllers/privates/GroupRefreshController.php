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

        if (!isset($chat)) {
            return $this->run('group/index');
        }

        try {
            $this->getBotApi()->getChat($chat->chat_id);
        } catch (\Exception $e) {
            Yii::warning($e);

            if ($e->getCode() == 400) {
                // group has been removed in Telegram
                removeFromDb($chat);
                return $this->run('group/index');
            }

            throw $e;
        }

        if (!$this->getBotApi()
            ->getChatMember($chat->chat_id, explode(':', $this->getBot()->token)[0])
            ->isActualChatMember()) {
            // bot is not the chat member => remove chat from db
            removeFromDb($chat);
            return $this->run('group/index');
        }

        $telegramAdministrators = $this->getBotApi()->getChatAdministrators($chat->chat_id);
        $telegramAdministratorsIds = array_map(
            fn ($a) => $a->getUser()->getId(),
            $telegramAdministrators
        );

        if (!in_array($this->getTelegramUser()->provider_user_id, $telegramAdministratorsIds)) {
            // user is not in Telegram's admins list

            if (empty($telegramAdministratorsIds)) {
                // and no administrators left => remove chat from db
                removeFromDb($chat);
            }
            return $this->run('group/index');
        }

        $curAdministratorsIds = array_map(fn ($a) => $a->provider_user_id, $chat->getAdministrators()->all());

        $outdatedAdministrators = $chat->getAdministrators()
            ->andWhere(['not', ['provider_user_id' => $telegramAdministratorsIds]])
            ->all();
        foreach ($outdatedAdministrators as $outdatedAdministrator) {
            try {
                $telegramChatMember = $this->getBotApi()->getChatMember(
                    $chat->chat_id,
                    $outdatedAdministrator->provider_user_id
                );
            } catch (\Exception $e) {
                Yii::warning($e);

                $chat->unlink('users', $outdatedAdministrator, true);

                continue;
            }

            if ($telegramChatMember->isActualChatMember()) {
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

            $chat->unlink('users', $outdatedAdministrator, true);
        }

        $users = ArrayHelper::index(User::find(['provider_user_id' => $telegramAdministratorsIds])->all(), 'provider_user_id');

        foreach ($telegramAdministrators as $telegramAdministrator) {
            $user = $users[$telegramAdministrator->getUser()->getId()];
            if (!isset($user)) {
                $user = User::createUser($telegramAdministrator->getUser());
                $user->updateInfo($telegramAdministrator->getUser());
                $user->save();
            }
            if (!in_array($user->provider_user_id, $curAdministratorsIds)) {
                $user->link('chats', $chat, ['status' => $telegramAdministrator->getStatus()]);
            }
        }

        return $this->getResponseBuilder()
            ->answerCallbackQuery(
                $this->render('index'),
                true
            )
            ->build();
    }
}
