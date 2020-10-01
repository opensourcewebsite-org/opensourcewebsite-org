<?php

namespace app\modules\bot\controllers\channels;

use Yii;
use app\modules\bot\components\Controller;
use app\modules\bot\models\Chat;
use app\modules\bot\models\ChatSetting;
use app\modules\bot\models\ChatMember;
use app\modules\bot\models\User as TelegramUser;

/**
* Class SystemMessageController
*
* @package app\modules\bot\controllers\channels
*/
class SystemMessageController extends Controller
{
    /**
    * @return array
    */
    public function actionNewChatMembers()
    {
        return [];
    }

    /**
    * @return array
    */
    public function actionLeftChatMember()
    {
        return [];
    }
}
