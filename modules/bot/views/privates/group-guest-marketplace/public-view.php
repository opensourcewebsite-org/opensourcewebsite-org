<?php

use app\modules\bot\components\helpers\Emoji;

?>
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
