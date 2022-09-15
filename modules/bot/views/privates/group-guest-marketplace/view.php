<?php

use app\modules\bot\components\helpers\Emoji;

?>
<b><?= $chat->title ?></b><br/>
<br/>
<b><?= Yii::t('bot', 'Post') ?>: #<?= $post->id ?> <?= $post->title ?></b><br/>
————<br/>
<?= nl2br($post->text) ?><br/>
<?php if ($tags) : ?>
<br/>
<?php foreach ($tags as $tag) : ?>
#<?= $tag . ' ' ?>
<?php endforeach; ?>
<br/>
<?php endif; ?>
<br/>
<?= Emoji::RIGHT ?> <?= $user->getFullLink(); ?><br/>
————<br/>
<?= Yii::t('bot', 'Buttons will be automatically added') ?>:<br/>
  • <?= Yii::t('bot', 'Contact') ?><br/>
  • <?= Yii::t('bot', 'Reviews') ?><br/>
<?php if ($links = $chatMember->marketplaceLinks) : ?>
<?php foreach ($links as $link) : ?>
<?php if ($link->title) : ?>
  • <?= $link->title ?><br/>
<?php endif; ?>
<?php endforeach; ?>
<?php endif; ?>
