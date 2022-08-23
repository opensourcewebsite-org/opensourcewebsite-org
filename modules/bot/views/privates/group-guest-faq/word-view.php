<?= $phrase->text ?><br/>
<?php if (isset($phrase->answer)) : ?>
————<br/>
<br/>
<?= nl2br($phrase->answer) ?><br/>
<br/>
————<br/>
<?php endif; ?>
