<?php
/** @var \app\models\Language[] $languages */
/** @var \yii\data\Pagination $pagination */
echo '<b>' . \Yii::t('bot', 'Please choose your language') . '</b>:' . '<br/><br/>';

foreach ($languages as $language) {
    echo  '/my_language_' . $language->code .  ' - ' . $language->name . ' (' . strtoupper($language->code) . ')<br/>';
}
?>
<br/><?= \Yii::t('bot', 'Page {page} of {total}',
    ['page' => $pagination->page + 1, 'total' => $pagination->pageCount]); ?><br/>
