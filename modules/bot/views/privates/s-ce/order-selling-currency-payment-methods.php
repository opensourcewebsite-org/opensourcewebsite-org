<b><?= $selling . '/' . $buying?> offer</b><br/>
<br/>
<?= Yii::t('bot', 'Payment methods')?> :<br/>

<?  foreach ($paymentMethod as $value) {
		if ($value['name'] == 'Cash') {
			echo ' - ' . $value['name'] . '<br/>';
		}
	}
	foreach ($paymentMethod as $value) {
		if ($value['name'] !== 'Cash')
		echo ' - ' . $value['name'] . '<br/>';
    }
?>