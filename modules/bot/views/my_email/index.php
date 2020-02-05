<? if (isset($email)) { ?>
	<b><?= \Yii::t('bot', 'Your Email') ?></b><br/><br/>
<?= $email ?>
	<? if ($isEmailConfirmed === 0) {
		echo '<br/>';
		echo '<br/>';
		echo \Yii::t('bot', 'Your email isn`t confirmed. Please, check your email box for confirmation url.');
	} ?>
<? } else { ?>
	<?= \Yii::t('bot', 'Please, sent me your email') ?>
<? } ?>