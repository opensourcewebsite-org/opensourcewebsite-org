<?php

namespace app\modules\bot\controllers\publics;

use app\modules\bot\components\response\commands\SendMessageCommand;
use app\modules\bot\components\Controller as Controller;
use app\modules\bot\components\response\ResponseBuilder;
use app\modules\bot\models\ChatMember;
use app\modules\bot\models\User;
use yii\helpers\ArrayHelper;

/**
 * Class FilterChatController
 *
 * @package app\controllers\bot
 */
class RefreshController extends Controller
{
    /**
     * @return array
     */
    public function actionIndex()
    {
        $chat = $this->getTelegramChat();
        $telegramAdministrators = $this->getBotApi()->getChatAdministrators($chat->chat_id);
        $telegramAdministratorsIds = ArrayHelper::getColumn($telegramAdministrators, function ($telegramAdministrator) {
            return $telegramAdministrator->getUser()->getId();
        });

        $currentUser = $this->getTelegramUser();
        $currentUserIsAdministrator = false;
        if (in_array($currentUser->provider_user_id, $telegramAdministratorsIds)) {
            $currentUserIsAdministrator = true;
        }

        $curAdministrators = $chat->getAdministrators()->all();
        $curAdministratorsIndexdByIds = ArrayHelper::index($curAdministrators, function ($curAdministrator) {
            return $curAdministrator->provider_user_id;
        });
        $curAdministratorsIds = array_keys($curAdministratorsIndexdByIds);

        $outdatedAdministrators = $chat->getAdministrators()
                            ->andWhere(['not',['provider_user_id'=>$telegramAdministratorsIds]])
                            ->all();

        foreach ($outdatedAdministrators as $outdatedAdministrator) {
            $telegramChatMember = $this->getBotApi()->getChatMember(
                $chat->chat_id,
                $outdatedAdministrator->provider_user_id
            );
            if ($telegramChatMember->isActualChatMember()) {
                $chatMember = ChatMember::findOne(['chat_id' => $chat->id, 'user_id' => $outdatedAdministrator->id]);
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
            $user = isset($users[$telegramAdministrator->getUser()->getId()]) ? $users[$telegramAdministrator->getUser()->getId()] : null;
            if (!isset($user)) {
                $user = User::createUser($telegramAdministrator->getUser());
                $user->updateInfo($telegramAdministrator->getUser());
            }
            if (!in_array($user->provider_user_id, $curAdministratorsIds)) {
                $user->link('chats', $chat, ['status' => $telegramAdministrator->getStatus()]);
            }
        }

        $response = $this->getResponseBuilder()
                ->deleteMessage();

        if ($currentUserIsAdministrator) {
            $response->editMessageTextOrSendMessage(
                $this->render('index')
            );
        }

        return $response->build();
    }
}
