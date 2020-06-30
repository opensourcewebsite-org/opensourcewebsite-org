<?php

use app\models\Vacancy;

?>
    <b><?= Yii::t('bot', 'Vacancy') ?>: <?= $name ?></b><br/>
<br/>
<?php if ($responsibilities) : ?>
<?= Yii::t('bot', 'Responsibilities') ?>:<br/>
<br/>
<?= nl2br($responsibilities) ?><br/>
<br/>
<?php endif; ?>
<?php if ($requirements) : ?>
<?= Yii::t('bot', 'Requirements') ?>:<br/>
<br/>
<?= nl2br($requirements) ?><br/>
<br/>
<?php endif; ?>
<?php if ($conditions) : ?>
<?= Yii::t('bot', 'Conditions') ?>:<br/>
<br/>
<?= nl2br($conditions) ?><br/>
<br/>
<?php endif; ?>
<?= Yii::t('bot', 'Hourly rate') ?>: <?= $hourlyRate ?> <?= $currencyCode ?><br/>
<br/>
<?= Yii::t('bot', 'Remote Job') ?>: <?= $remote_on == Vacancy::REMOTE_ON ? Yii::t('bot', 'Yes') : Yii::t('bot', 'No') ; ?><br/>
<br/>
<?php
if ($company):
    ?>
<b>Company: <?= $company->name; ?></b><br/>
<br/>
    <?php
    if ($company->description):
        ?>
Description of company<br/>
<i><?= nl2br($company->description); ?></i>
<br/>
    <?php
    endif;
endif;
if ($isActive) :
    ?>
<i>
<?= Yii::t('bot', 'You will receive a notification in case of matches with offers of other users') ?>.<?= Yii::t('bot', 'This page is active for {0,number} more days', 14) ?>.<?= Yii::t('bot', 'Visit this page again before this term to automatically renew this') ?>.
</i>
<?php
endif;
