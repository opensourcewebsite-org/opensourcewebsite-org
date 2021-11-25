<?php

/* @var $this yii\web\View */
/* @var $answerMarkdown string */

?>
<b><?= Yii::t('bot', 'Send a message for the answer') ?>:</b><br/>
<?php if (isset($answerMarkdown) && $answerMarkdown) : ?>
<br/>
————<br/>
<br/>
<?= nl2br($answerMarkdown) ?><br/>
<?php endif; ?>
<br/>
————<br/>
<br/>
<?= $this->render('../formatting-options') ?>
