<?php

namespace app\modules\bot\controllers\groups;

use Yii;
use app\modules\bot\components\Controller;
use app\modules\bot\models\ChatMember;
use app\modules\bot\models\ChatSetting;
use app\modules\bot\models\BotChatCaptcha;
use app\modules\bot\models\BotChatFaqAnswer;

/**
 * Class MessageController
 *
 * @package app\modules\bot\controllers\groups
 */
class MessageController extends Controller
{
    /**
     * @return array
     */
    public function actionIndex()
    {
        $telegramUser = $this->getTelegramUser();
        $chat = $this->getTelegramChat();

        $chatMember = ChatMember::findOne([
            'chat_id' => $chat->id,
            'user_id' => $telegramUser->id,
        ]);

        if (!$chatMember->isAdministrator() && ($chat->join_captcha_status == ChatSetting::STATUS_ON) && !$telegramUser->captcha_confirmed_at) {
            if ($chatMember->role == JoinCaptchaController::ROLE_VERIFIED) {
                $telegramUser->captcha_confirmed_at = time();
                $telegramUser->save(false);
            } else {
                if ($this->getUpdate()->getMessage()) {
                    $this->getBotApi()->deleteMessage(
                        $chat->chat_id,
                        $this->getUpdate()->getMessage()->getMessageId()
                    );
                }

                $botCaptcha = BotChatCaptcha::find()
                    ->where([
                        'chat_id' => $chat->id,
                        'provider_user_id' => $telegramUser->provider_user_id,
                    ])
                    ->one();

                // Forward to captcha if a new member
                if (!isset($botCaptcha)) {
                    return $this->run('join-captcha/show-captcha');
                }
            }
        }

        $deleteMessage = false;

        if (!$chatMember->isAdministrator() && $chat->filter_status == ChatSetting::STATUS_ON) {
            if ($this->getMessage()->getText() !== null) {
                if ($chat->filter_mode == ChatSetting::FILTER_MODE_BLACKLIST) {
                    $phrases = $chat->getBlacklistPhrases()->all();

                    foreach ($phrases as $phrase) {
                        if (mb_stripos($this->getMessage()->getText(), $phrase->text) !== false) {
                            $deleteMessage = true;

                            break;
                        }
                    }
                } else {
                    $deleteMessage = true;

                    $phrases = $chat->getWhitelistPhrases()->all();

                    foreach ($phrases as $phrase) {
                        if (mb_stripos($this->getMessage()->getText(), $phrase->text) !== false) {
                            $deleteMessage = false;

                            break;
                        }
                    }
                }

                if ($deleteMessage && $this->getUpdate()->getMessage()) {
                    $this->getBotApi()->deleteMessage(
                        $chat->getChatId(),
                        $this->getUpdate()->getMessage()->getMessageId()
                    );
                }
            }
        }

        if (!$deleteMessage) {
            if ($chat->faq_status == ChatSetting::STATUS_ON) {
                if (($text = $this->getMessage()->getText()) !== null) {
                    if (strtolower($text) == 'faq') {
                        return $this->run('faq/show-chat-link');
                    }

                    $question = $chat->getQuestionPhrases()
                        ->where([
                            'text' => $text,
                        ])
                        ->andWhere([
                            'not', ['answer' => null],
                        ])
                        ->one();

                    if (isset($question)) {
                        return $this->run('faq/show-answer', [
                                'questionId' => $question->id,
                            ]);
                    }
                }
            }
        }

        return [];
    }
}
