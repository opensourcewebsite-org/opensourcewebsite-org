<?php
/** @var \app\models\Language[] $languages */
/** @var \yii\data\Pagination $pagination */
echo '<b>' . \Yii::t('bot', 'Please choose your language:') . '</b>' . '<br/>';

foreach ($languages as $language) {
    echo '<code>' . $language->name . '</code>' . ' (' . strtoupper($language->code) . ') - /my_language_' . $language->code . '<br/>';
}
?>
<br/><?= \Yii::t('bot', 'Page {page} of {total}',
    ['page' => $pagination->page + 1, 'total' => $pagination->pageCount]); ?><br/>
