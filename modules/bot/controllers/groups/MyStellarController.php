<?php

namespace app\modules\bot\controllers\groups;

use Yii;
use app\modules\bot\components\Controller;
use app\modules\bot\components\helpers\Emoji;
use app\models\User;
use app\models\UserStellar;

/**
 * Class MyStellarController
 *
 * @package app\modules\bot\controllers\groups
 */
class MyStellarController extends Controller
{
    /**
     * @return array
     */
    public function actionIndex()
    {
        $user = $this->getUser();

        if (isset($user->stellar) && $user->stellar->isConfirmed()) {
            return $this->getResponseBuilder()
                ->sendMessage(
                    $this->render('index', [
                        'stellar' => $user->stellar,
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

        return $this->getResponseBuilder()
            ->answerCallbackQuery()
            ->build();
    }
}
