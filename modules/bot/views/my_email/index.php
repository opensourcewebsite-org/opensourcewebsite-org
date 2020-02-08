<? if (isset($email)) { ?>
	<b><?= \Yii::t('bot', 'Your Email') ?></b><br/><br/>
<?= $email ?>
	<? if ($isEmailConfirmed === 0) {
		echo '<br/>';
		echo '<br/>';
		echo \Yii::t('bot', 'Your email isn`t confirmed. Please, check your email box for confirmation url.');
	} ?>
	<? if ($hasMergeAccountsRequest) { 
		echo '<br/>';
		echo '<br/>';
		echo \Yii::t('bot', 'We found that you requested a merge of accounts. You can confirm it by open the link from letter sent to your email. Or you can discard it and change email to another one.');
	}?>
<? } else { ?>
	<?= \Yii::t('bot', 'Please, sent me your email') ?>
<? } ?>