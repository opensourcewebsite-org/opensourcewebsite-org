<?php

namespace app\modules\bot\controllers\groups;

use app\modules\bot\components\Controller;
use app\modules\bot\controllers\privates\GroupGuestController;
use app\modules\bot\models\ChatMember;
use app\modules\bot\models\User;
use Yii;

/**
 * Class WarnController
 *
 * @package app\modules\bot\controllers\groups
 */
class WarnController extends Controller
{
    /**
     * @return array
     */
    public function actionIndex()
    {
        if ($this->getUpdate() && $this->getUpdate()->getMessage() && !$this->getUpdate()->getCallbackQuery()) {
            $this->getResponseBuilder()
                ->deleteMessage()
                ->send();
        }

        $user = $this->getTelegramUser();
        $chat = $this->getTelegramChat();

        $chatMember = ChatMember::findOne([
            'chat_id' => $chat->id,
            'user_id' => $user->id,
        ]);

        if ($chatMember->isActiveAdministrator() || $chatMember->isAnonymousAdministrator()) {
            if ($replyMessage = $this->getMessage()->getReplyToMessage()) {
                $this->getBotApi()->deleteMessage(
                    $chat->getChatId(),
                    $replyMessage->getMessageId()
                );

                $replyUser = User::findOne([
                    'provider_user_id' => $replyMessage->getFrom()->getId(),
                ]);

                if ($replyUser) {
                    $replyChatMember = ChatMember::findOne([
                        'chat_id' => $chat->id,
                        'user_id' => $replyUser->id,
                    ]);
                }

                if (!isset($replyChatMember) || !$replyChatMember->isAdministrator()) {
                    $replyUser->sendMessage(
                        $this->render('/privates/warning-warn-chat-member', [
                            'chat' => $chat,
                        ]),
                        [
                            [
                                [
                                    'callback_data' => GroupGuestController::createRoute('view', [
                                        'id' => $chat->id,
                                    ]),
                                    'text' => Yii::t('bot', 'Group View'),
                                ],
                            ],
                        ]
                    );
                }
            }
        }

        return [];
    }
}
