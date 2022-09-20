<?php

use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\models\ChatSetting;

?>
<b><?= $chat->title ?></b><br/>
<br/>
<b><?= Yii::t('bot', 'Post') ?>: #<?= $post->id ?> <?= $post->title ?></b><br/>
————<br/>
<br/>
<?= nl2br($post->text) ?><br/>
<?php if ($chatMember && $chat->isMembershipOn() && $chat->membership_tag && $chatMember->hasMembership()) : ?>
<br/>
#<?= $chat->membership_tag ?><br/>
<?php endif; ?>
<br/>
<?= Emoji::RIGHT ?> <?= $user->getFullLink(); ?><br/>
<br/>
————<br/>
