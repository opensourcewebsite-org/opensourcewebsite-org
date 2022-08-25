<?php if (!$chat->membership_tag) : ?>
<b><?= Yii::t('bot', 'Premium members') ?></b><br/>
<?php else : ?>
<b><?= Yii::t('bot', 'Members status') ?></b>: #<?= $chat->membership_tag ?><br/>
<?php endif; ?>
<?php foreach ($members as $member) : ?>
<br/>
• <?= $member->user->getFullLink(); ?><br/>
<?php endforeach; ?>
