<b><?= $chat->title ?></b><br/>
<br/>
<b><?= Yii::t('bot', 'Post') ?></b>: <?= $post->title ?: '#' . $post->id ?><br/>
<br/>
————<br/>
<br/>
<?= nl2br($post->text) ?><br/>
<br/>
<b><?= Yii::t('bot', 'Contact') ?></b>: <?= $user->getFullLink(); ?><br/>
<br/>
————<br/>
