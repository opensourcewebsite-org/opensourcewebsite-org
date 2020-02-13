<b><?= \Yii::t('bot', 'Your Rating') ?></b><br/><br/>

<b><?= \Yii::t('bot', 'Active Rating') ?>:</b> <b><?= $active_rating ?></b> (last 30 days)<br/>
<b><?= \Yii::t('bot', 'Overall Rating') ?>:</b> <b><?= $overall_rating[0] ?></b>, <?= $overall_rating[2] ?>% <?= \Yii::t('bot', 'of') ?> <?= $overall_rating[1] ?> <?= \Yii::t('bot', '(total system overall rating)') ?><br/>
<b><?= \Yii::t('bot', 'Rank') ?>:</b> #<b><?= $ranking[0] ?></b> <?= \Yii::t('bot', 'among') ?> <?= $ranking[1] ?> <?= \Yii::t('bot', 'users') ?><br/><br/>

<?= \Yii::t('bot', 'How does your User Rating work?') ?> <?= \Yii::t('bot', 'How to increase your User Rating?') ?><br/><br/>

<?= \Yii::t('bot', 'Read more') ?>: https://opensourcewebsite.org
