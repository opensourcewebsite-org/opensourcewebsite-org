<?php
if (!$isEdit) {
    echo '<b>' . Yii::t('bot', 'Step {0} of {1}', [ $step, $totalSteps ]) . ' - </b>';
}
?>
<b><?= Yii::t('bot', 'Currency') ?></b><br/>
<br/>
<?php
if (!empty($currentValue)) {
    echo Yii::t('bot', 'Current value') . ': ' . $currentValue->code . ' - ' . $currentValue->name . '<br/><br/>';
}
?>
<?= Yii::t('bot', 'Select a currency of the vacancy') ?>
