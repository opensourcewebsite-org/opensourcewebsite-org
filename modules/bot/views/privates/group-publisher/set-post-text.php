<b><?= Yii::t('bot', 'Send a text for the post') ?>:</b><br/>
<?php if (isset($messageMarkdown) && $messageMarkdown) : ?>
————<br/>
<?= nl2br($messageMarkdown) ?><br/>
<?php endif; ?>
————<br/>
<?= $this->render('../formatting-options') ?>
