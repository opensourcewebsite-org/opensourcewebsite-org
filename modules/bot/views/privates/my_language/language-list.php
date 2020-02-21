<?php
/** @var \app\models\Language[] $languages */
/** @var \yii\data\Pagination $pagination */
?>
<b><?= Yii::t('bot', 'Choose your language') ?></b>:<br/>
<br/>
<?php
foreach ($languages as $language) {
    echo  '/my_language_' . $language->code .  ' - ' . $language->name . ' (' . strtoupper($language->code) . ')<br/>';
}
