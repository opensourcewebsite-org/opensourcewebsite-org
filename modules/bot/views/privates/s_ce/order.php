<b><?= $selling_currency . '/' . $buying_currency?> order #1020304050</b><br/>
<b><?= ($optional_name !== '' ? Yii::t('bot', 'Optional name') . ': ' . $optional_name : '');?></b><br/>
<br/>
<?= Yii::t('bot', 'Sell') ?>: <b><?= $selling_currency ?></b><br/>
<?= Yii::t('bot', 'Amount') ?>: <?= $selling_currency_min_amount . ' - ' . $selling_currency_max_amount ?><br/>
<?= Yii::t('bot', 'Payment methods') ?>:<br/>
<?php  foreach ($sellingPaymentMethod as $value) {
		if ($value['name'] == 'Cash') {
			echo ' - ' . $value['name'] . '<br/>';
		}
	}
	foreach ($sellingPaymentMethod as $value) {
		if ($value['name'] !== 'Cash') {
			echo ' - ' . $value['name'] . '<br/>';
		}
    }
?>
<br/>
<?= Yii::t('bot', 'Buy')?>: <b><?= $buying_currency?></b><br/>
<?= Yii::t('bot', 'Payment methods') ?>:<br/>
<?php  foreach ($buyingPaymentMethod as $value) {
		if ($value['name'] == 'Cash') {
			echo ' - ' . $value['name'] . '<br/>';
		}
	}
	foreach ($buyingPaymentMethod as $value) {
		if ($value['name'] !== 'Cash') {
			echo ' - ' . $value['name'] . '<br/>';
		}
    }
?>
<br/>
<?= Yii::t('bot', 'This post is active for 14 more days. Check this post again before this term to automatically renew this.') ?>
