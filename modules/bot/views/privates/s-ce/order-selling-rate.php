<b><?= $selling_currency . '/' . $buying_currency ?> order #1020304050 </b><br/>
<br/>
<?= $selling_currency . '/' . $buying_currency . ' : ' . $selling_rate ?><br/>
<br/>
<?= Yii::t('bot', 'Please send the new maximum') ?> <?= $selling_currency . '/' . $buying_currency ?> <?= Yii::t('bot', 'rate acceptable to you. User offers with the same rate and higher will be offered to you.') ?>
