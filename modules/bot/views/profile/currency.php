<?php
/** @var \app\models\currency $currencyModel */
/** @var string $currentCode */
/** @var string $currentName */

if ($currencyModel) { ?>
    <?= $currencyModel->hasErrors() ? \Yii::t('bot', 'Sorry, it looks like something went wrong.') : NULL ?><br/>
<?php } ?>

<b><?= \Yii::t('bot', 'Your currency') ?>:</b><br/>
<?= $currentName ?> (<?= strtoupper($currentCode) ?>)
