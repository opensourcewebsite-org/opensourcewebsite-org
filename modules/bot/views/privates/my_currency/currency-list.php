<?php
/** @var \app\models\Currency[] $currencies */
/** @var \yii\data\Pagination $pagination */
?>
<b><?= Yii::t('bot', 'Choose your currency') ?>:</b><br/>
<br/>
<?php
foreach ($currencies as $currency) {
    echo '/my_currency_' . $currency->code . ' - ' . $currency->name . ' (' . strtoupper($currency->code) . ')<br/>';
}
