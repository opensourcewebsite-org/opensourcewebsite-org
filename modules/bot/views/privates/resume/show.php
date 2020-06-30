<?php

use app\models\Resume;

?>
<b><?= Yii::t('bot', 'Resume') ?>: <?= $name ?></b><br/>
<br/>
<?php if ($skills) : ?>
<b><?= Yii::t('bot', 'Skills') ?>:</b><br/>
<br/>
<?= nl2br($skills) ?><br/>
<br/>
<?php endif; ?>
<?php if ($experiences) : ?>
<b><?= Yii::t('bot', 'Experiences') ?>:</b><br/>
<br/>
<?= nl2br($experiences) ?><br/>
<br/>
<?php endif; ?>
<?php if ($expectations) : ?>
<b><?= Yii::t('bot', 'Expectations') ?>:</b><br/>
<br/>
<?= nl2br($expectations) ?><br/>
<br/>
<?php endif; ?>
<?php if($hourlyRate) : ?>
<b><?= Yii::t('bot', 'Min. hourly rate') ?>:</b> <?= $hourlyRate ?> <?= $currencyCode ?><br/>
<br/>
<?= Yii::t('bot', 'Remote Job') ?>: <?= $remote_on == Resume::REMOTE_ON ? Yii::t('bot', 'Yes') : Yii::t('bot', 'No') ; ?><br/>
<br/>
<?php endif; ?>
<?php if ($isActive) : ?>
————<br/>
<br/>
<i><?= Yii::t('bot', 'You will receive a notification in case of matches with offers of other users') ?>. <?= Yii::t('bot', 'This page is active for {0,number} more days', 14) ?>. <?= Yii::t('bot', 'Visit this page again before this term to automatically renew this')?>.</i>
<?php endif; ?>
