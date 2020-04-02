<b><?= Yii::t('bot', 'Your Currency') ?></b><br/>
<br/>
<?php if (isset($currencyName) && isset($currencyCode)) : ?>
<?= $currencyName ?> (<?= strtoupper($currencyCode) ?>)
<?php else : ?>
<?= Yii::t('bot', 'Unknown') ?>
<?php endif; ?>
