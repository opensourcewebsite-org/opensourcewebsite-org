<?php

namespace app\modules\bot\controllers\groups;

use app\modules\bot\components\Controller;
use app\modules\bot\models\Chat;
use app\modules\bot\models\User;
use Yii;

/**
 * Class NotifierController
 *
 * @package app\modules\bot\controllers\groups
 */
class NotifierController extends Controller
{
    /**
     * Action notifies when user changes username/name
     *
     * @param Chat $chat
     * @param mixed $changedAttributes
     * @param User $user
     *
     * @return array
     */
    public function actionIndex($chat = null, $changedAttributes = null, $user = null)
    {
        if ($chat && $changedAttributes && $user) {
            if ($chat->isNotifyNameChangeOn()) {
                $this->getResponseBuilder()
                    ->sendMessage(
                        $this->render('index', [
                            'changedAttributes' => $changedAttributes,
                            'user' => $user,
                        ]),
                        [],
                        [
                            'disablePreview' => true,
                            'disableNotification' => true,
                        ]
                    )
                    ->send();
            }
        }

        return [];
    }
}
