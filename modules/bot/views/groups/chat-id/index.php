<b><?= Yii::t('bot', 'Telegram') ?> Chat ID</b>: <?= $chat->getChatId() ?><br/>
<?php if (isset($topicId)) : ?>
<b><?= Yii::t('bot', 'Telegram') ?> Topic ID</b>: <?= $topicId ?><br/>
<?php endif; ?>
