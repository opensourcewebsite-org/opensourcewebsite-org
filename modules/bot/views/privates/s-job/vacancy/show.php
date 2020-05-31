<?php
/* @var \app\models\Vacancy $vacancy */
?>
<b><?= Yii::t('bot', 'Vacancy') ?>: <?= $vacancy->name ?></b><br/>
<br/>

<?php if (!empty($vacancy->responsibilities)) : ?>
<?= Yii::t('bot', 'Responsibilities') ?>:<br/>
<br/>
<?= $vacancy->responsibilities ?><br/>
<br/>
<?php endif; ?>

<?php if (!empty($vacancy->requirements)) : ?>
<?= Yii::t('bot', 'Requirements') ?>:<br/>
<br/>
<?= $vacancy->requirements ?><br/>
<br/>
<?php endif; ?>

<?php if (!empty($vacancy->conditions)) : ?>
<?= Yii::t('bot', 'Conditions') ?>:<br/>
<br/>
<?= $vacancy->conditions ?><br/>
<br/>
<?php endif; ?>

<?= Yii::t('bot', 'Hourly rate') ?>: <?= $vacancy->hourly_rate . ' ' . $vacancy->currency->code ?><br/>
<br/>

<b><?= Yii::t('bot', 'Company') ?>: <?= $vacancy->company->name ?></b><br/>
<br/>
<? if (!empty($vacancy->company->description)) : ?>
<?= $vacancy->company->description ?><br/>
<br/>
<? endif; ?>
<?= Yii::t('bot', 'This vacancy is active for {0,number} more days', 14) ?>. <?= Yii::t('bot', 'Visit this page again before this term to automatically renew this')?>.
