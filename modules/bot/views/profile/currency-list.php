<?php
/** @var \app\models\Currency[] $currencies */
/** @var \yii\data\Pagination $pagination */
echo '<b>' . \Yii::t('bot', 'Please choose your currency:') . '</b>' . '<br/>';

foreach ($currencies as $currency) {
    echo $currency->name . ' (' . strtoupper($currency->code) . ') - /my_currency_' . $currency->code . '<br/>';
}
?>
<br/><?= \Yii::t('bot', 'Page {page} of {total}',
    ['page' => $pagination->page + 1, 'total' => $pagination->pageCount]); ?><br/>
