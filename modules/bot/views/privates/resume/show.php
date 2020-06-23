<b><?= Yii::t('bot', 'Resume') ?>: <?= $name ?></b><br/>
<br/>
<?php if ($skills) : ?>
<?= Yii::t('bot', 'Skills') ?>:<br/>
<br/>
<?= $skills ?><br/>
<br/>
<?php endif; ?>
<?php if ($experiences) : ?>
<?= Yii::t('bot', 'Experiences') ?>:<br/>
<br/>
<?= $experiences ?><br/>
<br/>
<?php endif; ?>
<?php if ($expectations) : ?>
<?= Yii::t('bot', 'Expectations') ?>:<br/>
<br/>
<?= $expectations ?><br/>
<br/>
<?php endif;
if($hourlyRate):
?>
<?= Yii::t('bot', 'Hourly rate') ?>: <?= $hourlyRate ?> <?= $currency->code ?><br/>
<br/>
<?php
endif;
?>
Description of company<br/>
<br/>
<i><?= Yii::t('bot', 'You will receive a notification in case of matches with offers of other users') ?>. <?= Yii::t('bot', 'This page is active for {0,number} more days', 14) ?>. <?= Yii::t('bot', 'Visit this page again before this term to automatically renew this')?>.</i>