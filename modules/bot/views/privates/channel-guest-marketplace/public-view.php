<?php

use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\models\ChatSetting;

?>
<?= nl2br($post->text) ?><br/>
<?php if ($chatMember && ($chat->membership_status == ChatSetting::STATUS_ON) && $chat->membership_tag && $chatMember->hasMembership()) : ?>
<br/>
#<?= $chat->membership_tag ?><br/>
<?php endif; ?>
<br/>
<?= Emoji::RIGHT ?> <?= $user->getFullLink(); ?><br/>
