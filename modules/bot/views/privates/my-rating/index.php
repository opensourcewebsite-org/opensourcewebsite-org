<b><?= Yii::t('bot', 'Your Rating') ?></b><br/><br/>

<b><?= Yii::t('bot', 'Rank') ?>:</b> <b><?= $ranking[0] ?></b> <?= Yii::t('bot', 'of') ?> <?= $ranking[1] ?><br/>
<b><?= Yii::t('bot', 'Voting Power') ?>:</b> <b><?= $overall_rating[2] ?>%</b> <?= Yii::t('bot', 'of') ?> 100%<br/>
<b><?= Yii::t('bot', 'Rating') ?>:</b> <b><?= $overall_rating[0] ?></b> <?= Yii::t('bot', 'of') ?> <?= $overall_rating[1] ?><br/>
<b><?= Yii::t('bot', 'Active Rating') ?>:</b> <b><?= $active_rating ?></b> (<?= Yii::t('bot', 'in the last {0,number} days', 30) ?>)<br/>
<br/>
<?= Yii::t('common', 'Rating provides') ?>:<br/>
<br/>
- <?= Yii::t('bot', 'The power of user vote when voting for new features or changing existing features of the Bot and Website') ?>.<br/>
- <?= Yii::t('bot', 'More possibilities and less limits when using of the Bot and Website') ?>.<br/>
- <?= Yii::t('bot', 'Passive income, such as dividends from the profits of the Bot and Website, which is distributed proportionally among all users in accordance with the value of their Ratings') ?>.<br/>
<br/>
<?= Yii::t('bot', 'Your donations and contributions increase your Rating') ?>.
