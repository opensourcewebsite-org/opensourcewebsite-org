<?php

namespace app\modules\bot\controllers\groups;

use app\modules\bot\components\Controller;
use app\modules\bot\models\Chat;
use app\modules\bot\models\User;
use PhpParser\Node\Expr\Cast\Object_;
use Yii;

/**
 * Class NotifyNameChangeController
 *
 * @package app\modules\bot\controllers\groups
 */
class NotifyNameChangeController extends Controller
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
    public function actionNotify($chat = null, $changedAttributes = null, $user = null)
    {
        Yii::warning($user);
        if ($chat && $changedAttributes && $user) {
            if ($chat->isNotifyNameChangeOn()) {
                $this->getResponseBuilder()
                    ->sendMessage(
                        $this->render('view', [
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
