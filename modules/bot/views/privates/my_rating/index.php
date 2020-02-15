<b><?= \Yii::t('bot', 'Your User Rating') ?></b><br/><br/>

<b><?= \Yii::t('bot', 'Active Rating') ?>:</b> <b><?= $active_rating ?></b> (last 30 days)<br/>
<b><?= \Yii::t('bot', 'Global Rating') ?>:</b> <b><?= $overall_rating[0] ?></b>, <?= $overall_rating[2] ?>% <?= \Yii::t('bot', 'of') ?> <?= $overall_rating[1] ?> <?= \Yii::t('bot', '(total system overall rating)') ?><br/>
<b><?= \Yii::t('bot', 'Rank') ?>:</b> #<b><?= $ranking[0] ?></b> <?= \Yii::t('bot', 'among') ?> <?= $ranking[1] ?> <?= \Yii::t('bot', 'users') ?><br/>
<br/>
<?= \Yii::t('bot', 'User Rating provides') ?>:<br/>
<br/>
- <?= \Yii::t('bot', 'The power of user vote when voting for new features or changing existing features of the Bot and Website') ?>.<br/>
- <?= \Yii::t('bot', 'More possibilities and less limits when using of the Bot and Website') ?>.<br/>
- <?= \Yii::t('bot', 'Passive income, such as dividends from the profits of the Bot and Website, which is distributed proportionally among all users in accordance with the value of their User Ratings') ?>.<br/>
<br/>
<?= \Yii::t('bot', 'Your donations and contributions increase your User Rating') ?>.
