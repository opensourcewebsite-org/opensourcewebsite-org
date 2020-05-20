<?php

namespace app\modules\bot\controllers\privates;

use Yii;

use app\modules\bot\components\Controller;
use app\modules\bot\models\Chat;
use app\modules\bot\models\ChatMember;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\models\User;
use yii\helpers\ArrayHelper;
use TelegramBot\Api\HttpException;

/**
 * Class AdminChatController
 *
 * @package app\controllers\bot
 */
class AdminChatRefreshController extends Controller
{
    /**
     * @return array
     */
    public function actionIndex($chatId = null)
    {
        $result = [];
        $isChatExists = true;

        if ($chatId) {
            $chat = Chat::findOne($chatId);
        }

        $isChatExists = !empty($chat);

        if ($isChatExists) {
            try {
                $this->getBotApi()->getChat($chat->chat_id);
            } catch (HttpException $e) {
                if ($e->getCode() == 400) {
                    $isChatExists = false;
                    $chat->unlinkAll('phrases', true);
                    $chat->unlinkAll('settings', true);
                    $chat->unlinkAll('users', true);
                    $chat->delete();
                }
            }
        }

        if ($isChatExists) {
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

            if ($currentUserIsAdministrator) {
                $result = $this->getResponseBuilder()($this->getUpdate())
                ->answerCallbackQuery(
                    $this->render('index'),
                    true
                )
                ->build();
            }
        }

        if (!$result) {
            $result = $this->run('admin/index');
        }
        return $result;
    }
}
