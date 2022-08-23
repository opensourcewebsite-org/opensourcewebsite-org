<b><?= Yii::t('bot', 'Send a text for the post') ?>:</b><br/>
<?php if ($chat->marketplace_text_hint) : ?>
<br/>
<?= nl2br($chat->marketplace_text_hint) ?><br/>
<?php endif; ?>
<?php if (isset($messageMarkdown) && $messageMarkdown) : ?>
————<br/>
<br/>
<?= nl2br($messageMarkdown) ?><br/>
<br/>
<?php endif; ?>
————<br/>
<?= $this->render('../formatting-options') ?>
