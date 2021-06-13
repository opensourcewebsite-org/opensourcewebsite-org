<?php

/* @var $this yii\web\View */
/* @var $answerMarkdown string */

?>
<b><?= Yii::t('bot', 'Send a message for the answer') ?>:</b><br/>
<br/>
————<br/>
<br/>
<pre><?= $answerMarkdown ?></pre><br/>
<br/>
————<br/>
<br/>
<?= $this->render('../formatting-options') ?>
