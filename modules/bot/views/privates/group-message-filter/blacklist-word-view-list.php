<b><?= $chat->title ?></b><br/>
<br/>
<?= Yii::t('bot', 'All blacklist phrases') ?>:<br/>
————————————————————<br/>
<?php foreach($phrases as $phrase) : ?>
<?= $phrase->text ?><br/>
<?php endforeach; ?>