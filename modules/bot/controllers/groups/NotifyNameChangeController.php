<?php

namespace app\modules\bot\controllers\groups;

use app\modules\bot\components\Controller;
use app\modules\bot\models\Chat;
use app\modules\bot\models\User;

/**
 * Class NotifyNameChangeController
 *
 * @package app\modules\bot\controllers\groups
 */
class NotifyNameChangeController extends Controller
{
    /**
     * Action notifies when user changes username
     *
     * @param Chat $chat
     * @param \TelegramBot\Api\Types\User $updateUser
     * @param User $oldUser
     *
     * @return array
     */
    public function actionUsernameChange($chat = null, $updateUser = null, $oldUser = null)
    {
        if ($chat && $updateUser && $oldUser) {
            if ($chat->isNotifyNameChangeOn()) {
                $this->getResponseBuilder()
                    ->sendMessage(
                        $this->render('username-change', [
                            'updateUser' => $updateUser,
                            'user' => $oldUser,
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

    /**
     * Action notifies when user changes name
     *
     * @param Chat $chat
     * @param \TelegramBot\Api\Types\User $updateUser
     * @param User $oldUser
     *
     * @return array
     */
    public function actionNameChange($chat = null, $updateUser = null, $oldUser = null)
    {
        if ($chat && $updateUser && $oldUser) {
            if ($chat->isNotifyNameChangeOn()) {
                $this->getResponseBuilder()
                    ->sendMessage(
                        $this->render('name-change', [
                            'updateUser' => $updateUser,
                            'user' => $oldUser,
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
