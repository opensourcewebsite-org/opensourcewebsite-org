<?php if ($voting->isAccepted()) : ?>
✅ <b>[РІШЕННЯ ПРИЙНЯТО] <?= $voting->name ?>.</b><br/>
<?php else : ?>
🛑 <b>[РІШЕННЯ НЕ ПРИЙНЯТО] <?= $voting->name ?>.</b><br/>
<?php endif; ?>
<br/>
За: <b><?= $voting->for ?></b><br/>
Проти: <b><?= $voting->against ?></b><br/>
Утримались: <b><?= $voting->abstain ?></b><br/>
Не голосували: <b><?= $voting->not_voting ?></b><br/>
Всього: <b><?= $voting->presence ?></b><br/>
<br/>
Дата: <b><?= $voting->date ?></b><br/>
<br/>
<?= $voting->getVotingFullLink() ?>, <?= $voting->getLawFullLink() ?>
