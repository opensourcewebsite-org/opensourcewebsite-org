<b><?= $name ?></b><br/>
<br/>
<?php if ($description) : ?>
<?= nl2br($description) ?><br/>
<br/>
<?php endif; ?>
<?php if ($address) : ?>
<b><?= Yii::t('bot', 'Address') ?>:</b> <?= $address ?><br/>
<br/>
<?php endif; ?>
<?php if ($url) : ?>
<b><?= Yii::t('bot', 'Website') ?>:</b> <?= $url ?><br/>
<br/>
<?php endif; ?>
