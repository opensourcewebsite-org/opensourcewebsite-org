<b><?= $chat->title ?></b><br/>
<?php if ($chat->description) : ?>
<br/>
<?= nl2br($chat->description); ?><br/>
<?php endif; ?>
<?php if ($chat->username) : ?>
<br/>
@<?= $chat->username; ?><br/>
<?php endif; ?>
