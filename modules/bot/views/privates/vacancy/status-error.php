<?= Yii::t('bot', 'To activate, fill in the following fields'); ?>:<br/>
<br/>
<?php foreach ($notFilledFields as $field) : ?>
<?= Yii::t('bot', $field); ?><br/>
<?php endforeach; ?>
