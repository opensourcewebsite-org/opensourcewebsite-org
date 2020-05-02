<b><?= $buying_currency . '/' . $selling_currency ?> order #1020304050</b><br/>
<br/>
<?= $buying_currency . '/' . $selling_currency . ' : ' . $buying_rate ?><br/>
<br/>
<?= Yii::t('bot', 'Please send the new maximum ') ?><?= $buying_currency . '/' . $selling_currency ?><?= Yii::t('bot', ' rate acceptable to you. User offers with the same rate and higher will be offered to you.') ?>
