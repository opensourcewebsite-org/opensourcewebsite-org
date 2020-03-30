<b><?= Yii::t('bot', 'Your Language') ?></b><br/>
<br/>
<? if (isset($languageName) && isset($languageCode)) : ?>
<?= $languageName ?> (<?= strtoupper($languageCode) ?>)
<? else : ?>
<?= Yii::t('bot', 'Unknown') ?>
<? endif; ?>
