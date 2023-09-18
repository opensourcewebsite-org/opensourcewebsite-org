<?php

namespace app\modules\bot\filters;

use app\modules\bot\models\Chat;
use Yii;

/**
 * Class ChatActiveAdministratorAccessFilter
 *
 * @package app\modules\bot\filters
 */
class ChatActiveAdministratorAccessFilter extends \yii\base\ActionFilter
{
    public $chatId;

    public function beforeAction($action)
    {
        if (($chatId = Yii::$app->request->get('chatId')) || ($chatId = Yii::$app->request->get('id'))) {
            $chat = Chat::findOne($chatId);

            if (isset($chat) && ($chat->isGroup() || $chat->isChannel())) {
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
