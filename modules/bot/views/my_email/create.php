<? if ($resetRequest) { ?>
	<?= \Yii::t('bot', 'You email was successfully set. Please, check your email for confirmation letter.') ?>
<? } else { ?>
	<?= $error ?>
<? } ?>