<?php

namespace app\modules\bot\controllers\groups;

use app\modules\bot\components\Controller;
use app\modules\bot\models\ChatMember;
use app\modules\bot\models\User;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * Class RefreshController
 *
 * @package app\modules\bot\controllers\groups
 */
class RefreshController extends Controller
{
    // TODO fix role for creator
    /**
     * @return array
     */
    public function actionIndex()
    {
        $chat = $this->getTelegramChat();

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

                if ($botApiChatMember && $botApiChatMember->isActualChatMember()) {
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
                $user->link('chats', $chat, [
                    'status' => $botApiAdministrator->getStatus(),
                    'role' => ($botApiAdministrator->getStatus() == ChatMember::STATUS_CREATOR) ? ChatMember::ROLE_ADMINISTRATOR : ChatMember::ROLE_MEMBER,
                ]);
            } else {
                $chatMember->setAttributes([
                    'status' => $botApiAdministrator->getStatus(),
                    'role' => ($botApiAdministrator->getStatus() == ChatMember::STATUS_CREATOR) ? ChatMember::ROLE_ADMINISTRATOR : $chatMember->role,
                ]);

                $chatMember->save(false);
            }
        }

        return $this->getResponseBuilder()
                ->deleteMessage()
                ->build();
    }
}
