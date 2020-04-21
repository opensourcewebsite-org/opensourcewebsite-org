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
        $telegramAdmins = $this->getBotApi()->getChatAdministrators($chat->chat_id);
        $telegramAdminsIds = ArrayHelper::getColumn($telegramAdmins, function ($telegramAdmin) {
            return $telegramAdmin->getUser()->getId();
        });

        $currentUser = $this->getTelegramUser();
        $currentUserIsAdmin = false;
        if (in_array($currentUser->provider_user_id, $telegramAdminsIds)) {
            $currentUserIsAdmin = true;
        }

        $curAdmins = $chat->getAdministrators()->all();
        $curAdminsIndexdByIds = ArrayHelper::index($curAdmins, function ($curAdmins) {
            return $curAdmins->provider_user_id;
        });
        $curAdminsIds = array_keys($curAdminsIndexdByIds);

        $outdatedAdmins = $chat->getAdministrators()
                            ->andWhere(['not',['provider_user_id'=>$telegramAdminsIds]])
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

        $users = ArrayHelper::index(User::find(['provider_user_id' => $telegramAdminsIds])->all(), 'provider_user_id');
        foreach ($telegramAdmins as $telegramAdmin) {
            $user = isset($users[$telegramAdmin->getUser()->getId()]) ? $users[$telegramAdmin->getUser()->getId()] : null;
            if (!isset($user)) {
                $user = User::createUser($telegramAdmin->getUser());
                $user->updateInfo($telegramAdmin->getUser());
            }
            if (!in_array($user->provider_user_id, $curAdminsIds)) {
                $user->link('chats', $chat, ['status' => $telegramAdmin->getStatus()]);
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
