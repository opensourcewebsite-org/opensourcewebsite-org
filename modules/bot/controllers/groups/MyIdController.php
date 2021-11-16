<?php

namespace app\modules\bot\controllers\groups;

use Yii;
use app\modules\bot\components\Controller;
use app\modules\bot\components\helpers\Emoji;

/**
 * Class MyIdController
 *
 * @package app\modules\bot\controllers\groups
 */
class MyIdController extends Controller
{
    /**
     * @return array
     */
    public function actionIndex()
    {
        $telegramUser = $this->getTelegramUser();
        $user = $this->getUser();

        return $this->getResponseBuilder()
            ->sendMessage(
                $this->render('index', [
                    'user' => $user,
                    'telegramUser' => $telegramUser,
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
