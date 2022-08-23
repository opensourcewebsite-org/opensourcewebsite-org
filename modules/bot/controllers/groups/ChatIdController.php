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

        return $this->getResponseBuilder()
            ->sendMessage(
                $this->render('index', [
                    'chat' => $chat,
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
