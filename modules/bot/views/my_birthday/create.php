<? if ($success) { ?>
	<?= \Yii::t('bot', 'Birthday successfully changed') : ?>
<? else : ?>
<?= \Yii::t('bot', 'Please, enter your birthday in format DD.MM.YYYY') ?>
<? endif; ?>
