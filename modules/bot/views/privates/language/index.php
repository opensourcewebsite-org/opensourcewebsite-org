<b><?= Yii::t('bot', 'Your Language') ?></b><br/>
<br/>
<?php if (isset($languageName) && isset($languageCode)) : ?>
<?= $languageName ?> (<?= strtoupper($languageCode) ?>)
<?php else : ?>
<?= Yii::t('bot', 'Unknown') ?>
<?php endif; ?>
