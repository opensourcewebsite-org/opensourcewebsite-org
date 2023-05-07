<b><?= Yii::t('bot', 'Tip without reply configuration') ?></b><br />
<br />
<?php if ($chat): ?>
<?= Yii::t('bot', 'Group') ?>:
<b><?= $chat->title ?></b>
<?= $chat->username ? ' (@' . $chat->username . ')' : '' ?><br />
<?php endif; ?>