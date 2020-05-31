<?php
/* @var \app\models\Company $company */
?>

<b><?= $company->name ?></b><br/>
<br/>
<? if (!empty($company->description)) : ?>
<?= $company->description ?><br/>
<br/>
<? endif; ?>
<? if (!empty($company->address)) : ?>
<?= Yii::t('bot', 'Address')?>: <?= $company->address ?><br/>
<br/>
<? endif; ?>
<? if (!empty($company->url)) : ?>
<?= Yii::t('bot', 'Website')?>: <?= $company->url ?><br/>
<br/>
<? endif; ?>
