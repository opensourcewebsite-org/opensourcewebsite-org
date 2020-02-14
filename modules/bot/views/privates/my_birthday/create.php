<?php if ($success) : ?>
    <?= \Yii::t('bot', 'Birthday successfully changed') ?>
<?php else : ?>
<?= \Yii::t('bot', 'Please, enter your birthday in format DD.MM.YYYY') ?>
<?php endif; ?>
