<b><?= Yii::t('bot', 'Send a message for the text hint') ?>:</b><br/>
<br/>
<i><?= Yii::t('bot', 'This information is shown when members set the text of posts') ?>.</i><br/>
<?php if (isset($messageMarkdown) && $messageMarkdown) : ?>
————<br/>
<br/>
<?= nl2br($messageMarkdown) ?><br/>
<br/>
<?php endif; ?>
————<br/>
<?= $this->render('../formatting-options') ?>
