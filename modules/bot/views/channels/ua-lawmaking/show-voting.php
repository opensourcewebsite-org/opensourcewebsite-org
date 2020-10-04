<?php if ($voting->isAccepted()) : ?>
✅ <b>[РІШЕННЯ ПРИЙНЯТО]</b><br/>
<?php else : ?>
🛑 <b>[РІШЕННЯ НЕ ПРИЙНЯТО]</b><br/>
<?php endif; ?>
<br/>
<b><?= $voting->date ?> - <?= $voting->getVotingFullLink() ?>.</b><br/>
<br/>
За: <b><?= $voting->for ?></b><br/>
Проти: <b><?= $voting->against ?></b><br/>
Утримались: <b><?= $voting->abstain ?></b><br/>
Не голосували: <b><?= $voting->not_voting ?></b><br/>
Всього: <b><?= $voting->presence ?></b><br/>
<br/>
<?php if (is_array($voting->getLaws()) && !empty($voting->getLaws())) : ?>
Законопроекти: <?= implode(', ', $voting->getLawsFullLinks()) ?>
<?php endif; ?>
