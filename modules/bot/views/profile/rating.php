<b><?= \Yii::t('bot', 'Your Rating') ?></b><br/><br/>
<b><?= \Yii::t('bot', 'Active Rating') ?>:</b> <?= $active_rating ?><br/>
<b><?= \Yii::t('bot', 'Overall Rating') ?>:</b> <?= $overall_rating[0] ?>
<?= \Yii::t('bot', 'of') ?> <?= $overall_rating[1] ?><br/>
<b><?= \Yii::t('bot', 'Ranking') ?>:</b> #<?= $ranking[0] ?> <?= \Yii::t('bot', 'among') ?>
<?= $ranking[1] ?> <?= \Yii::t('bot', 'users') ?><br/>
