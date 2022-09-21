<?php

namespace app\modules\bot\controllers\groups;

use app\modules\bot\components\Controller;
use Yii;

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
        $user = $this->getTelegramUser();

        return $this->getResponseBuilder()
            ->sendMessage(
                $this->render('index', [
                    'user' => $user,
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
