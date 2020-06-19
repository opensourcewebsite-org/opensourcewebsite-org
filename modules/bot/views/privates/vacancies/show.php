<b><?= Yii::t('bot', 'Vacancy') ?>: <?= $name ?></b><br/>
<br/>
<?php if ($responsibilities) : ?>
<?= Yii::t('bot', 'Responsibilities') ?>:<br/>
<br/>
<?= $responsibilities ?><br/>
<br/>
<?php endif; ?>
<?php if ($requirements) : ?>
<?= Yii::t('bot', 'Requirements') ?>:<br/>
<br/>
<?= $requirements ?><br/>
<br/>
<?php endif; ?>
<?php if ($conditions) : ?>
<?= Yii::t('bot', 'Conditions') ?>:<br/>
<br/>
<?= $conditions ?><br/>
<br/>
<?php endif; ?>
<?= Yii::t('bot', 'Hourly rate') ?>: <?= $hourlyRate ?><br/>
<br/>
<b>Company: COMPANY</b><br/>
<br/>
Description of company<br/>
<br/>
<i><?= Yii::t('bot', 'You will receive a notification in case of matches with offers of other users') ?>. <?= Yii::t('bot', 'This page is active for {0,number} more days', 14) ?>. <?= Yii::t('bot', 'Visit this page again before this term to automatically renew this')?>.</i>
