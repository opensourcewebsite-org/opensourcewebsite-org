<?php
if (!$isEdit) {
    echo '<b>' . Yii::t('bot', 'Step {0} of {1}', [ $step, $totalSteps ]) . ' - </b>';
}
?>
<b><?= Yii::t('bot', 'Responsibilities') ?></b><br/>
<br/>
<?php
if (isset($currentValue) && $currentValue !== '') {
    echo Yii::t('bot', 'Current value') . ': <br/>';
    echo $currentValue . '<br/><br/>';
}
?>
<?= Yii::t('bot', 'Send me responsibilities of the vacancy') ?>
