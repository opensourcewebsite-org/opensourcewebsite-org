<b><?= Yii::t('bot', 'Send a text for the post') ?>:</b><br/>
<?php if (isset($messageMarkdown) && $messageMarkdown) : ?>
————<br/>
<?= nl2br($messageMarkdown) ?><br/>
<?php endif; ?>
————<br/>
<?= $this->render('../formatting-options') ?>
<br/>
<i><?= Yii::t('bot', 'Tags and @username will be automatically added to the end of the text') ?>.</i><br/>
