<b><?= \Yii::t('bot', 'Your Gender') ?></b>
<br/><br/>
<? if (isset($gender)) { ?>
	<?= \Yii::t('bot', $gender ? 'Female' : 'Male') ?>
<? } else { ?>
	<?= \Yii::t('bot', 'Unknown') ?>
<? } ?>