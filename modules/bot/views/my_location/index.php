<b><?= \Yii::t('bot', 'Your Location') ?></b>
<br/><br/>
<?php
	if (isset($longtitude))
	{
		echo \Yii::t('bot', 'Longtitude') . ': ' . $longtitude . '<br/>';
	}
	if (isset($latitude))
	{
		echo \Yii::t('bot', 'Latitude') . ': ' . $latitude . '<br/>';
	}
	if (isset($lastUpdate))
	{
		echo \Yii::t('bot', 'Last update') . ': ' . $lastUpdate . '<br/>';
	}
?>
