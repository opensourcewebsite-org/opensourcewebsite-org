<b><?= Yii::t('bot', 'Send an exchange rate') ?> <?= !$isEdit ? (' ' . Yii::t('bot', 'or SKIP to enter inverse rate')) : '' ?> (<?= $model->getTitle() ?>).</b><br/>
<br/>
<i><?= Yii::t('bot', 'This information is used to find matches with offers from other users') ?>.<br/>
<br/>
    - <?= Yii::t('bot', 'Your offer with an exchange rate sees other offers that contain the same or higher rate') ?>.<br/>
    - <?= Yii::t('bot', 'Cross rate is the current international exchange rate') ?>.</i>