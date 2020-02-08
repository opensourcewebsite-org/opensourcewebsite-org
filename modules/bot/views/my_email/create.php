<? if ($resetRequest) { ?>
	<?= \Yii::t('bot', 'You email was successfully set. Please, check your email for confirmation letter.') ?>
<? } elseif ($mergeRequest) {?>
	<?= \Yii::t('bot', 'We find a user with the same email as you entered. Do you want to merge your accounts?') ?>
<? } else { ?>
	<?= $error ?>
<? } ?>