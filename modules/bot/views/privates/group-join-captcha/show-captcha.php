<b><?= $chat->title ?></b><?= $chat->username ? ' (@' . $chat->username . ')' : '' ?><br/>
<?php if ($message) : ?>
<br/>
<?= nl2br($message) ?><br/>
<?php endif; ?>
<br/>
<?= Yii::t('bot', 'To join this group, read the group rules and press {0}', '👍') ?><br/>
