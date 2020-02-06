<? if ($success) { ?>
	<?= \Yii::t('bot', 'Birthday successfully changed') ?>
<? } else { ?>
	<?= \Yii::t('bot', 'Given date has invalid format') ?>
<? } ?>