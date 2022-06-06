<b><?= $chat->title ?></b><?= $chat->username ? ' (@' . $chat->username . ')' : '' ?><br/>
<?php if ($chat->description) : ?>
<br/>
<?= nl2br($chat->description); ?><br/>
<?php endif; ?>
