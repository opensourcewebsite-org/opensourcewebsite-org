<?php if ($voting->isAccepted()) : ?>
✅ <b>РІШЕННЯ ПРИЙНЯТО</b><br/>
<?php else : ?>
🛑 <b>РІШЕННЯ НЕ ПРИЙНЯТО</b><br/>
<?php endif; ?>
<br/>
<b><?= $voting->date ?> - <?= $voting->getVotingFullLink() ?>.</b><br/>
<br/>
<?php if (is_array($voting->getLaws()) && !empty($voting->getLaws())) : ?>
Законопроекти: <?= implode(', ', $voting->getLawsFullLinks()) ?>
<?php endif; ?>
