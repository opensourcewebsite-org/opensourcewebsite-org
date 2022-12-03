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
 * Class ChannelpRefreshController
 *
 * @package app\modules\bot\controllers\privates
 */
class ChannelRefreshController extends Controller
{
    /**
     * @param int $id Chat->id
     * @return array
     * @throws \yii\db\Exception
     */
    public function actionIndex($id = null)
    {
        $chat = Chat::findOne($id);

        if (!isset($chat) || !$chat->isChannel()) {
            return $this->run('channel/index');
        }

        try {
            $botApiChat = $this->getBotApi()->getChat($chat->getChatId());
        } catch (\Exception $e) {
            Yii::warning($e);

            if (in_array($e->getCode(), [400, 403])) {
                // Chat has been removed in Telegram => remove chat from db
                return $this->run('channel-delete/index', [
                    'id' => $chat->id,
                ]);
            }

            throw $e;
        }
        // Update chat information
        $chat->setAttributes([
            'type' => $botApiChat->getType(),
            'title' => $botApiChat->getTitle(),
            'username' => $botApiChat->getUsername(),
            'description' => $botApiChat->getDescription(),
        ]);

        if (!$chat->save()) {
            Yii::warning($chat->getErrors());

            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }
        // Bot is the chat member with kicked or left statuses
        if (!$this->getBotApi()->getChatMember($chat->getChatId(), $this->getBot()->getProviderUserId())->isActiveChatMember()) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery(
                    $this->render('../alert', [
                        'alert' => Yii::t('bot', 'Inaccessible, the bot is an inactive member') . '.',
                    ]),
                    true
                )
                ->build();
        }

        $botApiAdministrators = $this->getBotApi()->getChatAdministrators($chat->getChatId());

        $botApiAdministratorsIds = array_map(
            fn ($a) => $a->getUser()->getId(),
            $botApiAdministrators
        );

        $outdatedAdministrators = $chat->getAdministrators()
            ->andWhere([
                'not',
                ['provider_user_id' => $botApiAdministratorsIds],
            ])
            ->all();

        foreach ($outdatedAdministrators as $outdatedAdministrator) {
            try {
                $botApiChatMember = $this->getBotApi()->getChatMember(
                    $chat->getChatId(),
                    $outdatedAdministrator->provider_user_id
                );

                if ($botApiChatMember && $botApiChatMember->isActiveChatMember()) {
                    $chatMember = ChatMember::findOne([
                        'chat_id' => $chat->id,
                        'user_id' => $outdatedAdministrator->id,
                    ]);

                    $chatMember->setAttributes([
                        'status' => $botApiChatMember->getStatus(),
                    ]);

                    $chatMember->save(false);

                    continue;
                } else {
                    ChatMember::deleteAll([
                        'chat_id' => $chat->id,
                        'user_id' => $outdatedAdministrator->id,
                    ]);
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

        foreach ($botApiAdministrators as $botApiAdministrator) {
            $user = User::find()
                ->andWhere([
                    'provider_user_id' => $botApiAdministrator->getUser()->getId(),
                ])
                ->one();

            if (!$user) {
                $user = User::createUser($botApiAdministrator->getUser());
                $user->updateInfo($botApiAdministrator->getUser());
                $user->save();
            }

            $chatMember = ChatMember::findOne([
                'chat_id' => $chat->id,
                'user_id' => $user->id,
            ]);

            if (!$chatMember) {
                $user->link('chats', $chat, ['status' => $botApiAdministrator->getStatus()]);
            } else {
                $chatMember->setAttributes([
                    'status' => $botApiAdministrator->getStatus(),
                ]);

                $chatMember->save(false);
            }
        }
        // User is not in Telegram admin list
        if (!in_array($this->getTelegramUser()->provider_user_id, $botApiAdministratorsIds)) {
            return $this->run('channel/index');
        } else {
            return $this->getResponseBuilder()
                ->answerCallbackQuery(
                    $this->render('index')
                )
                ->build();
        }
    }
}
