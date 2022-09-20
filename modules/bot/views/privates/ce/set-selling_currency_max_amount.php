<b><?= Yii::t('bot', 'Send a maximum amount limit of selling currency in one trade') ?> (<?= $model->sellingCurrency->code ?>).</b><br/>
<br/>
<i><?= Yii::t('bot', 'This information is used to find matches with offers from other users') ?>.<br/>
<br/>
  • <?= Yii::t('bot', 'Your offer with a maximum amount sees other offers that contain the same or lower minimum amount') ?>.<br/>
  • <?= Yii::t('bot', 'Your offer without a maximum amount sees all other offers with and without any minimum amount') ?>.</i>
