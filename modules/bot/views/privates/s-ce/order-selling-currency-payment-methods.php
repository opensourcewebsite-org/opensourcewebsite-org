<b><?php echo $selling . '/' . $buying?> offer</b><br/>
<br/>
<?php echo Yii::t('bot', 'Payment methods')?> :<br/>

<?php
foreach ($paymentMethod as $value) {
    if ($value['name'] == 'Cash') {
        echo(' - ' . $value['name'] . '<br/>');
    }
}
foreach ($paymentMethod as $value) {
    if ($value['name'] !== 'Cash') {
		echo(' - ' . $value['name'] . '<br/>');
	}
}
?>
