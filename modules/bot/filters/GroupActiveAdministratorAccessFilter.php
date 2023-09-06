<?php

namespace app\modules\bot\filters;

use app\modules\bot\models\Chat;
use Yii;
use yii\base\ActionFilter;

class GroupActiveAdministratorAccessFilter extends ActionFilter
{
    public $chatId;

    public function beforeAction($action)
    {
        if (($chatId = Yii::$app->request->get('chatId')) || ($chatId = Yii::$app->request->get('id'))) {
            $chat = Chat::findOne($chatId);

            if (isset($chat) && $chat->isGroup()) {
                $chatMember = $chat->getChatMemberByUserId();

                if (isset($chatMember) && $chatMember->isActiveAdministrator()) {
                    Yii::$app->cache->set('chat', $chat);
                    Yii::$app->cache->set('chatMember', $chatMember);

                    return true;
                }
            }
        }

        Yii::$app->getModule('bot')->getResponseBuilder()
            ->answerCallbackQuery()
            ->send();

        return false;
    }
}
