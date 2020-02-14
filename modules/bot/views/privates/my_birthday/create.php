<?php if ($success) : ?>
<?= \Yii::t('bot', 'Birthday successfully changed') ?>
<?php else : ?>
<?= \Yii::t('bot', 'Please, send your birthday in format DD.MM.YYYY') ?>.
<?php endif; ?>
