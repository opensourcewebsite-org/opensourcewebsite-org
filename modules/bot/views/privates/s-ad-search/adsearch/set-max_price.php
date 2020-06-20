<?php
if (!empty($error)) {
    echo Yii::t('bot', 'You entered an invalid value') . ': ' . $error . '<br/><br/>';
}
$currencyCode = 'TEST';
if (isset($model) && ($currency = $model->currencyRelation)) {
    $currencyCode = $currency->code;
}
?>

<?= Yii::t('bot', 'Send a max price') ?> (<?= $currencyCode ?>)
<?php
if ($model->max_price):
    ?>
    <br/><br/>
    <b><?= Yii::t('bot', 'Max price') ?>:</b> <?= $model->max_price ?> <?= $currencyCode ?><br/>
<?php
endif;
