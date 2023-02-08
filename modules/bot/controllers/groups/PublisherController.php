<?php

namespace app\modules\bot\controllers\groups;

use app\modules\bot\components\Controller;
use app\modules\bot\models\ChatPublisherPost;

/**
 * Class PublisherController
 *
 * @package app\modules\bot\controllers\groups
 */
class PublisherController extends Controller
{
    /**
     * @return array
     */
    public function actionIndex()
    {
        return [];
    }

    /**
     * @param int $id ChatPublisherPost->id
     * @param string|null $message
     *
     * @return array
     */
    public function actionSendMessage($id = null, $message = null)
    {
        if ($this->getUpdate() && $this->getUpdate()->getMessage() && !$this->getUpdate()->getCallbackQuery()) {
            $this->getResponseBuilder()
                ->deleteMessage()
                ->send();
        }

        $post = ChatPublisherPost::findOne($id ?? $message);

        if (!isset($post)) {
            return [];
        }

        if ((!$chat = $post->chat) || ($chat->id != $this->chat->id) || !$chat->isPublisherOn()) {
            return [];
        }

        $user = $this->getTelegramUser();

        if ($user) {
            $chatMember = $chat->getChatMemberByUserId();

            if (!isset($chatMember) || !$chatMember->isAdministrator()) {
                return [];
            }
        }

        if (!$post->canRepost()) {
            return [];
        }

        $response = $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('view', [
                    'post' => $post,
                ]),
                [],
                [
                    'disablePreview' => true,
                    'replyToMessageId' => $post->topic_id,
                ]
            )
            ->send();

        if ($response) {
            if (isset($isSlowModeOn) && $isSlowModeOn) {
                $chatMember->updateSlowMode($response->getDate());
            }

            $post->sent_at = $response->getDate();
            $post->provider_message_id = $response->getMessageId();
            $post->save(false);
        }

        return $response;
    }
}
