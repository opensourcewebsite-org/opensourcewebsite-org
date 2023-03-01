<b><?= $chat->title ?></b><br/>
<br/>
<b><?= Yii::t('bot', 'Post') ?>: #<?= $post->id ?></b><br/>
————<br/>
<?= nl2br($post->text) ?><br/>
<?php if ($post->getNextSendAt()) : ?>
————<br/>
<?= Yii::t('bot', 'Next publish') ?>: <?= Yii::$app->formatter->asDatetime($post->getNextSendAt()) // TODO use chat timezone ?>
<?php endif; ?>
