<?php
if (!$isEdit) {
    echo '<b>' . Yii::t('bot', 'Step {0} of {1}', [ $step, $totalSteps ]) . ' - </b>';
}
?>
<b><?= Yii::t('bot', 'Hourly Rate') ?></b><br/>
<br/>
<?php
if (!empty($error)) {
    echo Yii::t('bot', 'You entered an invalid value') . ': ' . $error . '<br/><br/>';
}
if (isset($currentValue) && $currentValue !== '') {
    echo Yii::t('bot', 'Current value') . ': ' . $currentValue . '<br/><br/>';
}
?>
<?= Yii::t('bot', 'Send me hourly rate of the vacancy') ?>
