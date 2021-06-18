<?php

/* @var $this yii\web\View */
/* @var $messageMarkdown string */

?>
<b><?= Yii::t('bot', 'Send a message for the greeting') ?>:</b><br/>
<br/>
<?php if (isset($messageMarkdown) && $messageMarkdown) : ?>
————<br/>
<br/>
<?= nl2br($messageMarkdown) ?><br/>
<br/>
<?php endif; ?>
————<br/>
<br/>
<?= $this->render("../formatting-options") ?>
