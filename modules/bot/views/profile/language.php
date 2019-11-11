<?php
/** @var \app\models\Language $languageModel */
/** @var string $currentCode */
/** @var string $currentName */

if ($languageModel) { ?>
    <?= \Yii::t('bot', $languageModel->hasErrors() ? 'Sorry, it looks like something went wrong.'
        : 'Language has been changed successfully') ?><br/>
<?php } ?>

<b><?= \Yii::t('bot', 'Language is now installed') ?></b><br/>
<?= $currentName ?> (<?= strtoupper($currentCode) ?>)