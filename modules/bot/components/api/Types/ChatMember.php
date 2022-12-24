<?php

namespace app\modules\bot\components\api\Types;

use app\modules\bot\models\ChatMember as ChatMemberModel;

class ChatMember extends \TelegramBot\Api\Types\ChatMember
{
    /**
     * Optional. Restricted only. True, if the user is a member of the chat at the moment of the request
     *
     * @var bool
     */
    protected $isMember;

    /**
     * @return bool
     */
    public function isMember()
    {
        return $this->isMember;
    }

    /**
     * @param bool $isMember
     */
    public function setIsMember($isMember)
    {
        $this->isMember = $isMember;
    }

    /**
     * @return bool
     */
    public function isActiveChatMember()
    {
        if (($this->getStatus() == ChatMemberModel::STATUS_LEFT) || ($this->getStatus() == ChatMemberModel::STATUS_KICKED) || (($this->getStatus() == ChatMemberModel::STATUS_RESTRICTED) && !$this->isMember())) {
            return false;
        }

        return true;
    }
}
