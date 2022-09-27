<?php

namespace app\modules\bot\controllers\groups;

use app\components\helpers\ArrayHelper;
use app\modules\bot\components\Controller;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\helpers\ExternalLink;
use app\modules\bot\models\ChatMarketplacePost;
use app\modules\bot\models\ChatPhrase;
use Yii;

/**
 * Class MarketplaceController
 *
 * @package app\modules\bot\controllers\groups
 */
class MarketplaceController extends Controller
{
    /**
     * @return array
     */
    public function actionIndex()
    {
        return [];
    }

    /**
     * @param int $id ChatMarketplacePost->id
     * @param string|null $message
     * @return array
     */
    public function actionUpdateMessage($id = null, $message = null)
    {
        if ($this->getUpdate() && $this->getUpdate()->getMessage() && !$this->getUpdate()->getCallbackQuery()) {
            $this->getResponseBuilder()
                ->deleteMessage()
                ->send();
        }

        $post = ChatMarketplacePost::findOne($id ?? $message);

        if (!isset($post) || !$post->getProviderMessageId()) {
            return [];
        }

        if ((!$chat = $post->chat) || ($chat->id != $this->chat->id) || !$chat->isMarketplaceOn()) {
            return [];
        }

        $user = $this->getTelegramUser();

        if ($user) {
            $chatMember = $chat->getChatMemberByUserId();

            if (!$chatMember || ($post->getChatMemberId() != $chatMember->id)) {
                return [];
            }
        }

        $chatMember = $post->chatMember;

        if (!$post->canRepost()) {
            return [];
        }

        $response = $this->prepareResponseBuilder($post, false)->send();

        if ($response) {
            //$post->sent_at = $response->getDate();
            //$post->save(false);
        }

        return $response;
    }

    /**
     * @param int $id ChatMarketplacePost->id
     * @param string|null $message
     * @return array
     */
    public function actionSendMessage($id = null, $message = null)
    {
        if ($this->getUpdate() && $this->getUpdate()->getMessage() && !$this->getUpdate()->getCallbackQuery()) {
            $this->getResponseBuilder()
                ->deleteMessage()
                ->send();
        }

        $post = ChatMarketplacePost::findOne($id ?? $message);

        if (!isset($post)) {
            return [];
        }

        if ((!$chat = $post->chat) || ($chat->id != $this->chat->id) || !$chat->isMarketplaceOn()) {
            return [];
        }

        $user = $this->getTelegramUser();

        if ($user) {
            $chatMember = $chat->getChatMemberByUserId();

            if (!$chatMember || ($post->getChatMemberId() != $chatMember->id)) {
                return [];
            }
        }

        $chatMember = $post->chatMember;

        if (!$post->canRepost()) {
            return [];
        }

        if ($chat->isLimiterOn() && !$chatMember->isCreator() && !$chatMember->hasLimiter()) {
            return [];
        }

        if (!$chatMember->canUseMarketplace()) {
            return [];
        }

        if ($chat->isSlowModeOn() && !$chatMember->isCreator()) {
            if (!$chatMember->checkSlowMode()) {
                return [];
            } else {
                $isSlowModeOn = true;
            }
        }

        $response = $this->prepareResponseBuilder($post)->send();

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

    /**
     * @param ChatMarketplacePost $post
     * @param int $isNewMessage
     * @return array
     */
    private function prepareResponseBuilder(ChatMarketplacePost $post, bool $isNewMessage = true)
    {
        $chat = $post->chat;
        $chatMember = $post->chatMember;
        $user = $post->user;

        $buttons = [];
        $tags = [];

        $tags = ArrayHelper::getColumn($chatMember->getPhrases(ChatPhrase::TYPE_MARKETPLACE_TAGS)->asArray()->all(), 'text');

        if ($membershipTag = $chatMember->getMembershipTag()) {
            $tags = ArrayHelper::merge([
                $membershipTag,
            ], $tags);
        }

        $buttons[] = [
            [
                'url' => $user->getLink(),
                'text' => Yii::t('bot', 'Contact'),
            ],
        ];

        $buttons[] = [
            [
                'url' => $chatMember->getReviewsLink(),
                'text' => Yii::t('bot', 'Reviews') . ($chatMember->getPositiveReviewsCount() ? ' ' . Emoji::LIKE . ' ' . $chatMember->getPositiveReviewsCount() : '') . ($chatMember->getNegativeReviewsCount() ? ' ' . Emoji::DISLIKE . ' ' . $chatMember->getNegativeReviewsCount() : ''),
            ],
        ];

        if ($links = $chatMember->marketplaceLinks) {
            foreach ($links as $link) {
                if ($link->url && $link->title) {
                    $buttons[] = [
                        [
                            'url' => $link->url,
                            'text' => $link->title,
                        ],
                    ];
                }
            }
        }

        if ($isNewMessage) {
            return $this->getResponseBuilder()
                ->sendMessage(
                    $this->render('view', [
                        'chat' => $chat,
                        'post' => $post,
                        'chatMember' => $chatMember,
                        'user' => $user,
                        'tags' => $tags,
                    ]),
                    $buttons,
                    [
                        'disablePreview' => true,
                    ]
                );
        } else {
            return $this->getResponseBuilder()
                ->editMessage(
                    $post->getProviderMessageId(),
                    $this->render('view', [
                        'chat' => $chat,
                        'post' => $post,
                        'chatMember' => $chatMember,
                        'user' => $user,
                        'tags' => $tags,
                    ]),
                    $buttons,
                    [
                        'disablePreview' => true,
                    ]
                );
        }
    }
}
