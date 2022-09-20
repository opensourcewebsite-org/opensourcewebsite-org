<b><?= Yii::t('bot', 'Send a message for the answer') ?>:</b><br/>
<?php if (isset($answerMarkdown) && $answerMarkdown) : ?>
————<br/>
<?= nl2br($answerMarkdown) ?><br/>
<?php endif; ?>
————<br/>
<?= $this->render('../formatting-options') ?>
