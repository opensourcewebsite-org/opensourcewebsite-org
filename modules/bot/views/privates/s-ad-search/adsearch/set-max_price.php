<?php
if (!empty($error)) {
    echo Yii::t('bot', 'You entered an invalid value') . ': ' . $error . '<br/><br/>';
}
$currencyCode = 'TEST';
if (isset($model)) {
    $currencyCode = $model->currency->code;
}
?>

<?= Yii::t('bot', 'Send a max price') ?> (<?= $currencyCode ?>)
