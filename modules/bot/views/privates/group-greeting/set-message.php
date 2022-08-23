<b><?= Yii::t('bot', 'Send a message for the greeting') ?>:</b><br/>
<?php if (isset($messageMarkdown) && $messageMarkdown) : ?>
————<br/>
<br/>
<?= nl2br($messageMarkdown) ?><br/>
<br/>
<?php endif; ?>
————<br/>
<?= $this->render('../formatting-options') ?>
