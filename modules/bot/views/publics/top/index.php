Top users list: <br/>
<?php foreach ($users as $user): ?>
<?= $user['username']; ?>: <?= $user['rating']; ?><br/>
<?php endforeach; ?>
