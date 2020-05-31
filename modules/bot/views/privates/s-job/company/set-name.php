<?php
if (!$isEdit) {
    echo '<b>' . Yii::t('bot', 'Step {0} of {1}', [ $step, $totalSteps ]) . ' - </b>';
}
?>
<b><?= Yii::t('bot', 'Name')?></b><br/>
<br/>
<?php
if (!empty($currentValue)) {
    echo Yii::t('bot', 'Current value') . ': ' . $currentValue . '<br/><br/>';
}
?>
<?= Yii::t('bot', 'Send me name of the company') ?>
