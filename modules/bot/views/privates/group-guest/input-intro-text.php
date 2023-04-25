<?php

use app\modules\bot\components\helpers\MessageWithEntitiesConverter;

$markdownText = MessageWithEntitiesConverter::fromHtml($chatMember->intro ?? '');
?>
<b><?= Yii::t('bot', 'Send a message for the intro') ?>:</b><br/>
<?php if ($markdownText) : ?>
————<br/>
<?= nl2br($markdownText) ?><br/>
<?php endif; ?>
————<br/>
<?= $this->render('../formatting-options') ?>
