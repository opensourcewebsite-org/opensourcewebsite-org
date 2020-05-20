<?php

namespace app\modules\bot\components\api\Types;

use app\modules\bot\models\ChatMember as ChatMemberModel;
use TelegramBot\Api\Types\User;

class ChatMember extends \TelegramBot\Api\Types\ChatMember
{
    protected static $map = [
        'user' => User::class,
        'status' => true,
        'until_date' => true,
        'can_be_edited' => true,
        'can_change_info' => true,
        'can_post_messages' => true,
        'can_edit_messages' => true,
        'can_delete_messages' => true,
        'can_invite_users' => true,
        'can_restrict_members' => true,
        'can_pin_messages' => true,
        'can_promote_members' => true,
        'can_send_messages' => true,
        'can_send_media_messages' => true,
        'can_send_other_messages' => true,
        'can_add_web_page_previews' => true,
        'is_member' => true
    ];

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
    public function isActualChatMember()
    {
        if (($this->getStatus() == ChatMemberModel::STATUS_LEFT) || ($this->getStatus() == ChatMemberModel::STATUS_KICKED) || (($this->getStatus() == ChatMemberModel::STATUS_RESTRICTED) && !$this->isMember())) {
            return false;
        }
        return true;
    }
}
