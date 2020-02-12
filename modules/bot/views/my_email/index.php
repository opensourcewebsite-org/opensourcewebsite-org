<b><?= \Yii::t('bot', 'Your Email') ?></b>
<br/><br/>
<? if (isset($email)) { ?>
<?= $email ?>
<? } else { ?>
<?= \Yii::t('bot', 'Your email isn\'t set for now') ?>
<br/><br/>
<?= \Yii::t('bot', 'Please, sent me your email') ?>
<? } ?>
