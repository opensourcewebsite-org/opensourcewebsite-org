<?php

use app\modules\bot\components\helpers\MessageWithEntitiesConverter;

$markdownText = MessageWithEntitiesConverter::fromHtml($review->text ?? '');
?>
<b><?= Yii::t('bot', 'Send a message for the review') ?>:</b><br/>
<?php if ($markdownText) : ?>
<br/>
————<br/>
<br/>
<?= nl2br($markdownText) ?><br/>
<?php endif; ?>
<br/>
————<br/>
<br/>
<?= $this->render('../formatting-options') ?>
