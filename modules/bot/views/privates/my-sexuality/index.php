<b><?= Yii::t('bot', 'Your Sexuality') ?></b><br/>
<br/>
<?php if (isset($sexuality)) : ?>
<?= Yii::t('bot', $sexuality) ?>
<?php else : ?>
<?= Yii::t('bot', 'Unknown') ?>
<?php endif; ?>
