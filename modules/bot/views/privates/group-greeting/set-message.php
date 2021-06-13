<?php

/* @var $this yii\web\View */
/* @var $messageMarkdown string */

?>
<b><?= Yii::t('bot', 'Send a message for the greeting') ?>:</b><br/>
<br/>
————<br/>
<br/>
<pre><?= nl2br($messageMarkdown) ?></pre><br/>
<br/>
————<br/>
<br/>
<?= $this->render("../formatting-options") ?>
