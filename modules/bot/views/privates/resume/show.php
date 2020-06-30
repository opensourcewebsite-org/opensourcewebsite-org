<?php

use app\models\Resume;

?>
    <b><?= Yii::t('bot', 'Resume') ?>: <?= $name ?></b><br/>
<br/>
<?php if ($skills) : ?>
<?= Yii::t('bot', 'Skills') ?>:<br/>
<br/>
<?= nl2br($skills) ?><br/>
<br/>
<?php endif; ?>
<?php if ($experiences) : ?>
<?= Yii::t('bot', 'Experiences') ?>:<br/>
<br/>
<?= nl2br($experiences) ?><br/>
<br/>
<?php endif; ?>
<?php if ($expectations) : ?>
<?= Yii::t('bot', 'Expectations') ?>:<br/>
<br/>
<?= nl2br($expectations) ?><br/>
<br/>
<?php endif;
if($hourlyRate):
?>
<?= Yii::t('bot', 'Hourly rate') ?>: <?= $hourlyRate ?> <?= $currencyCode ?><br/>
<br/>
<?= Yii::t('bot', 'Remote Job') ?>: <?= $remote_on == Resume::REMOTE_ON ? Yii::t('bot', 'Yes') : Yii::t('bot', 'No') ; ?><br/>
<br/>
<?php
endif;
if ($isActive) :
?>
<i><?= Yii::t('bot', 'You will receive a notification in case of matches with offers of other users') ?>. <?= Yii::t('bot', 'This page is active for {0,number} more days', 14) ?>. <?= Yii::t('bot', 'Visit this page again before this term to automatically renew this')?>.</i>
<?php
endif;
