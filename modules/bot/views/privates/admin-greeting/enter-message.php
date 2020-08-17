<b><?= Yii::t('bot', 'Please, type your Welcome message (multilines are allowed)') ?></b><br />
<?= Yii::t('bot', 'Allowed HTML tags for your welcome message:') ?><br />
<?= htmlspecialchars('<b></b>') . ' ' . '<b>' . Yii::t('bot', 'Get the text bold') . '</b>' ?><br />
<?= htmlspecialchars('<i></i>') . ' ' . '<i>' . Yii::t('bot', 'Get the text italic') . '</i>' ?><br />
