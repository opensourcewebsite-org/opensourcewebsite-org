<b><?= Yii::t('bot', 'Send a minimum amount limit of selling currency in one trade') ?> (<?= $model->sellingCurrency->code ?>).</b><br/>
<br/>
<i><?= Yii::t('bot', 'This information is used to find matches with offers from other users') ?>.<br/>
<br/>
  • <?= Yii::t('bot', 'Your offer with a minimum amount sees other offers that contain the same or higher maximum amount') ?>.<br/>
  • <?= Yii::t('bot', 'Your offer without a minimum amount sees all other offers with and without any maximum amount') ?>.</i>
