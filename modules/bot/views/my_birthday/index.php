<b><?= \Yii::t('bot', 'Your Birthday') ?></b>
<br/><br/>
<? if ($birthday) : ?>
<?= $birthday ?>
<? else : ?>
<?= \Yii::t('bot', 'We don\'t know your birthday yet. Please, enter your birthday in format DD.MM.YYYY') ?>
<? endif; ?>
