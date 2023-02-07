<b><?= $chat->title ?></b><br/>
<br/>
<b><?= Yii::t('bot', 'Post') ?>: #<?= $post->id ?> <?= $post->title ?></b><br/>
————<br/>
<?= nl2br($post->text) ?><br/>
