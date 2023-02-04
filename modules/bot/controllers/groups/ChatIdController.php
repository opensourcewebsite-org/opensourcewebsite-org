<?php

namespace app\modules\bot\controllers\groups;

use app\modules\bot\components\Controller;
use Yii;

/**
 * Class ChatIdController
 *
 * @package app\modules\bot\controllers\groups
 */
class ChatIdController extends Controller
{
    /**
     * @return array
     */
    public function actionIndex()
    {
        $chat = $this->getTelegramChat();
        $topicId = $this->getMessage()->getMessageThreadId();

        return $this->getResponseBuilder()
            ->sendMessage(
                $this->render('index', [
                    'chat' => $chat,
                    'topicId' => $topicId,
                ]),
                [],
                [
                    'disablePreview' => true,
                    'disableNotification' => true,
                    'replyToMessageId' => $this->getMessage()->getMessageId(),
                ]
            )
            ->build();
    }
}
