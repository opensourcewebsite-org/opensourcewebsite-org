<?php
	use \app\models\User;
?>

<? if ($success) { ?>
	<?= \Yii::t('bot', 'Birthday successfully changed') ?>
<? } else { ?>
<?= \Yii::t('bot', 'Please, enter your birthday in format') . ' ' . User::DATE_FORMAT ?>
<? } ?>