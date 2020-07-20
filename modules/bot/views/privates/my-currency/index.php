<b><?= Yii::t('bot', 'Your Currency') ?></b><br/>
<br/>
<?php if (isset($currencyName) && isset($currencyCode)) : ?>
<?= $currencyName ?> (<?= strtoupper($currencyCode) ?>)<br/>
<?php else : ?>
<?= Yii::t('bot', 'Unknown') ?><br/>
<?php endif; ?>
<br/>
<i><?= Yii::t('bot', 'This information is used for all bot services') ?>.</i>
