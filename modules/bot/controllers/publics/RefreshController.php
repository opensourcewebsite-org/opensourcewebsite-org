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
        $tmAdmins = $this->getBotApi()->getChatAdministrators($chat->chat_id);
        $tmAdminsIds = ArrayHelper::getColumn($tmAdmins, function ($el) {
            return $el->getUser()->getId();
        });

        $currentUser = $this->getTelegramUser();
        $currentUserIsAdmin = false;
        if (in_array($currentUser->provider_user_id, $tmAdminsIds)) {
            $currentUserIsAdmin = true;
        }

        $curAdmins = $chat->getAdministrators()->all();
        $curAdminsIndexdByIds = ArrayHelper::index($curAdmins, function ($el) {
            return $el->provider_user_id;
        });
        $curAdminsIds = array_keys($curAdminsIndexdByIds);

        $outdatedAdmins = $chat->getAdministrators()
                            ->andWhere(['not',['provider_user_id'=>$tmAdminsIds]])
                            ->all();

        foreach ($outdatedAdmins as $outdatedAdmin) {
            $telegramChatMember = $this->getBotApi()->getChatMember(
                $chat->chat_id,
                $outdatedAdmin->provider_user_id
            );
            if ($telegramChatMember->isActualChatMember()) {
                $chatMember = ChatMember::findOne(['chat_id' => $chat->id, 'user_id' => $outdatedAdmin->id]);
                $chatMember->setAttributes([
                    'status' => $telegramChatMember->getStatus(),
                ]);
                $chatMember->save();
                continue;
            }
            $chat->unlink('users', $outdatedAdmin, true);
        }

        $users = ArrayHelper::index(User::find(['provider_user_id' => $tmAdminsIds])->all(), 'provider_user_id');
        foreach ($tmAdmins as $tmAdmin) {
            $user = isset($users[$tmAdmin->getUser()->getId()]) ? $users[$tmAdmin->getUser()->getId()] : null;
            if (!isset($user)) {
                $user = User::createUser($tmAdmin->getUser());
                $user->updateInfo($tmAdmin->getUser());
            }
            if (!in_array($user->provider_user_id, $curAdminsIds)) {
                $user->link('chats', $chat, ['status' => $tmAdmin->getStatus()]);
            }
        }

        if (!$currentUserIsAdmin) {
            $this->getState()->setName(self::createRoute('index'));
            return [];
        }

        return ResponseBuilder::fromUpdate($this->getUpdate())
                ->editMessageTextOrSendMessage(
                    $this->render('index')
                )
                ->build();
    }
}
