<b><?= \Yii::t('bot', 'Your Rating') ?></b><br/><br/>

<b><?= \Yii::t('bot', 'Active Rating') ?>:</b> <?= $active_rating ?> (last 30 days)<br/>
<b><?= \Yii::t('bot', 'Overall Rating') ?>:</b> <?= $overall_rating[0] ?>, 0,00% <?= \Yii::t('bot', 'of') ?> <?= $overall_rating[1] ?><br/>
<b><?= \Yii::t('bot', 'Rank') ?>:</b> #<?= $ranking[0] ?> <?= \Yii::t('bot', 'of') ?> <?= $ranking[1] ?><br/><br/>

<?= \Yii::t('bot', 'How does your User Rating work?') ?> <?= \Yii::t('bot', 'How to increase your User Rating?') ?><br/><br/>

<?= \Yii::t('bot', 'Read more') ?>: https://opensourcewebsite.org
