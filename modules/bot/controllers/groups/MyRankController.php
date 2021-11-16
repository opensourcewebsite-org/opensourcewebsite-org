<?php

namespace app\modules\bot\controllers\groups;

use Yii;
use app\modules\bot\components\Controller;
use app\modules\bot\components\helpers\Emoji;

/**
 * Class MyRankController
 *
 * @package app\modules\bot\controllers\groups
 */
class MyRankController extends Controller
{
    /**
     * @return array
     */
    public function actionIndex()
    {
        $user = $this->getUser();

        $params = [
            'user' => $user,
        ];

        return $this->getResponseBuilder()
        ->sendMessage(
            $this->render('index', $params),
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
