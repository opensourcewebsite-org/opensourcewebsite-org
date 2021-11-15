<b><?= Yii::t('bot', 'Your Website Account') ?></b><br/>
<br/>
<?= Yii::t('bot', 'Follow the link below in the next {0, number} minutes to go to your Website account in the browser', $user->getAuthLinkTimeLimit()) ?>.<br/>
