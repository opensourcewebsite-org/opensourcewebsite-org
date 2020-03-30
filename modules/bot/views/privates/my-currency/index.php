<b><?= Yii::t('bot', 'Your Currency') ?></b><br/>
<br/>
<? if (isset($currencyName) && isset($currencyCode)) : ?>
<?= $currencyName ?> (<?= strtoupper($currencyCode) ?>)
<? else : ?>
<?= Yii::t('bot', 'Unknown') ?>
<? endif; ?>