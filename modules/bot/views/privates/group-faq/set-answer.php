<?php

/* @var $this yii\web\View */
/* @var $answerMarkdown string */

?>
<b><?= Yii::t('bot', 'Send a message for the answer') ?>:</b><br/>
<br/>
————<br/>
<br/>
<?= nl2br($answerMarkdown) ?><br/>
<br/>
————<br/>
<br/>
<?= $this->render('../formatting-options') ?>
