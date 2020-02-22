<b><?= \Yii::t('bot', 'Your Birthday') ?></b>
<br/><br/>
<?php if (isset($birthday)) : ?>
<?= $birthday ?>
<?php else : ?>
<?= \Yii::t('bot', 'Unknown') ?>. <?= \Yii::t('bot', 'Please, send your birthday in format DD.MM.YYYY') ?>.
<?php endif; ?>
