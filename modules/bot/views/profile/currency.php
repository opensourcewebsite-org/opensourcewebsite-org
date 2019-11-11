<?php
/** @var \app\models\currency $currencyModel */
/** @var string $currentCode */
/** @var string $currentName */

if ($currencyModel) { ?>
    <?= \Yii::t('bot', $currencyModel->hasErrors() ? 'Sorry, it looks like something went wrong.'
        : 'Currency has been changed successfully') ?><br/>
<?php } ?>

<b><?= \Yii::t('bot', 'Currency is now installed') ?></b><br/>
<?= $currentName ?> (<?= strtoupper($currentCode) ?>)