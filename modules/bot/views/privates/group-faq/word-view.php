<?= $phrase->text ?><br/>
<?php if (isset($phrase->answer)) : ?>
————<br/>
<?= nl2br($phrase->answer) ?><br/>
————<br/>
<?php endif; ?>
