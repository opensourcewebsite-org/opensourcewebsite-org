<?= Yii::t('bot', 'Vacancy status error'); ?>
<br/>
<?= Yii::t('bot', 'Please fill'); ?>
<?php
foreach ($notFilledFields as $field) :
    ?>
<br/>
<?= Yii::t('bot', $field); ?>
<?php
endforeach;
?>
