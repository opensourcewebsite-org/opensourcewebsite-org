<b><?= $chat->title ?></b><br/>
<br/>
<?= $chatMember->user->getFullLink() ?><br/>
<br/>
ID: #<?= $link->id ?><br/>
<?php if ($link->title) : ?>
<br/>
<?= Yii::t('bot', 'Title') . ': ' . $link->title ?><br/>
<?php endif; ?>
<?php if ($link->url) : ?>
<br/>
<?= Yii::t('bot', 'Url') . ': ' . $link->url ?><br/>
<?php endif; ?>
